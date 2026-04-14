<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Faq extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'question',
        'answer',
        'display_order',
        'is_published',
        'related_document_ids',
    ];

    protected $casts = [
        'related_document_ids' => 'array',
        'is_published' => 'boolean',
        'view_count' => 'integer',
        'helpful_count' => 'integer',
        'not_helpful_count' => 'integer',
    ];

    /**
     * Get the category this FAQ belongs to.
     */
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    /**
     * Get related documents by IDs stored in JSON.
     */
    public function relatedDocuments()
    {
        if (! $this->related_document_ids || empty($this->related_document_ids)) {
            return collect();
        }

        return Document::whereIn('id', $this->related_document_ids)
            ->where('tenant_id', tenant('id'))
            ->get();
    }

    /**
     * Calculate helpfulness percentage.
     */
    public function getHelpfulPercentageAttribute(): float
    {
        $total = $this->helpful_count + $this->not_helpful_count;

        if ($total === 0) {
            return 0;
        }

        return round(($this->helpful_count / $total) * 100, 1);
    }

    /**
     * Scope: Get only published FAQs.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope: Order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('created_at', 'desc');
    }
}
