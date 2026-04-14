<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class DocumentChunk extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'document_id',
        'chunk_index',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'chunk_index' => 'integer',
    ];

    /**
     * Get the document that this chunk belongs to.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the embedding vector as an array.
     * Note: This accessor only works when using PostgreSQL with pgvector.
     */
    public function getEmbeddingAttribute($value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        // pgvector returns the vector as a string like "[0.1,0.2,0.3]"
        // We convert it to an array of floats
        if (is_string($value)) {
            $value = trim($value, '[]');
            return array_map('floatval', explode(',', $value));
        }

        return $value;
    }

    /**
     * Set the embedding vector from an array.
     */
    public function setEmbeddingAttribute($value): void
    {
        if (is_array($value)) {
            // Convert array to pgvector format: "[0.1,0.2,0.3]"
            $this->attributes['embedding'] = '[' . implode(',', $value) . ']';
        } else {
            $this->attributes['embedding'] = $value;
        }
    }

    /**
     * Generate and store embedding for this chunk.
     */
    public function generateEmbedding(): bool
    {
        try {
            $embeddingResult = Embeddings::for([$this->content])
                ->using(config('ai.embedding_model'))
                ->generate();
            
            $this->embedding = $embeddingResult->embeddings[0];
            return $this->save();
        } catch (\Exception $e) {
            Log::error('Failed to generate embedding for chunk', [
                'chunk_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
