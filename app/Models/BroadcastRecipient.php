<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class BroadcastRecipient extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'broadcast_batch_id', 'contact_id', 'contact_name', 'contact_email',
        'contact_phone', 'status', 'message_id', 'delivery_status',
        'sent_at', 'delivered_at', 'read_at', 'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(BroadcastBatch::class, 'broadcast_batch_id');
    }

    public function contact()
    {
        return $this->belongsTo(RosterContact::class);
    }

    public function markAsSent(?string $messageId = null)
    {
        $updates = [
            'status' => 'sent',
            'sent_at' => now(),
            'error_message' => null,
            'delivery_status' => 'sent',
        ];

        if ($messageId) {
            $updates['message_id'] = $messageId;
        }

        $this->update($updates);

        $this->batch->increment('sent_count');
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);

        $this->batch->increment('failed_count');
    }

    public function resetForRetry()
    {
        $previousStatus = $this->status;

        $this->update([
            'status' => 'pending',
            'error_message' => null,
            'sent_at' => null,
        ]);

        if ($previousStatus === 'sent') {
            $this->batch->decrement('sent_count');
        } elseif ($previousStatus === 'failed') {
            $this->batch->decrement('failed_count');
        }
    }
}
