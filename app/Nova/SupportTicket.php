<?php

namespace App\Nova;

use App\Models\SupportTicket as SupportTicketModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class SupportTicket extends Resource
{
    public static $model = SupportTicketModel::class;

    public static $title = 'subject';

    public static $search = ['subject', 'user_name', 'user_email', 'tenant_name', 'message'];

    public static function label(): string
    {
        return 'Support Tickets';
    }

    public static function singularLabel(): string
    {
        return 'Support Ticket';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('School', 'tenant_name')
                ->sortable()
                ->readonly(),

            Text::make('Submitted By', 'user_name')
                ->sortable()
                ->readonly(),

            Text::make('Email', 'user_email')
                ->onlyOnDetail(),

            Text::make('Subject')
                ->readonly()
                ->sortable(),

            Select::make('Type')
                ->options([
                    'bug'             => 'Bug / Error',
                    'question'        => 'Question',
                    'billing'         => 'Billing',
                    'feature_request' => 'Feature Request',
                    'other'           => 'Other',
                ])
                ->displayUsingLabels()
                ->sortable()
                ->readonly(),

            Badge::make('Priority')
                ->map([
                    'low'    => 'success',
                    'medium' => 'warning',
                    'high'   => 'danger',
                    'urgent' => 'danger',
                ]),

            Badge::make('Status')
                ->map([
                    'open'        => 'warning',
                    'in_progress' => 'info',
                    'resolved'    => 'success',
                ]),

            Select::make('Status')
                ->options([
                    'open'        => 'Open',
                    'in_progress' => 'In Progress',
                    'resolved'    => 'Resolved',
                ])
                ->displayUsingLabels()
                ->onlyOnForms()
                ->rules('required'),

            Textarea::make('Message')
                ->readonly()
                ->hideFromIndex()
                ->alwaysShow(),

            Textarea::make('Admin Notes', 'admin_notes')
                ->nullable()
                ->help('Internal notes — not visible to the school'),

            DateTime::make('Submitted', 'created_at')
                ->sortable()
                ->readonly(),
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
