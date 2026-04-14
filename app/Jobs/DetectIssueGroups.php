<?php

namespace App\Jobs;

use App\Models\Branch;
use App\Models\Issue;
use App\Models\IssueAiAnalysis;
use App\Models\IssueCategory;
use App\Models\IssueGroup;
use App\Models\IssueGroupItem;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class DetectIssueGroups implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries   = 1;
    public int $timeout = 60;

    public const SENSITIVE_CATEGORIES = ['Behavior', 'Health & Safety'];

    // Words too generic to use as group keywords
    private const STOP_WORDS = [
        // English stop words
        'the', 'a', 'an', 'is', 'are', 'was', 'were', 'be', 'been', 'being',
        'in', 'of', 'no', 'not', 'and', 'or', 'with', 'for', 'to', 'at', 'on',
        'by', 'it', 'its', 'has', 'have', 'had', 'do', 'does', 'did', 'will',
        'very', 'too', 'also', 'just', 'from', 'that', 'this', 'there', 'their',
        'poor', 'lack', 'bad', 'issue', 'problem', 'concern', 'complaint',
        'every', 'after', 'main', 'need', 'needed', 'want', 'year', 'time',
        'week', 'month', 'day', 'more', 'less', 'many', 'much', 'often', 'into',
        'over', 'about', 'been', 'being', 'please', 'would', 'could', 'should',
        'recur', 'systemic', 'oneoff', 'formally', 'required', 'requesting',
        'available', 'response', 'urgently',
        // School-context generic words — appear in almost every issue
        'student', 'students', 'school', 'children', 'child', 'kids', 'class',
        'teacher', 'staff', 'parent', 'management', 'classroom',
        // Roman Urdu common words that leak through
        'nahi', 'mujhe', 'hoga', 'hain', 'bohot', 'kesy', 'mein', 'hoti',
        'bachon', 'sabun', 'gandy', 'takleef', 'jaane', 'hath', 'dhoyen',
        'bachy', 'kyun', 'karo', 'kiya', 'kuch', 'bhi', 'wali', 'wala',
    ];

    public function __construct(
        public readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        tenancy()->initialize(Tenant::find($this->tenantId));

        try {
            $analyses = IssueAiAnalysis::where('tenant_id', $this->tenantId)
                ->where('analysis_type', 'full')
                ->where('created_at', '>=', now()->subDays(14))
                ->whereHas('issue', fn ($q) =>
                    $q->whereIn('status', ['new', 'in_progress'])
                      ->where('is_spam', false)
                )
                ->with('issue:id,branch_id,issue_category_id,status,title,description')
                ->get();

            if ($analyses->isEmpty()) {
                return;
            }

            // Build a set of dismissed bucket signatures — these are permanently skipped
            // so rescanning never resurfaces a group the admin already dismissed.
            $dismissedSignatures = IssueGroup::where('tenant_id', $this->tenantId)
                ->where('status', 'dismissed')
                ->get(['theme', 'issue_category_id', 'branch_id'])
                ->mapWithKeys(fn ($g) => [
                    // Keyword groups: key is theme + branch (no category)
                    md5($g->theme . '|' . $g->branch_id) => true,
                    // Category+branch groups: key includes category
                    md5($g->theme . '|' . $g->issue_category_id . '|' . $g->branch_id) => true,
                ])
                ->all();

            $categoryNames = IssueCategory::where('tenant_id', $this->tenantId)->pluck('name', 'id');
            $branchNames   = Branch::where('tenant_id', $this->tenantId)->pluck('name', 'id');

            // ── Pass 1: Keyword-based grouping ────────────────────────────────────────
            // Extract significant words from each theme. Issues sharing a keyword
            // in the same category+branch get grouped — catches "dirty bathrooms"
            // and "no soap in bathroom" under the shared keyword "bathroom".
            $keywordBuckets = [];

            foreach ($analyses as $analysis) {
                $issue  = $analysis->issue;
                $themes = $analysis->result['themes'] ?? [];

                // Combine AI theme keywords + keywords from the issue title.
                // Titles often contain consistent location words (e.g. "bathroom")
                // even when AI picks different theme labels for related issues.
                $allKeywords = [];
                foreach ($themes as $theme) {
                    foreach ($this->extractKeywords($theme) as $kw) {
                        $allKeywords[] = $kw;
                    }
                }
                if ($issue->title) {
                    foreach ($this->extractKeywords($issue->title) as $kw) {
                        $allKeywords[] = $kw;
                    }
                }
                if ($issue->description) {
                    foreach ($this->extractKeywords($issue->description) as $kw) {
                        $allKeywords[] = $kw;
                    }
                }

                foreach (array_unique($allKeywords) as $keyword) {
                    // Keyword + branch only — category intentionally excluded.
                    // Parents often miscategorise (Facilities vs General Complaints)
                    // but the keyword + branch location is the real signal.
                    $key = md5($keyword . '|' . $issue->branch_id);
                    $keywordBuckets[$key]['keyword']     = $keyword;
                    $keywordBuckets[$key]['category_id'] = null;
                    $keywordBuckets[$key]['branch_id']   = $issue->branch_id;
                    $keywordBuckets[$key]['issue_ids'][] = $issue->id;
                }
            }

            // Track which issues have been grouped so Pass 2 doesn't duplicate them
            $groupedIssueIds = [];

            foreach ($keywordBuckets as $bucket) {
                $issueIds = array_unique($bucket['issue_ids']);
                if (count($issueIds) < 2) {
                    continue;
                }

                $sig = md5($bucket['keyword'] . '|' . $bucket['branch_id']);
                if (isset($dismissedSignatures[$sig])) {
                    continue; // admin dismissed this — never resurface it
                }

                $this->upsertGroup(
                    theme: $bucket['keyword'],
                    categoryId: $bucket['category_id'],
                    branchId: $bucket['branch_id'],
                    issueIds: $issueIds,
                    categoryNames: $categoryNames,
                    branchNames: $branchNames,
                    labelSuffix: Str::title($bucket['keyword']),
                );

                foreach ($issueIds as $id) {
                    $groupedIssueIds[$id] = true;
                }
            }

            // ── Pass 2: Category + Branch fallback grouping ───────────────────────────
            // For issues that didn't match any keyword group, cluster purely by
            // category + branch. Requires 3+ issues (looser signal, higher bar).
            $cbBuckets = [];

            foreach ($analyses as $analysis) {
                $issue = $analysis->issue;
                if (isset($groupedIssueIds[$issue->id])) {
                    continue; // already handled in Pass 1
                }

                $key = md5('cb|' . $issue->issue_category_id . '|' . $issue->branch_id);
                $cbBuckets[$key]['category_id'] = $issue->issue_category_id;
                $cbBuckets[$key]['branch_id']   = $issue->branch_id;
                $cbBuckets[$key]['issue_ids'][] = $issue->id;
            }

            foreach ($cbBuckets as $bucket) {
                $issueIds = array_unique($bucket['issue_ids']);
                if (count($issueIds) < 3) {
                    continue; // stricter threshold — no theme signal
                }

                $sig = md5('multiple_issues' . '|' . $bucket['category_id'] . '|' . $bucket['branch_id']);
                if (isset($dismissedSignatures[$sig])) {
                    continue;
                }

                $this->upsertGroup(
                    theme: 'multiple_issues',
                    categoryId: $bucket['category_id'],
                    branchId: $bucket['branch_id'],
                    issueIds: $issueIds,
                    categoryNames: $categoryNames,
                    branchNames: $branchNames,
                    labelSuffix: 'Multiple Issues',
                );
            }
        } finally {
            tenancy()->end();
        }
    }

    private function upsertGroup(
        string $theme,
        ?int $categoryId,
        ?int $branchId,
        array $issueIds,
        $categoryNames,
        $branchNames,
        string $labelSuffix,
    ): void {
        $confidence = match (true) {
            count($issueIds) >= 5 => 'high',
            count($issueIds) >= 3 => 'medium',
            default               => 'low',
        };

        $label = implode(' · ', array_filter([
            $categoryNames[$categoryId] ?? null,
            $branchNames[$branchId] ?? null,
            $labelSuffix,
        ]));

        $group = IssueGroup::firstOrCreate(
            [
                'tenant_id'         => $this->tenantId,
                'theme'             => $theme,
                'issue_category_id' => $categoryId,
                'branch_id'         => $branchId,
                'status'            => 'open',
            ],
            [
                'label'       => $label,
                'confidence'  => $confidence,
                'issue_count' => 0,
            ]
        );

        foreach ($issueIds as $issueId) {
            IssueGroupItem::firstOrCreate(
                ['issue_group_id' => $group->id, 'issue_id' => $issueId],
                ['tenant_id' => $this->tenantId],
            );
        }

        $activeCount = IssueGroupItem::where('issue_group_id', $group->id)
            ->whereNull('removed_at')
            ->count();

        $group->update([
            'confidence'  => $confidence,
            'issue_count' => $activeCount,
            'label'       => $label,
        ]);
    }

    /**
     * Extract significant keywords from an AI theme string.
     * "dirty bathrooms" → ["bathroom"]
     * "no soap in bathroom" → ["soap", "bathroom"]
     */
    private function extractKeywords(string $theme): array
    {
        // Normalize: lowercase, remove punctuation
        $normalized = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $theme));

        $words = preg_split('/\s+/', trim($normalized));

        $keywords = [];
        foreach ($words as $word) {
            // Skip short words and stop words
            if (strlen($word) < 5) {
                continue;
            }
            if (in_array($word, self::STOP_WORDS, true)) {
                continue;
            }
            // Normalize plural → singular (simple suffix stripping)
            $word = $this->singularize($word);
            $keywords[] = $word;
        }

        return array_unique($keywords);
    }

    /** Very lightweight singularizer for common English plurals */
    private function singularize(string $word): string
    {
        if (str_ends_with($word, 'ies') && strlen($word) > 4) {
            return substr($word, 0, -3) . 'y';   // bathries → bathroom (won't match, but facilities → facility)
        }
        if (str_ends_with($word, 'ses') || str_ends_with($word, 'xes') || str_ends_with($word, 'zes')) {
            return substr($word, 0, -2);          // classes → class
        }
        if (str_ends_with($word, 's') && strlen($word) > 4) {
            return substr($word, 0, -1);          // bathrooms → bathroom
        }
        return $word;
    }
}
