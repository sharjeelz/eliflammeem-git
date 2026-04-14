<?php

namespace App\Nova;

use App\Models\Issue as IssueModel;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class Issue extends Resource
{
    public static $model = IssueModel::class;

    public static $title = 'public_id';

    public static $search = ['public_id', 'title', 'description'];

    public static $group = 'Issues';

    public static function label(): string
    {
        return 'Issues';
    }

    public static function singularLabel(): string
    {
        return 'Issue';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            BelongsTo::make('Tenant', 'tenant', Tenant::class)
                ->searchable()
                ->sortable(),

            BelongsTo::make('School', 'school', School::class)
                ->searchable()
                ->sortable()
                ->nullable(),

            BelongsTo::make('Branch', 'branch', Branch::class)
                ->searchable()
                ->sortable()
                ->nullable(),

            Text::make('Track Code', 'public_id')
                ->sortable()
                ->copyable(),

            Text::make('Title')
                ->sortable(),

            Badge::make('Status')->map([
                'new' => 'info',
                'in_progress' => 'warning',
                'resolved' => 'success',
                'closed' => 'danger',
            ])->labels([
                'new' => 'New',
                'in_progress' => 'In Progress',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ])->exceptOnForms(),

            Select::make('Status')->options([
                'new' => 'New',
                'in_progress' => 'In Progress',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
            ])->displayUsingLabels()->sortable()->rules('required')->onlyOnForms(),

            Badge::make('Priority')->map([
                'low' => 'info',
                'medium' => 'warning',
                'high' => 'danger',
                'urgent' => 'danger',
            ])->labels([
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'urgent' => 'Urgent',
            ])->exceptOnForms(),

            Select::make('Priority')->options([
                'low' => 'Low',
                'medium' => 'Medium',
                'high' => 'High',
                'urgent' => 'Urgent',
            ])->displayUsingLabels()->sortable()->rules('required')->onlyOnForms(),

            BelongsTo::make('Category', 'issueCategory', IssueCategory::class)
                ->searchable()
                ->nullable()
                ->hideFromIndex(),

            Select::make('Source', 'source_role')->options([
                'parent' => 'Parent',
                'teacher' => 'Teacher',
                'admin' => 'Admin',
            ])->displayUsingLabels()->nullable()->sortable(),

            BelongsTo::make('Assigned To', 'assignedTo', User::class)
                ->searchable()
                ->nullable()
                ->sortable(),

            BelongsTo::make('Contact', 'roasterContact', RosterContact::class)
                ->searchable()
                ->nullable()
                ->hideFromIndex(),

            Textarea::make('Description')
                ->onlyOnDetail(),

            DateTime::make('Submitted', 'created_at')
                ->sortable()
                ->exceptOnForms()
                ->displayUsing(fn ($value) => $value?->diffForHumans()),

            DateTime::make('SLA Due At', 'sla_due_at')
                ->nullable()
                ->sortable()
                ->hideFromIndex(),

            DateTime::make('Resolved At', 'resolved_at')
                ->nullable()
                ->hideFromIndex()
                ->exceptOnForms(),

            Boolean::make('Anonymous', 'is_anonymous')
                ->readonly()
                ->sortable()
                ->filterable(),

            Boolean::make('Spam', 'is_spam')
                ->readonly()
                ->sortable()
                ->filterable(),

            Text::make('Spam Reason', 'spam_reason')
                ->readonly()
                ->hideFromIndex()
                ->nullable(),

            Text::make('AI Urgency', 'urgency_flag')
                ->resolveUsing(fn ($v, $resource) => $resource->aiAnalysis?->result['urgency_flag'] ?? null)
                ->onlyOnDetail()
                ->nullable(),

            Text::make('AI Themes', 'themes')
                ->resolveUsing(function ($v, $resource) {
                    $themes = $resource->aiAnalysis?->result['themes'] ?? [];
                    return implode(', ', $themes) ?: null;
                })
                ->onlyOnDetail()
                ->nullable(),

            Text::make('AI Summary', 'admin_summary')
                ->resolveUsing(fn ($v, $resource) => $resource->aiAnalysis?->result['admin_summary'] ?? null)
                ->onlyOnDetail()
                ->nullable(),
        ];
    }

    public function filters(NovaRequest $request): array
    {
        return [
            new Filters\TenantFilter,
        ];
    }

    public static function indexQuery(NovaRequest $request, $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderByDesc('created_at');
    }
}
