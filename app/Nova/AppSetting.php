<?php

namespace App\Nova;

use App\Models\AppSetting as AppSettingModel;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Trix;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class AppSetting extends Resource
{
    public static $model = AppSettingModel::class;

    public static $title = 'label';

    public static $search = ['key', 'label', 'group'];

    public static function uriKey(): string
    {
        return 'app-settings';
    }

    public static function label(): string
    {
        return 'App Settings';
    }

    public static function singularLabel(): string
    {
        return 'App Setting';
    }

    public function fields(Request $request): array
    {
        return [
            ID::make('Key', 'key')->sortable(),

            Text::make('Label')
                ->rules('required', 'max:150')
                ->sortable(),

            Select::make('Group')
                ->options([
                    'legal'   => 'Legal',
                    'contact' => 'Contact',
                    'general' => 'General',
                ])
                ->displayUsingLabels()
                ->sortable()
                ->rules('required'),

            Select::make('Type')
                ->options([
                    'text'     => 'Plain Text',
                    'html'     => 'Rich HTML',
                    'textarea' => 'Textarea',
                ])
                ->displayUsingLabels()
                ->rules('required'),

            Trix::make('Value')
                ->nullable()
                ->withFiles(false)
                ->alwaysShow(),
        ];
    }

    public function afterSave(NovaRequest $request, $model): void
    {
        AppSettingModel::set($model->key, (string) $model->value);
    }

    public static function authorizedToCreate(Request $request): bool
    {
        return true;
    }

    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
