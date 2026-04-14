<?php

namespace App\Nova\Metrics;

use App\Models\AiUsageLog;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Partition;

class AiCallsByType extends Partition
{
    public function name(): string { return 'AI Calls by Type'; }

    public function calculate(NovaRequest $request): \Laravel\Nova\Metrics\PartitionResult
    {
        return $this->count($request, AiUsageLog::class, 'call_type')
            ->label(fn ($v) => match ($v) {
                'issue_analysis'   => 'Issue Analysis',
                'trend_detection'  => 'Trend Detection',
                'chatbot'          => 'Chatbot',
                'embedding'        => 'Embedding',
                default            => ucwords(str_replace('_', ' ', $v ?? 'Unknown')),
            });
    }

    public function cacheFor(): \DateTimeInterface|\DateInterval|float|int|null
    {
        return now()->addMinutes(5);
    }
}
