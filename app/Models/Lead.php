<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'leads';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'school_name',
        'city',
        'package',
        'message',
        'status',
        'notes',
        'ip_address',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function scopeNew(Builder $q): Builder
    {
        return $q->where('status', 'new');
    }
}
