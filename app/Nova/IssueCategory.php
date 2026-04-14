<?php

namespace App\Nova;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class IssueCategory extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\IssueCategory>
     */
    public static $model = \App\Models\IssueCategory::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = ['id', 'name', 'slug'];

    public static function label()
    {
        return 'Issue Categories';
    }

    public static $group = 'Schools';

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            // Central Nova: pick which tenant (school) this category belongs to
            BelongsTo::make('Tenant', 'tenant', Tenant::class)
                ->searchable()
                ->rules('required'),

            Text::make('Name')
                ->rules('required', 'max:191'),

            Text::make('Slug')
                ->help('Unique per tenant. Lowercase, a–z, 0–9, hyphens.')
                ->rules(function () use ($request) {
                    // unique per-tenant: unique(issue_categories, slug, exceptId, id, tenant_id, selectedTenant)
                    $id = optional($this->resource)->id ?? 'NULL';
                    $tenantId = $request->input('tenant') ?: optional($this->resource)->tenant_id;

                    return [
                        'required',
                        'max:191',
                        'regex:/^[a-z0-9-]+$/',
                        "unique:issue_categories,slug,{$id},id,tenant_id,{$tenantId}",
                    ];
                }),

            Number::make('Default SLA Hours', 'default_sla_hours')
                ->min(0)->step(1)->help('Used to auto-set SLA on new issues of this category.'),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [new \App\Nova\Filters\TenantFilter];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }

    public static function indexQuery(NovaRequest $request, Builder $query): Builder
    {
        return $query->orderBy('tenant_id')->orderBy('name');
    }

    public static function relatableQuery(NovaRequest $request, Builder $query): Builder
    {
        // When picking a Tenant in the BelongsTo, show all tenants (central)
        return $query;
    }
}
