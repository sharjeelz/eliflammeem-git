<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class AiUsageLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'call_type',
        'model',
        'prompt_tokens',
        'completion_tokens',
        'cost_usd',
    ];

    protected $casts = [
        'prompt_tokens'     => 'integer',
        'completion_tokens' => 'integer',
        'cost_usd'          => 'float',
    ];

    /**
     * Pricing per 1 million tokens (USD).
     * Input price applies to prompt tokens; output price to completion tokens.
     */
    private const PRICING = [
        'gpt-4o-mini'                => ['input' => 0.15,  'output' => 0.60],
        'gpt-4o'                     => ['input' => 2.50,  'output' => 10.00],
        'text-embedding-3-small'     => ['input' => 0.02,  'output' => 0.0],
        'text-embedding-3-large'     => ['input' => 0.13,  'output' => 0.0],
        'claude-haiku-4-5-20251001'  => ['input' => 0.80,  'output' => 4.00],
        'claude-haiku-4-5'           => ['input' => 0.80,  'output' => 4.00],
        'claude-sonnet-4-6'          => ['input' => 3.00,  'output' => 15.00],
        'claude-opus-4-6'            => ['input' => 15.00, 'output' => 75.00],
    ];

    public static function record(
        string  $callType,
        string  $model,
        int     $promptTokens,
        int     $completionTokens = 0,
        ?string $tenantId = null,
    ): void {
        // Exact match first, then prefix match for versioned model names (e.g. gpt-4o-mini-2024-07-18)
        $pricing = self::PRICING[$model] ?? null;
        if (! $pricing) {
            foreach (self::PRICING as $key => $price) {
                if (str_starts_with($model, $key)) {
                    $pricing = $price;
                    break;
                }
            }
        }
        $pricing ??= ['input' => 0.0, 'output' => 0.0];
        $costUsd   = ($promptTokens * $pricing['input'] + $completionTokens * $pricing['output']) / 1_000_000;

        try {
            self::create([
                'tenant_id'         => $tenantId ?? (function_exists('tenant') ? tenant('id') : null),
                'call_type'         => $callType,
                'model'             => $model,
                'prompt_tokens'     => $promptTokens,
                'completion_tokens' => $completionTokens,
                'cost_usd'          => $costUsd,
            ]);
        } catch (\Throwable $e) {
            Log::warning('AiUsageLog: failed to record usage', [
                'error'      => $e->getMessage(),
                'call_type'  => $callType,
                'model'      => $model,
            ]);
        }
    }
}
