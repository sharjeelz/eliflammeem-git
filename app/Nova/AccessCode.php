<?php

namespace App\Nova;

use App\Models\AccessCode as AccessCodeModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class AccessCode extends Resource
{
    public static $model = AccessCodeModel::class;

    public static $title = 'code';

    public static $search = ['code'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Tenant')->searchable()->sortable(),

            BelongsTo::make('Roster Contact', 'contact', RosterContact::class)
                ->searchable()
                ->sortable(),

            BelongsTo::make('Branch')->nullable()->searchable()->sortable(),

            Text::make('Code')
                ->rules('required', 'max:64')
                ->creationRules('unique:access_codes,code,NULL,id,tenant_id,'.$this->tenant_id)
                ->updateRules('unique:access_codes,code,{{resourceId}},id,tenant_id,'.$this->tenant_id)
                ->sortable()
                ->copyable(),

            Select::make('Channel')->options([
                'email'  => 'Email',
                'sms'    => 'SMS',
                'manual' => 'Manual',
                'csv'    => 'CSV Import',
                'api'    => 'API',
            ])->displayUsingLabels()->nullable()->sortable(),

            Badge::make('Status', function () {
                if ($this->used_at) {
                    return 'used';
                }
                if ($this->expires_at && $this->expires_at->isPast()) {
                    return 'expired';
                }

                return 'active';
            })->map([
                'active'  => 'success',
                'expired' => 'warning',
                'used'    => 'danger',
            ])->labels([
                'active'  => 'Active',
                'expired' => 'Expired',
                'used'    => 'Used',
            ])->exceptOnForms(),

            DateTime::make('Expires At', 'expires_at')
                ->nullable()
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Used At', 'used_at')
                ->nullable()
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Sent At', 'sent_at')
                ->nullable()
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Created At', 'created_at')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    public function filters(Request $request)
    {
        return [new \App\Nova\Filters\TenantFilter];
    }
}
