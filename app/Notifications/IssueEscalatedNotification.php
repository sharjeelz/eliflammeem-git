<?php

namespace App\Notifications;

use App\Models\EscalationRule;
use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueEscalatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Issue $issue,
        public readonly EscalationRule $rule,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'issue_id'        => $this->issue->id,
            'issue_public_id' => $this->issue->public_id,
            'issue_title'     => $this->issue->title,
            'rule_name'       => $this->rule->name,
            'message'         => "Issue #{$this->issue->public_id} needs attention: {$this->rule->name}",
            'url'             => "/admin/issues/{$this->issue->id}",
            'type'            => 'escalation',
        ];
    }
}
