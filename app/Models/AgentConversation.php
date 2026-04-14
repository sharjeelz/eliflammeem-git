<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class AgentConversation extends Model
{
    use HasUuids, BelongsToTenant;

    protected $table = 'agent_conversations';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
    ];

    protected $casts = [
        'tenant_id' => 'string',
        'user_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the messages for this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(AgentConversationMessage::class, 'conversation_id');
    }

    /**
     * Get the user who owns this conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the last message in the conversation.
     */
    public function lastMessage()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get all user messages in the conversation.
     */
    public function userMessages(): HasMany
    {
        return $this->messages()->where('role', 'user');
    }

    /**
     * Get all assistant messages in the conversation.
     */
    public function assistantMessages(): HasMany
    {
        return $this->messages()->where('role', 'assistant');
    }
}
