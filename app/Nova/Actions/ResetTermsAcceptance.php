<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ResetTermsAcceptance extends Action
{
    use Queueable;

    public $name        = 'Reset Terms Acceptance';
    public $destructive = true;

    public function handle(ActionFields $fields, Collection $models): mixed
    {
        foreach ($models as $tenant) {
            $tenant->update([
                'terms_accepted_at'      => null,
                'terms_accepted_ip'      => null,
                'terms_accepted_version' => null,
                'contract_file_url'      => null,
                // Send them back to wizard step 2 (T&C) on next login
                'registration_status'    => 'profile_complete',
            ]);
        }

        return Action::message(
            count($models) === 1
                ? "Terms acceptance cleared. The admin will be prompted to re-accept on next login."
                : count($models) . " tenants reset. Their admins will be prompted to re-accept on next login."
        );
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
