<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Collection;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class AgentConversationMessage extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'agent_conversation_messages';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'user_id',
        'agent',
        'role',
        'content',
        'attachments',
        'tool_calls',
        'tool_results',
        'usage',
        'meta',
        'source_chunk_ids',
        'confidence_score',
    ];

    protected $casts = [
        'tenant_id' => 'string',
        'conversation_id' => 'string',
        'user_id' => 'integer',
        'attachments' => 'array',
        'tool_calls' => 'array',
        'tool_results' => 'array',
        'usage' => 'array',
        'meta' => 'array',
        'source_chunk_ids' => 'array',
        'confidence_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Set agent name if not provided
            if (empty($model->agent)) {
                $model->agent = 'school-chatbot';
            }

            // Initialize empty arrays for JSON fields
            if (empty($model->attachments)) {
                $model->attachments = [];
            }
            if (empty($model->tool_calls)) {
                $model->tool_calls = [];
            }
            if (empty($model->tool_results)) {
                $model->tool_results = [];
            }
            if (empty($model->usage)) {
                $model->usage = [];
            }
            if (empty($model->meta)) {
                $model->meta = [];
            }
        });
    }

    /**
     * Get the conversation this message belongs to.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AgentConversation::class, 'conversation_id');
    }

    /**
     * Get the user who sent this message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the document chunks referenced in this message.
     */
    public function sourceChunks(): Collection
    {
        if (empty($this->source_chunk_ids)) {
            return collect([]);
        }

        // BelongsToTenant trait automatically scopes to current tenant
        return DocumentChunk::whereIn('id', $this->source_chunk_ids)->get();
    }

    /**
     * Check if this is a user message.
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message.
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Get the reasoning explanation from meta data.
     */
    public function getReasoning(): ?string
    {
        return $this->meta['reasoning'] ?? null;
    }

    /**
     * Get the documents referenced in this response.
     */
    public function getSourceDocuments(): Collection
    {
        $chunks = $this->sourceChunks();
        
        if ($chunks->isEmpty()) {
            return collect([]);
        }

        $documentIds = $chunks->pluck('document_id')->unique();
        
        // BelongsToTenant trait automatically scopes to current tenant
        return Document::whereIn('id', $documentIds)->get();
    }
}
