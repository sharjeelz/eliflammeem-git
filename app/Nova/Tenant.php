<?php

namespace App\Nova;

use App\Models\AiUsageLog;
use App\Models\Tenant as TenantModel;
use App\Nova\Actions\ProvisionTenant;
use App\Nova\Actions\ResetSchoolData;
use App\Nova\User as NovaUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\URL;
use Laravel\Nova\Resource;

class Tenant extends Resource
{
    public static $model = TenantModel::class;

    public static $title = 'name';

    public static $search = ['id', 'data'];

    public function subtitle()
    {
        return $this->name;
    }

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            // Profile
            Text::make('Name', 'name')->rules('required', 'max:150')->sortable(),
            Text::make('Email', 'email')->rules('nullable', 'email', 'max:191')->sortable(),
            Text::make('Phone', 'phone')->rules('nullable', 'max:30')->sortable(),
            HasMany::make('Users', 'users', NovaUser::class),
            HasMany::make('Issue Categories', 'issueCategories', IssueCategory::class),
            // Plan
            Select::make('Plan', 'plan')
                ->options([
                    'starter'    => 'Starter',
                    'growth'     => 'Growth',
                    'pro'        => 'Pro',
                    'enterprise' => 'Enterprise',
                ])
                ->displayUsingLabels()
                ->sortable()
                ->default('starter')
                ->rules('required', 'in:starter,growth,pro,enterprise'),

            // Subscription dates
            Date::make('Subscription Starts', 'subscription_starts_at')
                ->nullable()
                ->help('Date the current subscription period began.'),

            Date::make('Subscription Ends', 'subscription_ends_at')
                ->nullable()
                ->help('Date the subscription expires / renews. This is the next payment due date.'),

            // Contract
            Select::make('Contract Type', 'contract_type')
                ->options(['yearly' => 'Yearly', 'monthly' => 'Monthly'])
                ->displayUsingLabels()
                ->nullable()
                ->sortable(),

            URL::make('Contract / T&C', 'contract_file_url')
                ->displayUsing(fn ($v) => $v ? 'View Contract' : '—')
                ->nullable()
                ->onlyOnDetail(),

            \Laravel\Nova\Fields\DateTime::make('Terms Accepted At', 'terms_accepted_at')
                ->nullable()
                ->onlyOnDetail(),

            Text::make('Terms Accepted IP', 'terms_accepted_ip')
                ->nullable()
                ->onlyOnDetail(),

            Text::make('Terms Version', 'terms_accepted_version')
                ->nullable()
                ->onlyOnDetail(),

            Text::make('AI Cost (30d)', 'ai_cost_30d')
                ->resolveUsing(function ($v, $resource) {
                    $cost = AiUsageLog::where('tenant_id', $resource->id)
                        ->where('created_at', '>=', now()->subDays(30))
                        ->sum('cost_usd');
                    return '$' . number_format((float) $cost, 4);
                })
                ->sortable(false)
                ->exceptOnForms(),

            Text::make('API Calls Today')->onlyOnDetail()->resolveUsing(function () {
                return (string) DB::table('api_request_logs')
                    ->where('tenant_id', $this->id)
                    ->whereDate('created_at', today())
                    ->count();
            }),

            Text::make('API Calls (30d)')->onlyOnDetail()->resolveUsing(function () {
                return (string) DB::table('api_request_logs')
                    ->where('tenant_id', $this->id)
                    ->where('created_at', '>=', now()->subDays(30))
                    ->count();
            }),

            HasMany::make('API Keys', 'apiKeys', TenantApiKey::class),

            // (Optional) show the raw JSON 'data' read-only for any extra metadata
            Code::make('Data (JSON)')
                ->json()
                ->resolveUsing(function ($value, $resource) {
                    $model = $resource->resource ?? $resource;
                    $raw = $model->getRawOriginal('data');
                    $arr = is_array($raw) ? $raw : (is_string($raw) ? (json_decode($raw, true) ?: []) : []);

                    return json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                })
                ->onlyOnDetail(),
        ];
    }

    public static function label()
    {
        return 'Tenants';
    }

    public static function singularLabel()
    {
        return 'Tenant';
    }

    public function actions(\Illuminate\Http\Request $request)
    {
        return [
            ProvisionTenant::make()->standalone(),
            new ResetSchoolData,
            new \App\Nova\Actions\GenerateDemoData,
            new \App\Nova\Actions\MarkTermsAccepted,
            new \App\Nova\Actions\ResendWelcomeEmail,
            new \App\Nova\Actions\ResetTermsAcceptance,
        ];
    }

    public static function authorizedToCreate($request): bool
    {
        return true;
    }
}
