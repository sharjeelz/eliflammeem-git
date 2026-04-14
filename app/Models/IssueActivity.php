<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IssueActivity extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'issue_id', 'actor_id', 'type', 'data'];

    protected $casts = ['data' => 'array'];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
