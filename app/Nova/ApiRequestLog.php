<?php

namespace App\Nova;

use App\Models\ApiRequestLog as ApiRequestLogModel;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ApiRequestLog extends Resource
{
    public static $model = ApiRequestLogModel::class;

    public static $title = 'id';

    public static $search = ['endpoint', 'ip', 'tenant_id'];

    // Only visible nested under TenantApiKey via HasMany, not in the main nav
    public static $displayInNavigation = false;

    public static function label(): string
    {
        return 'API Request Logs';
    }

    public static function singularLabel(): string
    {
        return 'API Request Log';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            DateTime::make('When', 'created_at')->sortable()->exceptOnForms(),

            Text::make('Endpoint')->sortable()->exceptOnForms(),

            Badge::make('Status', 'status_code')->map([
                '2xx' => 'success',
                '4xx' => 'warning',
                '5xx' => 'danger',
            ])->resolveUsing(function () {
                $code = (int) $this->status_code;
                if ($code >= 200 && $code < 300) return '2xx';
                if ($code >= 400 && $code < 500) return '4xx';
                return '5xx';
            })->exceptOnForms(),

            Text::make('HTTP Status', 'status_code')->onlyOnDetail(),

            Text::make('IP', 'ip')->exceptOnForms(),

            Text::make('Duration', 'duration_ms')
                ->resolveUsing(fn ($v) => $v !== null ? "{$v} ms" : '—')
                ->exceptOnForms(),

            BelongsTo::make('API Key', 'apiKey', TenantApiKey::class)
                ->nullable()
                ->exceptOnForms(),

            // Request body — shown formatted on detail, summarised on index
            Text::make('Contact Name', 'request_body')
                ->resolveUsing(fn ($v) => is_array($v) ? ($v['name'] ?? '—') : '—')
                ->hideFromDetail()
                ->exceptOnForms(),

            Text::make('Contact Email', 'request_body')
                ->resolveUsing(fn ($v) => is_array($v) ? ($v['email'] ?? '—') : '—')
                ->hideFromDetail()
                ->exceptOnForms(),

            Code::make('Request Body', 'request_body')
                ->json()
                ->resolveUsing(fn ($v) => $v ? json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                ->onlyOnDetail()
                ->nullable(),

            Code::make('Response', 'response_body')
                ->json()
                ->resolveUsing(fn ($v) => $v ? json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : null)
                ->onlyOnDetail()
                ->nullable(),
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
