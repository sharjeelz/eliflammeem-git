<?php

namespace App\Nova;

use App\Models\RosterContact as ContactModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class RosterContact extends Resource
{
    public static $model = ContactModel::class;

    public static $title = 'name';

    public static $search = ['name', 'email', 'phone', 'external_id'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Tenant')->searchable()->sortable(),

            BelongsTo::make('School')->nullable()->searchable()->sortable(),

            BelongsTo::make('Branch')->nullable()->searchable()->sortable(),

            Select::make('Role')->options([
                'parent'  => 'Parent',
                'teacher' => 'Teacher',
                'admin'   => 'Admin',
            ])->displayUsingLabels()->sortable(),

            Text::make('Name')->rules('required', 'max:150')->sortable(),

            Text::make('Email')->nullable()->sortable(),

            Text::make('Phone')->nullable()->sortable(),

            Text::make('External ID')->nullable()->sortable()->hideFromIndex(),

            HasMany::make('Access Codes', 'accessCodes', AccessCode::class),
        ];
    }

    public function filters(Request $request)
    {
        return [new \App\Nova\Filters\TenantFilter];
    }
}
