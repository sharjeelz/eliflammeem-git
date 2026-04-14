<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueTrendDetectedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $theme,
        public readonly int $count,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'theme'   => $this->theme,
            'count'   => $this->count,
            'message' => "{$this->count} issues about '{$this->theme}' in the last 7 days — possible systemic issue.",
            'url'     => url('admin/issues'),
        ];
    }
}
