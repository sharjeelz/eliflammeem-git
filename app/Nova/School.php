<?php

namespace App\Nova;

use App\Models\School as SchoolModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Panel;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Panel as NovaPanel;
use Laravel\Nova\Resource;

class School extends Resource
{
    public static $model = SchoolModel::class;

    public static $title = 'name';

    public static $search = ['name', 'code', 'city'];

    public function fields(Request $request): array
    {
        return [

            // ── Identity ─────────────────────────────────────────────────────
            ID::make()->sortable(),

            BelongsTo::make('Tenant')->searchable()->sortable(),

            Text::make('Name')
                ->rules('required', 'max:150')
                ->sortable(),

            Text::make('Code')
                ->rules('required', 'max:60')
                ->creationRules('unique:schools,code,NULL,id,tenant_id,' . $this->tenant_id)
                ->updateRules('unique:schools,code,{{resourceId}},id,tenant_id,' . $this->tenant_id)
                ->sortable(),

            Text::make('City')
                ->nullable()
                ->sortable(),

            Select::make('Status')
                ->options(['active' => 'Active', 'inactive' => 'Inactive'])
                ->displayUsingLabels()
                ->sortable(),

            // ── Logo ──────────────────────────────────────────────────────────
            Image::make('Logo', 'logo')
                ->disk('logos')
                ->path(fn() => 'schools/' . ($this->id ?? 'new'))
                ->nullable()
                ->prunable()
                ->hideFromIndex(),

            // ── Contact & Portal (stored in settings JSON) ────────────────────
            new NovaPanel('Contact & Portal', $this->settingsFields()),
        ];
    }

    /**
     * Individual fields that read/write from the settings JSON column.
     * resolveUsing reads the value out of the JSON.
     * fillUsing merges the value back in without overwriting other keys.
     */
    private function settingsFields(): array
    {
        $settingField = function (string $label, string $key, bool $textarea = false) {
            $field = $textarea
                ? Textarea::make($label, $key)->rows(2)
                : Text::make($label, $key);

            return $field
                ->nullable()
                ->hideFromIndex()
                ->resolveUsing(fn($v, $resource) => $resource->setting($key))
                ->fillUsing(function ($request, $model, $attribute, $requestAttribute) use ($key) {
                    $settings = $model->settings ?? [];
                    $settings[$key] = $request->$requestAttribute ?: null;
                    $model->settings = $settings;
                });
        };

        return [
            $settingField('Address',                 'address'),
            $settingField('Contact Email',           'contact_email'),
            $settingField('Contact Phone',           'contact_phone'),
            $settingField('Website URL',             'website_url'),
            $settingField('Welcome Message',         'welcome_message',  true),
            $settingField('Thank-You Message',       'thankyou_message', true),
            $settingField('Primary Color (hex)',     'primary_color')
                ->help('6-digit hex, e.g. #4338ca. Controls buttons, links and gradients on the public portal.'),
        ];
    }

    public function filters(Request $request): array
    {
        return [new \App\Nova\Filters\TenantFilter];
    }
}
