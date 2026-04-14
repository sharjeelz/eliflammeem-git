<?php

namespace App\Nova\Filters;

use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class LeadStatusFilter extends Filter
{
    public $name = 'Status';

    public function apply(Request $request, $query, $value)
    {
        return $query->where('status', $value);
    }

    public function options(Request $request): array
    {
        return [
            'New'       => 'new',
            'Contacted' => 'contacted',
            'Approved'  => 'approved',
            'Rejected'  => 'rejected',
        ];
    }
}
