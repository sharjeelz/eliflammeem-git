<?php

namespace App\Nova;

use App\Models\Branch as BranchModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class Branch extends Resource
{
    public static $model = BranchModel::class;

    public static $title = 'name';

    public static $search = ['name', 'code', 'city'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Tenant')->searchable()->sortable(),
            BelongsTo::make('School')->searchable()->sortable(),
            Text::make('Name')->rules('required', 'max:150')->sortable(),
            Text::make('Code')->rules('required', 'max:60')->sortable(),
            Text::make('City')->nullable()->sortable(),
            Text::make('Address')->nullable()->hideFromIndex(),
            Select::make('Status')->options([
                'active' => 'Active', 'inactive' => 'Inactive',
            ])->displayUsingLabels()->sortable(),
            // KeyValue::make('Settings')->rules('array'),
        ];
    }

    public function filters(Request $request)
    {
        return [new \App\Nova\Filters\TenantFilter];
    }
}
