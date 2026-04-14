<?php

// app/Nova/Filters/UsersByTenant.php

namespace App\Nova\Filters;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Laravel\Nova\Filters\Filter;

class UsersByTenant extends Filter
{
    public $name = 'Tenant';

    public function apply(Request $request, $query, $value)
    {
        app('log')->info('Applying UsersByTenant filter with value: '.$value);

        return $value ? $query->where('tenant_id', $value) : $query;
    }

    public function options(Request $request)
    {

        return Tenant::orderBy('name')
            ->get(['id', 'name'])
            ->pluck('id', 'name')  // Nova expects [Label => value]
            ->toArray();
    }
}
