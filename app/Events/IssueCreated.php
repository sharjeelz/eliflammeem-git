<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;

class IssueCreated
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int $issueId,
        public readonly string $tenantId,
        public readonly string $description,
        public readonly string $title = '',
        public readonly ?int $categoryId = null,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
