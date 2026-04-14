<?php

namespace App\Nova\Actions;

use App\Models\Branch;
use App\Models\School;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\Tenant\TenantIssueCategoriesSeeder as TenantTenantIssueCategoriesSeeder;
use Database\Seeders\TenantRolesSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Stancl\Tenancy\Database\Models\Domain;

class ProvisionTenant extends Action
{
    use Queueable;

    /** Show without selecting rows */
    public $standalone = true;

    public function name(): string
    {
        return 'Provision Tenant (Create School)';
    }

    /** UI fields shown in the action modal */
    public function fields(NovaRequest $request): array
    {
        $base = config('app.base_domain', 'lvh.me');

        return [
            Heading::make("Base domain: {$base}"),

            Heading::make('School / Tenant'),
            Text::make('School Name', 'school_name')
                ->rules('required', 'max:150')
                ->help("Displayed as the school's name"),

            Select::make('Plan', 'plan')
                ->options([
                    'starter'    => 'Starter',
                    'growth'     => 'Growth',
                    'pro'        => 'Pro',
                    'enterprise' => 'Enterprise',
                ])
                ->displayUsingLabels()
                ->default('starter')
                ->rules('required'),

            Select::make('Contract Type', 'contract_type')
                ->options(['monthly' => 'Monthly', 'yearly' => 'Yearly'])
                ->displayUsingLabels()
                ->default('yearly')
                ->rules('required'),

            Date::make('Subscription Starts', 'subscription_starts_at')
                ->default(now()->toDateString())
                ->rules('required', 'date')
                ->help('When does the subscription begin?'),

            Date::make('Subscription Ends', 'subscription_ends_at')
                ->default(now()->addYear()->toDateString())
                ->rules('required', 'date', 'after:subscription_starts_at')
                ->help('When does the subscription expire / renew?'),

            Heading::make('Domain'),
            Text::make('Subdomain', 'subdomain')
                ->rules('required', 'max:60', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->help("Will provision as {subdomain}.{$base}"),

            Heading::make('First Admin (tenant)'),
            Boolean::make('Create Admin', 'create_admin')
                ->withMeta(['value' => true]),
            Text::make('Admin Name', 'admin_name')
                ->hideFromIndex()
                ->help('Optional; defaults to "School Admin"'),
            Text::make('Admin Email', 'admin_email')
                ->hideFromIndex()
                ->help('Required if Create Admin is checked'),
        ];
    }

    /** Action logic */
    public function handle(ActionFields $fields, Collection $models)
    {
        $base = config('app.base_domain', 'lvh.me');
        $schoolName = trim((string) $fields->school_name);
        $subdomainIn = trim((string) $fields->subdomain);
        $createAdmin = (bool) $fields->create_admin;
        $adminName = trim((string) $fields->admin_name) ?: 'School Admin';
        $adminEmail = trim((string) $fields->admin_email);
        // Set a random internal password — the admin will set their own via the reset link
        $adminPass = Str::password(20);
        if ($schoolName === '') {
            return Action::danger('School name is required.');
        }

        // sanitize subdomain (lowercase, digits, hyphens)
        $subdomain = Str::of($subdomainIn)
            ->lower()
            ->replace(' ', '-')
            ->replaceMatches('/[^a-z0-9-]/', '')
            ->trim('-')
            ->value();

        if ($subdomain === '' || ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $subdomain)) {
            return Action::danger('Invalid subdomain. Use lowercase letters, numbers and dashes only.');
        }

        if ($createAdmin) {
            if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                return Action::danger('Valid admin email is required when "Create Admin" is checked.');
            }
        }

        $host = "{$subdomain}.{$base}";
        if (Domain::where('domain', $host)->exists()) {
            return Action::danger("Domain already exists: {$host}");
        }

        try {
            DB::beginTransaction();

            // 1) Create tenant (UUID auto by Stancl)
            /** @var Tenant $tenant */
            $tenant = Tenant::create([
                'name'                   => $schoolName,
                'plan'                   => trim((string) $fields->plan) ?: 'starter',
                'contract_type'          => $fields->contract_type,
                'subscription_starts_at' => $fields->subscription_starts_at,
                'subscription_ends_at'   => $fields->subscription_ends_at,
                'registration_status'    => 'pending',
            ]);

            if (Domain::where('domain', $host)->exists()) {

                DB::rollBack();

                return Action::danger("Domain already exists: {$host}");
            }
            // 2) Map domain to tenant
            Domain::create([
                'domain' => $host,
                'tenant_id' => $tenant->id, // UUID
            ]);

            $school_code = $schoolName ? Str::of($schoolName)->lower()->replace(' ', '_')->replaceMatches('/[^a-z0-9_]/', '')->value() : 'school_' . Str::random(5);
            // 3) Create school (row-level tenancy)
            $school = School::create([
                'tenant_id' => $tenant->id,
                'name' => $schoolName,
                'code' => $school_code,
                'status' => 'active',
            ]);

            // 4) Default branch
            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'school_id' => $school->id,
                'name' => 'Main',
                'code' => 'main',
                'status' => 'active',
            ]);

            // 5) Optional first admin user in this tenant

            tenancy()->initialize($tenant);

            app()->call(TenantRolesSeeder::class);

            app()->call(TenantTenantIssueCategoriesSeeder::class);
            if ($createAdmin) {
                $admin = User::create([
                    'tenant_id' => $tenant->id,
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => $adminPass,
                ]);

                $admin->assignRole('admin');

                // Generate a password-reset token so the admin sets their own password
                // (never send plaintext credentials via email)
                $resetToken  = Password::broker('users')->createToken($admin);
                $scheme      = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
                $port        = parse_url(config('app.url'), PHP_URL_PORT);
                $portSuffix  = $port ? ":{$port}" : '';
                $resetUrl    = "{$scheme}://{$host}{$portSuffix}/admin/reset-password/{$resetToken}?email=" . urlencode($adminEmail);

                // Notify the new admin their portal is ready
                Mail::to($adminEmail)->queue(new \App\Mail\TenantProvisionedMail(
                    schoolName:    $schoolName,
                    adminName:     $adminName,
                    adminEmail:    $adminEmail,
                    resetUrl:      $resetUrl,
                    portalUrl:     "{$scheme}://{$host}{$portSuffix}/",
                    adminLoginUrl: "{$scheme}://{$host}{$portSuffix}/admin/login",
                ));
            }
            tenancy()->end();

            DB::commit();


            $publicUrl = "https://{$host}/";
            $adminUrl = "https://{$host}/admin/login";

            return Action::message("Provisioned!\nTenant: {$tenant->id}\nDomain: {$host}\nPublic: {$publicUrl}\nAdmin Login: {$adminUrl}");
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return Action::danger('Provision failed: ' . $e->getMessage());
        }
    }
}
