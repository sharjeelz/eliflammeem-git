<?php

namespace App\Nova;

use App\Models\AiUsageLog as AiUsageLogModel;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class AiUsageLog extends Resource
{
    public static $model = AiUsageLogModel::class;

    public static $title = 'id';

    public static $search = ['tenant_id', 'call_type', 'model'];

    public static $group = 'AI';

    public static function label(): string
    {
        return 'AI Usage Logs';
    }

    public static function singularLabel(): string
    {
        return 'AI Usage Log';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Tenant', 'tenant_id')
                ->sortable()
                ->filterable()
                ->nullable(),

            Text::make('Call Type', 'call_type')
                ->sortable()
                ->filterable(),

            Text::make('Model')
                ->sortable()
                ->filterable(),

            Number::make('Prompt Tokens', 'prompt_tokens')
                ->sortable(),

            Number::make('Completion Tokens', 'completion_tokens')
                ->sortable(),

            Text::make('Cost (USD)', 'cost_usd')
                ->resolveUsing(fn ($v) => '$' . number_format((float) $v, 6))
                ->sortable(),

            DateTime::make('Created At', 'created_at')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    public static function authorizedToCreate($request): bool
    {
        return false;
    }

    public function authorizedToUpdate($request): bool
    {
        return false;
    }

    public function authorizedToDelete($request): bool
    {
        return false;
    }

    public static function indexQuery(NovaRequest $request, $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->orderByDesc('created_at');
    }
}
