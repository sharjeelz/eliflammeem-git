<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class BroadcastBatch extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'subject', 'message', 'channel', 'audience_type',
        'audience_filter', 'media_type', 'media_path', 'media_filename',
        'media_mime_type', 'whatsapp_template_id', 'template_parameters',
        'total_count', 'sent_count', 'failed_count',
    ];

    protected $casts = [
        'audience_filter' => 'array',
        'template_parameters' => 'array',
    ];

    public function recipients()
    {
        return $this->hasMany(BroadcastRecipient::class);
    }

    public function whatsappTemplate()
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'whatsapp_template_id');
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    public function getPendingCountAttribute()
    {
        return $this->total_count - $this->sent_count - $this->failed_count;
    }
}
