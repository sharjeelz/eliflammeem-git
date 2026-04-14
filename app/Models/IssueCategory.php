<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IssueCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'default_sla_hours',
    ];

    protected $casts = [
        'default_sla_hours' => 'integer',
    ];

    // Each category belongs to a tenant (school)
    public function tenant()
    {
        return $this->belongsTo(\Stancl\Tenancy\Database\Models\Tenant::class, 'tenant_id');
    }

    // Category can have many issues
    public function issues()
    {
        return $this->hasMany(Issue::class, 'category_id');
    }

    // Staff assigned to handle this category
    public function assignedStaff()
    {
        return $this->belongsToMany(User::class, 'issue_category_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }
}
