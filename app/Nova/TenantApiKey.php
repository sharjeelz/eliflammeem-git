<?php

namespace App\Nova;

use App\Models\TenantApiKey as TenantApiKeyModel;
use App\Nova\Actions\RevokeApiKey;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class TenantApiKey extends Resource
{
    public static $model = TenantApiKeyModel::class;

    public static $title = 'name';

    public static $search = ['name', 'tenant_id', 'key_prefix'];

    // Only shown nested under Tenant via HasMany, not in the main nav
    public static $displayInNavigation = false;

    public static function label(): string
    {
        return 'API Keys';
    }

    public static function singularLabel(): string
    {
        return 'API Key';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Text::make('Name')->sortable()->rules('required', 'max:100'),

            Text::make('Prefix', 'key_prefix')
                ->displayUsing(fn ($v) => $v . '...')
                ->exceptOnForms(),

            Text::make('Tenant ID', 'tenant_id')
                ->onlyOnDetail(),

            Badge::make('Status')->map([
                'Active'  => 'success',
                'Revoked' => 'danger',
                'Expired' => 'warning',
            ])->resolveUsing(function () {
                if ($this->revoked_at) {
                    return 'Revoked';
                }
                if ($this->expires_at?->isPast()) {
                    return 'Expired';
                }
                return 'Active';
            })->exceptOnForms(),

            DateTime::make('Last Used', 'last_used_at')->nullable()->sortable()->exceptOnForms(),

            DateTime::make('Expires At', 'expires_at')->nullable()->hideFromIndex(),

            DateTime::make('Revoked At', 'revoked_at')->nullable()->hideFromIndex(),

            DateTime::make('Created At', 'created_at')->sortable()->exceptOnForms(),

            // Usage stats — only on detail view
            Text::make('Calls Today')->onlyOnDetail()->resolveUsing(function () {
                return (string) DB::table('api_request_logs')
                    ->where('api_key_id', $this->id)
                    ->whereDate('created_at', today())
                    ->count();
            }),

            Text::make('Calls (30d)')->onlyOnDetail()->resolveUsing(function () {
                return (string) DB::table('api_request_logs')
                    ->where('api_key_id', $this->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
            }),

            HasMany::make('Request Logs', 'requestLogs', ApiRequestLog::class),
        ];
    }

    public function actions(NovaRequest $request): array
    {
        return [
            new RevokeApiKey,
        ];
    }

    public static function authorizedToCreate($request): bool
    {
        return false; // Keys are generated via tenant settings panel
    }
}
