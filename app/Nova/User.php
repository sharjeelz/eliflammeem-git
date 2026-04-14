<?php

namespace App\Nova;

use App\Models\User as UserModel;
use App\Nova\Tenant as TenantResource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Resource;

class User extends Resource
{
    public static $model = UserModel::class;

    public static $title = 'name';

    public static $search = ['id', 'name', 'email'];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Gravatar::make()->maxWidth(40),

            BelongsTo::make('Tenant', 'tenant', TenantResource::class)
                ->nullable()
                ->searchable(),

            Text::make('Name')->rules('required', 'max:150')->sortable(),

            Text::make('Email')->rules('required', 'email', 'max:191')->sortable(),

            Text::make('Phone', 'phone_number')
                ->nullable()
                ->hideFromIndex(),

            Text::make('Account ID', 'account_id')
                ->nullable()
                ->sortable()
                ->copyable()
                ->hideFromIndex(),

            // ── Security / 2FA ───────────────────────────────────────────
            Badge::make('2FA', function () {
                return $this->hasEnabledTwoFactorAuthentication() ? 'enabled' : 'disabled';
            })->map([
                'enabled'  => 'success',
                'disabled' => 'danger',
            ])->labels([
                'enabled'  => '2FA On',
                'disabled' => '2FA Off',
            ])->exceptOnForms(),

            DateTime::make('2FA Confirmed At', 'two_factor_confirmed_at')
                ->nullable()
                ->hideFromIndex()
                ->exceptOnForms(),

            // ── Login tracking ───────────────────────────────────────────
            Number::make('Login Count', 'login_count')
                ->sortable()
                ->exceptOnForms(),

            DateTime::make('Last Login', 'last_login')
                ->nullable()
                ->sortable()
                ->exceptOnForms(),

            Text::make('Last IP', 'last_login_ip')
                ->nullable()
                ->sortable()
                ->copyable()
                ->exceptOnForms(),

            Text::make('Last User Agent', 'last_login_user_agent')
                ->nullable()
                ->hideFromIndex()
                ->exceptOnForms(),

            DateTime::make('Created At', 'created_at')
                ->sortable()
                ->exceptOnForms()
                ->hideFromIndex(),
        ];
    }

    public function actions(Request $request): array
    {
        return [
            new \App\Nova\Actions\PermanentlyDeleteUser,
        ];
    }

    public function filters(Request $request)
    {
        return [new \App\Nova\Filters\TenantFilter];
    }

    public static function authorizedToCreate(Request $request)
    {
        return true;
    }
}
