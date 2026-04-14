<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IssueGroupItem extends Model
{
    use BelongsToTenant;

    public $timestamps = false;

    protected $fillable = [
        'tenant_id',
        'issue_group_id',
        'issue_id',
        'removed_at',
    ];

    protected $casts = [
        'removed_at' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(IssueGroup::class, 'issue_group_id');
    }

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }
}
