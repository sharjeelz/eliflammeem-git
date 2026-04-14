<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;
use Stancl\Tenancy\Database\Models\Domain as DomainModel;

class Domain extends Resource
{
    public static $model = DomainModel::class;

    public static $title = 'domain';

    public static $search = ['domain'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Tenant', 'tenant', Tenant::class)
                ->required(),
            Text::make('Domain')
                ->help('e.g. schoola.lvh.me')
                ->rules('required', 'max:191')
                ->updateRules(Rule::unique('domains', 'domain')->ignore($this->id)
                )
                ->sortable(),
        ];
    }

    public function filters(Request $request)
    {
        return [new \App\Nova\Filters\TenantFilter];
    }
}
