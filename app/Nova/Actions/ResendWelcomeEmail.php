<?php

namespace App\Nova\Actions;

use App\Models\School;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Stancl\Tenancy\Database\Models\Domain;

class ResendWelcomeEmail extends Action
{
    use Queueable;

    public $name = 'Resend Welcome Email';

    public function handle(ActionFields $fields, Collection $models): mixed
    {
        foreach ($models as $tenant) {
            tenancy()->initialize($tenant);

            try {
                $admin = User::where('tenant_id', $tenant->id)
                    ->role('admin')
                    ->orderBy('created_at')
                    ->first();

                if (! $admin) {
                    tenancy()->end();
                    return Action::danger("No admin user found for tenant: {$tenant->name}");
                }

                $school = School::where('tenant_id', $tenant->id)->first();
                $domain = Domain::where('tenant_id', $tenant->id)->first();

                if (! $domain) {
                    tenancy()->end();
                    return Action::danger("No domain found for tenant: {$tenant->name}");
                }

                $scheme     = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
                $port       = parse_url(config('app.url'), PHP_URL_PORT);
                $portSuffix = $port ? ":{$port}" : '';
                $host       = $domain->domain;

                $resetToken = Password::broker('users')->createToken($admin);
                $resetUrl   = "{$scheme}://{$host}{$portSuffix}/admin/reset-password/{$resetToken}?email=" . urlencode($admin->email);

                Mail::to($admin->email)->queue(new \App\Mail\TenantProvisionedMail(
                    schoolName:    $school?->name ?? $tenant->name,
                    adminName:     $admin->name,
                    adminEmail:    $admin->email,
                    resetUrl:      $resetUrl,
                    portalUrl:     "{$scheme}://{$host}{$portSuffix}/",
                    adminLoginUrl: "{$scheme}://{$host}{$portSuffix}/admin/login",
                ));
            } finally {
                tenancy()->end();
            }
        }

        return Action::message('Welcome email resent with a fresh password-reset link.');
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
