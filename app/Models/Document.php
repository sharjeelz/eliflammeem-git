<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class Document extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'title',
        'slug',
        'description',
        'disk',
        'path',
        'mime',
        'size',
        'type',
        'include_in_chatbot',
        'allow_public_download',
        'searchable_content',
        'text_extraction_status',
        'text_extraction_error',
        'text_extracted_at',
        'text_extraction_attempts',
        'embedding_status',
        'embedding_error',
        'embeddings_generated_at',
        'chunk_count',
        'embedding_attempts',
        'meta',
        'display_order',
        'published_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'include_in_chatbot'    => 'boolean',
        'allow_public_download' => 'boolean',
        'published_at' => 'datetime',
        'text_extracted_at' => 'datetime',
        'embeddings_generated_at' => 'datetime',
        'size' => 'integer',
        'text_extraction_attempts' => 'integer',
        'chunk_count' => 'integer',
        'embedding_attempts' => 'integer',
    ];

    /**
     * Get the category this document belongs to.
     */
    public function category()
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    /**
     * Get all chunks for this document.
     */
    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class);
    }

    /**
     * Whether the document is published (published_at is set and in the past).
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    /**
     * Get human-readable file size.
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    /**
     * Get file extension from path.
     */
    public function getFileExtensionAttribute(): string
    {
        return strtoupper(pathinfo($this->path, PATHINFO_EXTENSION));
    }

    /**
     * Get icon class based on file type.
     */
    public function getFileIconAttribute(): string
    {
        return match (strtolower($this->file_extension)) {
            'pdf' => 'ki-file-down text-danger',
            'doc', 'docx' => 'ki-file text-primary',
            'txt' => 'ki-text text-info',
            default => 'ki-document-cloud text-gray-500',
        };
    }

    /**
     * Scope: Get only documents available for AI chatbot.
     * These documents will be used to answer parent questions.
     */
    public function scopeForChatbot($query)
    {
        return $query->where('include_in_chatbot', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: Filter by document type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope: Filter by text extraction status.
     */
    public function scopeByExtractionStatus($query, $status)
    {
        return $query->where('text_extraction_status', $status);
    }

    /**
     * Scope: Get documents with completed text extraction.
     */
    public function scopeTextExtracted($query)
    {
        return $query->where('text_extraction_status', 'completed');
    }

    /**
     * Scope: Get documents with failed text extraction.
     */
    public function scopeExtractionFailed($query)
    {
        return $query->where('text_extraction_status', 'failed');
    }

    /**
     * Scope: Get documents pending text extraction.
     */
    public function scopeExtractionPending($query)
    {
        return $query->where('text_extraction_status', 'pending');
    }

    /**
     * Check if document supports text extraction.
     */
    public function supportsTextExtraction(): bool
    {
        $extension = strtolower($this->file_extension);
        return in_array($extension, ['pdf', 'docx', 'txt']);
    }

    /**
     * Check if text extraction is completed.
     */
    public function isTextExtracted(): bool
    {
        return $this->text_extraction_status === 'completed';
    }

    /**
     * Check if text extraction failed.
     */
    public function hasExtractionFailed(): bool
    {
        return $this->text_extraction_status === 'failed';
    }

    /**
     * Get text extraction status badge color.
     */
    public function getExtractionStatusBadgeAttribute(): string
    {
        return match ($this->text_extraction_status) {
            'completed' => 'success',
            'processing' => 'primary',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get text extraction status label.
     */
    public function getExtractionStatusLabelAttribute(): string
    {
        return match ($this->text_extraction_status) {
            'completed' => 'Extracted',
            'processing' => 'Processing',
            'failed' => 'Failed',
            'pending' => 'Pending',
            default => 'Unknown',
        };
    }

    // ========================================
    // Embedding-related Methods (Phase 3)
    // ========================================

    /**
     * Scope: Filter by embedding status.
     */
    public function scopeByEmbeddingStatus($query, $status)
    {
        return $query->where('embedding_status', $status);
    }

    /**
     * Scope: Get documents with completed embeddings.
     */
    public function scopeEmbeddingsGenerated($query)
    {
        return $query->where('embedding_status', 'completed');
    }

    /**
     * Scope: Get documents with failed embedding generation.
     */
    public function scopeEmbeddingsFailed($query)
    {
        return $query->where('embedding_status', 'failed');
    }

    /**
     * Scope: Get documents pending embedding generation.
     */
    public function scopeEmbeddingsPending($query)
    {
        return $query->where('embedding_status', 'pending');
    }

    /**
     * Scope: Get documents ready for chatbot (text extracted + embeddings generated + published).
     */
    public function scopeReadyForChatbot($query)
    {
        return $query->where('include_in_chatbot', true)
            ->where('text_extraction_status', 'completed')
            ->where('embedding_status', 'completed')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Check if embeddings have been generated.
     */
    public function hasEmbeddings(): bool
    {
        return $this->embedding_status === 'completed' && $this->chunk_count > 0;
    }

    /**
     * Check if embedding generation failed.
     */
    public function hasEmbeddingsFailed(): bool
    {
        return $this->embedding_status === 'failed';
    }

    /**
     * Check if document is ready for embedding generation.
     * (Must have extracted text and be included in chatbot)
     */
    public function isReadyForEmbedding(): bool
    {
        return $this->text_extraction_status === 'completed'
            && ! empty($this->searchable_content)
            && $this->include_in_chatbot === true;
    }

    /**
     * Get embedding status badge color.
     */
    public function getEmbeddingStatusBadgeAttribute(): string
    {
        return match ($this->embedding_status) {
            'completed' => 'success',
            'processing' => 'primary',
            'failed' => 'danger',
            'pending' => 'warning',
            default => 'secondary',
        };
    }

    /**
     * Get embedding status label.
     */
    public function getEmbeddingStatusLabelAttribute(): string
    {
        return match ($this->embedding_status) {
            'completed' => 'Ready',
            'processing' => 'Generating',
            'failed' => 'Failed',
            'pending' => 'Pending',
            default => 'Unknown',
        };
    }
}
