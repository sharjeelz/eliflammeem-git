<?php

namespace App\Nova;

use App\Models\Plan as PlanModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class Plan extends Resource
{
    public static string $model = PlanModel::class;

    public static $title = 'label';

    public static $search = ['key', 'label'];

    /** Plans are global config - no per-row search needed */
    public static $perPageOptions = [10];

    public static function label(): string
    {
        return 'Plans';
    }

    public static function singularLabel(): string
    {
        return 'Plan';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            ID::make('Key', 'key')->sortable()->readonly(),

            Text::make('Label', 'label')
                ->rules('required', 'string', 'max:60')
                ->sortable(),

            Text::make('Tagline', 'tagline')
                ->nullable()
                ->rules('nullable', 'string', 'max:120')
                ->help('Short description shown on the public pricing page.'),

            Number::make('Monthly Price (USD $)', 'price_monthly')
                ->nullable()
                ->min(0)
                ->step(1)
                ->help('Enter whole dollars - e.g. 49 = $49/month, 0 = Free. Leave blank to show "Contact us".'),

            Text::make('Formatted Price')
                ->resolveUsing(fn ($v, $plan) => $plan->formattedPrice() ?? 'Contact us')
                ->exceptOnForms()
                ->sortable(false),

            Heading::make('Count Limits (leave blank = unlimited)'),

            Number::make('Max Branches', 'max_branches')
                ->nullable()
                ->min(1)
                ->step(1)
                ->help('Leave blank for unlimited'),

            Number::make('Max Staff Users', 'max_users')
                ->nullable()
                ->min(1)
                ->step(1)
                ->help('Leave blank for unlimited'),

            Number::make('Max Roster Contacts', 'max_contacts')
                ->nullable()
                ->min(1)
                ->step(1)
                ->help('Leave blank for unlimited'),

            Number::make('Max Issues / Month', 'max_issues_per_month')
                ->nullable()
                ->min(1)
                ->step(1)
                ->help('Max issues that can be submitted per calendar month. Leave blank for unlimited.'),

            Heading::make('AI Features'),

            Boolean::make('AI Issue Analysis', 'feat_ai_analysis'),
            Boolean::make('AI Trend Detection', 'feat_ai_trends'),

            Heading::make('Chatbot'),

            Boolean::make('Chatbot Enabled', 'feat_chatbot'),

            Number::make('Chatbot Daily Limit', 'feat_chatbot_daily')
                ->nullable()
                ->min(0)
                ->step(1)
                ->help('Max questions per day. Leave blank for unlimited. Set 0 to disable even when chatbot is on.'),

            Heading::make('Communication'),

            Boolean::make('Broadcasting (SMS / Email)', 'feat_broadcasting'),
            Boolean::make('WhatsApp Integration', 'feat_whatsapp'),
            Boolean::make('Custom SMTP', 'feat_custom_smtp'),

            Heading::make('Content & Reporting'),

            Boolean::make('Document Library', 'feat_document_library'),
            Boolean::make('Full Reports & Analytics', 'feat_reports_full'),
            Boolean::make('CSV Export', 'feat_csv_export'),

            Heading::make('Other'),

            Boolean::make('CSAT Surveys', 'feat_csat'),
            Boolean::make('Two-Factor Auth (Admins & Managers)', 'feat_two_factor'),
        ];
    }

    /**
     * Clear plan cache after every save so changes take effect immediately.
     */
    public static function afterUpdate(NovaRequest $request, $model): void
    {
        PlanModel::clearCache($model->key);
    }

    public static function afterCreate(NovaRequest $request, $model): void
    {
        PlanModel::clearCache($model->key);
    }

    /** Prevent deleting plans - they are referenced by tenants. */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
