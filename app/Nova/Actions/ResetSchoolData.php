<?php

namespace App\Nova\Actions;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Heading;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;

class ResetSchoolData extends Action
{
    use Queueable;

    public $destructive = true;

    public function name(): string
    {
        return 'Reset School Data';
    }

    public function fields(NovaRequest $request): array
    {
        return [
            Heading::make(
                '<p class="text-red-600 font-bold">DANGER — This permanently deletes all issues, contacts, access codes, CSAT responses, and notifications for this school.</p>'
                    .'<p class="mt-1 text-gray-600">Issue categories, branches, users, and school settings are kept.</p>'
            )->asHtml(),

            Text::make('Type RESET to confirm', 'confirmation')
                ->rules('required')
                ->help('Must be exactly: RESET'),

            Boolean::make('Also delete branch managers & staff', 'delete_users')
                ->withMeta(['value' => false])
                ->help('Removes branch manager and staff accounts. Admin accounts are preserved. Use this before running Generate Demo Data.'),

            Boolean::make('Reactivate school after reset', 'reactivate')
                ->withMeta(['value' => true])
                ->help('If the school is currently suspended, this will set it back to active.'),
        ];
    }

    public function handle(ActionFields $fields, Collection $models)
    {
        if (strtoupper(trim((string) $fields->confirmation)) !== 'RESET') {
            return Action::danger('You must type RESET (all caps) to confirm.');
        }

        foreach ($models as $tenant) {
            try {
                tenancy()->initialize($tenant);

                DB::transaction(function () use ($tenant, $fields) {
                    $tenantId = $tenant->id;

                    // 1. CSAT responses (FK → issues)
                    DB::table('csat_responses')->where('tenant_id', $tenantId)->delete();

                    // 2. Issue notes (FK → issues, has tenant_id)
                    DB::table('issue_notes')->where('tenant_id', $tenantId)->delete();

                    // 3. Issue AI analysis (FK → issues)
                    DB::table('issue_ai_analysis')->where('tenant_id', $tenantId)->delete();

                    // 4. Issue attachments (FK → issues)
                    DB::table('issue_attachments')->where('tenant_id', $tenantId)->delete();

                    // 5. Issue activities (FK → issues)
                    DB::table('issue_activities')->where('tenant_id', $tenantId)->delete();

                    // 6. Issue messages (FK → issues)
                    DB::table('issue_messages')->where('tenant_id', $tenantId)->delete();

                    // 7. Issues
                    DB::table('issues')->where('tenant_id', $tenantId)->delete();

                    // 8. Issue group items (FK → issue_groups)
                    DB::table('issue_group_items')->where('tenant_id', $tenantId)->delete();

                    // 9. Issue groups
                    DB::table('issue_groups')->where('tenant_id', $tenantId)->delete();

                    // 10. Access codes (FK → roster_contacts)
                    DB::table('access_codes')->where('tenant_id', $tenantId)->delete();

                    // 11. Roster contacts
                    DB::table('roster_contacts')->where('tenant_id', $tenantId)->delete();

                    // 12. In-app notifications for all tenant users
                    $userIds = DB::table('users')->where('tenant_id', $tenantId)->pluck('id');
                    if ($userIds->isNotEmpty()) {
                        DB::table('notifications')
                            ->where('notifiable_type', User::class)
                            ->whereIn('notifiable_id', $userIds)
                            ->delete();
                    }

                    // 13. Optionally delete branch_manager + staff users (keep admins)
                    if ($fields->delete_users && $userIds->isNotEmpty()) {
                        // Find the admin role ID for this tenant (Spatie team = tenant_id)
                        $adminRoleId = DB::table('roles')
                            ->where('name', 'admin')
                            ->where('team_id', $tenantId)
                            ->value('id');

                        // User IDs that hold the admin role — must be preserved
                        $adminUserIds = $adminRoleId
                            ? DB::table('model_has_roles')
                                ->where('role_id', $adminRoleId)
                                ->where('model_type', User::class)
                                ->pluck('model_id')
                            : collect();

                        $nonAdminIds = $userIds->diff($adminUserIds);

                        if ($nonAdminIds->isNotEmpty()) {
                            DB::table('issue_category_user')
                                ->where('tenant_id', $tenantId)
                                ->whereIn('user_id', $nonAdminIds)
                                ->delete();
                            DB::table('branch_user')
                                ->where('tenant_id', $tenantId)
                                ->whereIn('user_id', $nonAdminIds)
                                ->delete();
                            DB::table('users')
                                ->where('tenant_id', $tenantId)
                                ->whereIn('id', $nonAdminIds)
                                ->delete();
                        }
                    }

                    // 14. Optionally reactivate the school
                    if ($fields->reactivate) {
                        DB::table('schools')
                            ->where('tenant_id', $tenantId)
                            ->update(['status' => 'active']);
                    }
                });

                tenancy()->end();
            } catch (\Throwable $e) {
                tenancy()->end();
                report($e);

                return Action::danger('Reset failed: '.$e->getMessage());
            }
        }

        $reactivated = $fields->reactivate ? ' School reactivated.' : '';
        $usersDeleted = $fields->delete_users ? ' Branch managers & staff deleted (admins kept).' : ' Users kept.';

        return Action::message('School data reset complete. Categories and branches intact.'.$usersDeleted.$reactivated);
    }
}
