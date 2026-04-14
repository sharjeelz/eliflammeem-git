<?php

namespace App\Nova\Actions;

use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class RevokeApiKey extends Action
{
    public $name = 'Revoke Key';

    public $confirmText = 'This will immediately invalidate the API key. This cannot be undone. Continue?';

    public $confirmButtonText = 'Revoke';

    public $destructive = true;

    public function handle(ActionFields $fields, Collection $models): mixed
    {
        $models->each(fn ($key) => $key->update(['revoked_at' => now()]));

        return Action::message('Key(s) revoked successfully.');
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
