<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DocumentCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'name',
        'slug',
        'description',
        'icon',
        'display_order',
    ];

    /**
     * Get all documents in this category.
     */
    public function documents()
    {
        return $this->hasMany(Document::class, 'category_id');
    }

    /**
     * Get all FAQs in this category.
     */
    public function faqs()
    {
        return $this->hasMany(Faq::class, 'category_id');
    }

    /**
     * Get parent category.
     */
    public function parent()
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children()
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')
            ->orderBy('display_order');
    }

    /**
     * Scope to get only root-level categories (no parent).
     */
    public function scopeRootCategories($query)
    {
        return $query->whereNull('parent_id')->orderBy('display_order');
    }

    /**
     * Get full category path (for breadcrumbs).
     */
    public function getFullPathAttribute(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }
}
