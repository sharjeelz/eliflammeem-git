<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class CsatResponse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'issue_id',
        'token',
        'rating',
        'submitted_at',
        'email_sent_at',
    ];

    protected $casts = [
        'rating'        => 'integer',
        'submitted_at'  => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
