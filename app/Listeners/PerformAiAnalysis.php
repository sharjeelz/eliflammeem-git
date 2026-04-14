<?php

namespace App\Listeners;

use App\Events\IssueCreated;
use App\Jobs\DetectIssueGroups;
use App\Jobs\DetectIssueTrends;
use App\Models\Issue;
use App\Models\IssueAiAnalysis;
use App\Models\IssueCategory;
use App\Models\Tenant;
use App\Services\AiAnalysisService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PerformAiAnalysis implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(protected AiAnalysisService $ai) {}

    public function handle(IssueCreated $event): void
    {
        tenancy()->initialize(Tenant::find($event->tenantId));

        try {
            $issue = Issue::find($event->issueId);

            if (! $issue || $issue->is_spam || $issue->is_anonymous) {
                return;
            }

            $categoryName = $event->categoryId
                ? IssueCategory::find($event->categoryId)?->name
                : null;

            $data = $this->ai->analyze($event->description, $event->title, $categoryName);

            if (empty($data)) {
                return;
            }

            IssueAiAnalysis::updateOrCreate(
                [
                    'tenant_id'     => $event->tenantId,
                    'issue_id'      => $event->issueId,
                    'analysis_type' => 'full',
                ],
                [
                    'result'        => $data,
                    'confidence'    => $data['sentiment_score'] ?? null,
                    'model_version' => $data['model_version'] ?? null,
                ]
            );
            // Stamp submission_type on the issue from AI classification
            if (! empty($data['submission_type'])) {
                Issue::where('id', $event->issueId)
                    ->update(['submission_type' => $data['submission_type']]);

                // Auto-close compliments immediately — no CSAT, no email, no assignment needed
                if ($data['submission_type'] === 'compliment') {
                    $fresh = Issue::find($event->issueId);
                    if ($fresh && $fresh->status !== 'closed') {
                        $fresh->update([
                            'status'            => 'closed',
                            'status_entered_at' => now(),
                            'last_activity_at'  => now(),
                            'reopen_token'      => null,
                        ]);
                        // Free up the access code so the contact can submit again
                        if ($fresh->roster_contact_id) {
                            \App\Models\AccessCode::where('tenant_id', $event->tenantId)
                                ->where('roster_contact_id', $fresh->roster_contact_id)
                                ->whereNotNull('used_at')
                                ->update(['used_at' => null]);
                        }
                        \App\Models\IssueActivity::create([
                            'issue_id' => $event->issueId,
                            'actor_id' => null,
                            'type'     => 'status_changed',
                            'data'     => ['from' => $fresh->getOriginal('status'), 'to' => 'closed', 'auto' => true, 'reason' => 'ai_compliment'],
                        ]);
                    }
                }
            }

            dispatch(new DetectIssueTrends($event->tenantId))->delay(30);
            dispatch(new DetectIssueGroups($event->tenantId))->delay(60);
        } catch (\Throwable $e) {
            $this->fail($e);
        } finally {
            tenancy()->end();
        }
    }
}
