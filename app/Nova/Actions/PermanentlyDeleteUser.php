<?php

namespace App\Nova\Actions;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class PermanentlyDeleteUser extends Action
{
    use Queueable;

    public $destructive = true;

    public function name(): string
    {
        return 'Permanently Delete User';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make(
                '<p class="text-red-600 font-bold text-base">PERMANENT — this cannot be undone.</p>'
                .'<ul class="mt-2 text-sm text-gray-700 list-disc list-inside space-y-1">'
                .'<li>User account hard-deleted</li>'
                .'<li>Assigned issues <strong>unassigned</strong> (not deleted — they belong to parents)</li>'
                .'<li>Their messages kept but <strong>anonymised</strong> → "Deleted User"</li>'
                .'<li>Private notes and notifications deleted</li>'
                .'</ul>'
            )->asHtml(),

            Text::make('Type DELETE to confirm', 'confirmation')
                ->rules('required')
                ->help('Must be exactly: DELETE'),
        ];
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        if (strtoupper(trim((string) $fields->confirmation)) !== 'DELETE') {
            return Action::danger('You must type DELETE (all caps) to confirm.');
        }

        foreach ($models as $user) {
            DB::transaction(function () use ($user) {
                $userId = $user->id;

                // 1. Unassign all issues assigned to this user
                DB::table('issues')
                    ->where('assigned_user_id', $userId)
                    ->update(['assigned_user_id' => null]);

                // 2. Anonymise their messages — keep content for audit trail
                DB::table('issue_messages')
                    ->where('author_type', \App\Models\User::class)
                    ->where('author_id', $userId)
                    ->update([
                        'author_type' => null,
                        'author_id'   => null,
                        'meta'        => DB::raw(
                            "(COALESCE(meta, '{}')::jsonb || '{\"actor_name\":\"Deleted User\"}'::jsonb)::json"
                        ),
                    ]);

                // 3. Anonymise activity log entries — keep the trail, clear the actor
                DB::table('issue_activities')
                    ->where('actor_id', $userId)
                    ->update(['actor_id' => null]);

                // 4. Delete private notes (personal scratchpad, no value without owner)
                DB::table('issue_notes')
                    ->where('user_id', $userId)
                    ->delete();

                // 5. Delete in-app notifications
                DB::table('notifications')
                    ->where('notifiable_type', \App\Models\User::class)
                    ->where('notifiable_id', $userId)
                    ->delete();

                // 6. Hard-delete the user (forceDelete bypasses SoftDeletes)
                $user->forceDelete();
            });
        }

        return Action::message('User(s) permanently deleted. Issues unassigned, messages anonymised, audit trail intact.');
    }
}
