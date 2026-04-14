<?php

namespace App\Notifications;

use App\Models\Issue;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IssueCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Issue $issue,
        public readonly User $actor,
        public readonly string $preview,
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
            'actor_name' => $this->actor->name,
            'message' => "{$this->actor->name} commented: ".Str::limit($this->preview, 80),
            'url' => url("admin/issues/{$this->issue->id}"),
        ];
    }
}
