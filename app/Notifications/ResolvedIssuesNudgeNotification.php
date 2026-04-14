<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class ResolvedIssuesNudgeNotification extends Notification
{
    public function __construct(
        public readonly int $count,
        public readonly string $tenantId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'resolved_nudge',
            'count'   => $this->count,
            'message' => "{$this->count} " . ($this->count === 1 ? 'issue has' : 'issues have') . " been resolved for 7+ days with no parent response. Consider closing them.",
            'url'     => url('/admin/issues?status=resolved'),
        ];
    }
}
