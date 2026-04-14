<?php

namespace App\Nova\Actions;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Stancl\Tenancy\Database\Models\Domain;

class MarkTermsAccepted extends Action
{
    use Queueable;

    public $name = 'Mark Terms Accepted';

    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $updated = 0;

        foreach ($models as $tenant) {
            /** @var Tenant $tenant */

            // Skip tenants that already accepted
            if ($tenant->terms_accepted_at) {
                continue;
            }

            // Resolve the tenant's admin domain to build the contract URL
            $domain = Domain::where('tenant_id', $tenant->id)->first();
            $protocol = app()->environment('local') ? 'http' : 'https';
            $contractUrl = $domain
                ? "{$protocol}://{$domain->domain}/admin/contract"
                : null;

            $tenant->update([
                'terms_accepted_at'      => now(),
                'terms_accepted_ip'      => '(superadmin)',
                'terms_accepted_version' => '1.0',
                'contract_file_url'      => $contractUrl,
                // If still in an incomplete onboarding status, advance to completed
                'registration_status'    => in_array($tenant->registration_status, ['pending', 'profile_complete', 'terms_accepted', null])
                    ? 'completed'
                    : $tenant->registration_status,
            ]);

            $updated++;
        }

        if ($updated === 0) {
            return Action::danger('All selected tenants have already accepted the Terms.');
        }

        return Action::message("Terms marked as accepted for {$updated} tenant(s). Contract URL recorded.");
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
