<?php

namespace App\Nova\Metrics;

use App\Models\Issue;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class IssuesByStatus extends Partition
{
    public function name(): string { return 'Issues by Status'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\PartitionResult
    {
        return $this->count($request, Issue::class, 'status')
            ->label(fn ($v) => match ($v) {
                'new'         => 'New',
                'in_progress' => 'In Progress',
                'resolved'    => 'Resolved',
                'closed'      => 'Closed',
                default       => ucfirst($v ?? 'Unknown'),
            });
    }

    public function colors(): array
    {
        return [
            'new'         => '#f1416c',
            'in_progress' => '#ffc700',
            'resolved'    => '#50cd89',
            'closed'      => '#b5b5c3',
        ];
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
