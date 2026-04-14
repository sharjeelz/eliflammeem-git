<?php

namespace App\Jobs;

use App\Jobs\DetectIssueTrends;
use App\Jobs\DetectIssueGroups;
use App\Models\Issue;
use App\Models\IssueAiAnalysis;
use App\Models\IssueCategory;
use App\Models\Tenant;
use App\Services\AiAnalysisService;
use App\Services\PlanService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AnalyzeIssueSentiment implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries   = 3;
    public int $timeout = 60;
    public int $backoff = 30;

    public function __construct(
        public readonly int    $issueId,
        public readonly string $tenantId,
        public readonly string $description,
        public readonly string $title = '',
        public readonly ?int   $categoryId = null,
    ) {}

    public function handle(AiAnalysisService $ai): void
    {
        tenancy()->initialize(Tenant::find($this->tenantId));

        try {
            // Skip AI analysis if not included in the tenant's plan
            if (! PlanService::forCurrentTenant()->allows('ai_analysis')) {
                return;
            }

            $issue = Issue::find($this->issueId);

            if (! $issue || $issue->is_spam || $issue->is_anonymous) {
                return;
            }

            $categoryName = $this->categoryId
                ? IssueCategory::find($this->categoryId)?->name
                : null;

            $data = $ai->analyze($this->description, $this->title, $categoryName);

            if (empty($data)) {
                return;
            }

            IssueAiAnalysis::updateOrCreate(
                [
                    'tenant_id'     => $this->tenantId,
                    'issue_id'      => $this->issueId,
                    'analysis_type' => 'full',
                ],
                [
                    'result'        => $data,
                    'confidence'    => $data['sentiment_score'] ?? null,
                    'model_version' => $data['model_version'] ?? null,
                ]
            );

            // Update submission_type on the issue from AI classification
            if (! empty($data['submission_type'])) {
                Issue::where('id', $this->issueId)
                    ->update(['submission_type' => $data['submission_type']]);

                // Auto-close compliments immediately — no CSAT, no email, no assignment needed
                if ($data['submission_type'] === 'compliment') {
                    $fresh = Issue::find($this->issueId);
                    if ($fresh && $fresh->status !== 'closed') {
                        $fresh->update([
                            'status'            => 'closed',
                            'status_entered_at' => now(),
                            'last_activity_at'  => now(),
                            'reopen_token'      => null,
                        ]);
                        // Free up the access code so the contact can submit again
                        if ($fresh->roster_contact_id) {
                            \App\Models\AccessCode::where('tenant_id', $this->tenantId)
                                ->where('roster_contact_id', $fresh->roster_contact_id)
                                ->whereNotNull('used_at')
                                ->update(['used_at' => null]);
                        }
                        \App\Models\IssueActivity::create([
                            'issue_id' => $this->issueId,
                            'actor_id' => null,
                            'type'     => 'status_changed',
                            'data'     => ['from' => $fresh->getOriginal('status'), 'to' => 'closed', 'auto' => true, 'reason' => 'ai_compliment'],
                        ]);
                    }
                }
            }

            dispatch(new DetectIssueTrends($this->tenantId))->delay(30);
            dispatch(new DetectIssueGroups($this->tenantId))->delay(60);
        } catch (\Throwable $e) {
            $this->fail($e);
        } finally {
            tenancy()->end();
        }
    }
}
