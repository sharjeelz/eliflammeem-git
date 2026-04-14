<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class IssueNote extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'issue_id', 'user_id', 'content'];

    public function issue()
    {
        return $this->belongsTo(Issue::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
