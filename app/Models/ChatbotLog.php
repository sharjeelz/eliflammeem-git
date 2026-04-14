<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class ChatbotLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'question',
        'answer',
        'confidence',
        'chunks_found',
        'faqs_matched',
        'used_fallback',
        'metadata_filters',
        'sources',
        'response_ms',
        'ip_address',
    ];

    protected $casts = [
        'metadata_filters' => 'array',
        'sources'          => 'array',
        'used_fallback'    => 'boolean',
        'confidence'       => 'float',
        'chunks_found'     => 'integer',
        'faqs_matched'     => 'integer',
        'response_ms'      => 'integer',
    ];

    public function getConfidenceLabelAttribute(): string
    {
        // No answer only when BOTH chunks and FAQs contributed nothing
        if ($this->chunks_found === 0 && ($this->faqs_matched ?? 0) === 0) {
            return 'no-answer';
        }

        return match (true) {
            $this->confidence >= 0.8 => 'high',
            $this->confidence >= 0.5 => 'medium',
            default                  => 'low',
        };
    }
}
