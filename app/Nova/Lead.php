<?php

namespace App\Nova;

use App\Models\Lead as LeadModel;
use App\Nova\Actions\ApproveLead;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Lead extends Resource
{
    public static $model = LeadModel::class;

    public static $title = 'name';

    public static $search = ['name', 'email', 'school_name', 'city'];

    public static function label(): string
    {
        return 'Leads';
    }

    public static function singularLabel(): string
    {
        return 'Lead';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')
                ->rules('required', 'max:150')
                ->readonly(fn ($request) => $request->isUpdateOrUpdateAttachedRequest())
                ->sortable(),

            Text::make('Email')
                ->rules('required', 'email', 'max:150')
                ->readonly(fn ($request) => $request->isUpdateOrUpdateAttachedRequest())
                ->sortable(),

            Text::make('Phone')
                ->nullable()
                ->rules('nullable', 'max:50'),

            Text::make('School Name', 'school_name')
                ->nullable()
                ->sortable(),

            Text::make('City')
                ->nullable()
                ->sortable(),

            Select::make('Package')
                ->options([
                    'starter'    => 'Starter',
                    'growth'     => 'Growth',
                    'pro'        => 'Pro',
                    'enterprise' => 'Enterprise',
                    'custom'     => 'Custom / Not Sure',
                ])
                ->displayUsingLabels()
                ->nullable()
                ->sortable(),

            Select::make('Status')
                ->options([
                    'new'       => 'New',
                    'contacted' => 'Contacted',
                    'approved'  => 'Approved',
                    'rejected'  => 'Rejected',
                ])
                ->displayUsingLabels()
                ->sortable()
                ->rules('required'),

            Textarea::make('Message')
                ->nullable()
                ->hideFromIndex()
                ->alwaysShow(),

            Textarea::make('Notes')
                ->nullable()
                ->help('Internal notes — not visible to the lead'),

            Text::make('IP Address', 'ip_address')
                ->onlyOnDetail(),

            DateTime::make('Approved At', 'approved_at')
                ->onlyOnDetail(),

            DateTime::make('Submitted', 'created_at')
                ->onlyOnDetail(),
        ];
    }

    public function filters(Request $request): array
    {
        return [
            new \App\Nova\Filters\LeadStatusFilter,
        ];
    }

    public function actions(Request $request): array
    {
        return [
            new ApproveLead,
        ];
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return false;
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
