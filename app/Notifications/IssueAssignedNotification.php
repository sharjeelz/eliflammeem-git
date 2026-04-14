<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Issue $issue,
        public readonly ?User $actor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $actorName = $this->actor?->name ?? 'System';
        return [
            'issue_id' => $this->issue->id,
            'issue_public_id' => $this->issue->public_id,
            'issue_title' => $this->issue->title,
            'actor_name' => $actorName,
            'message' => "{$actorName} assigned this issue to you.",
            'url' => url("admin/issues/{$this->issue->id}"),
        ];
    }
}
