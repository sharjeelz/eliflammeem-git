<?php

namespace App\Nova\Actions;

use App\Mail\LeadApprovedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class ApproveLead extends Action
{
    use Queueable;

    public function name(): string
    {
        return 'Approve Lead';
    }

    public function handle(ActionFields $fields, Collection $models): \Laravel\Nova\Actions\ActionResponse
    {
        foreach ($models as $lead) {
            $lead->update([
                'status'      => 'approved',
                'approved_at' => now(),
            ]);

            Mail::to($lead->email)->queue(new LeadApprovedMail($lead));
        }

        return Action::message('Lead(s) approved and notified.');
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
