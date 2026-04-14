<?php

namespace App\Notifications;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewIssueNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Issue $issue,
        public readonly string $contactName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'issue_id' => $this->issue->id,
            'issue_public_id' => $this->issue->public_id,
            'issue_title' => $this->issue->title,
            'actor_name' => $this->contactName,
            'message' => "{$this->contactName} submitted a new issue: {$this->issue->title}",
            'url' => url("admin/issues/{$this->issue->id}"),
        ];
    }
}
