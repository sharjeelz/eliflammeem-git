<?php

namespace App\Console\Commands;

use App\Models\Issue;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\ResolvedIssuesNudgeNotification;
use Illuminate\Console\Command;

class NudgeResolvedIssuesCommand extends Command
{
    protected $signature   = 'issues:nudge-resolved';
    protected $description = 'Notify admins about resolved issues that have had no parent response for 7+ days';

    public function handle(): void
    {
        Tenant::all('id')->each(function (Tenant $tenant) {
            tenancy()->initialize($tenant);

            try {
                $count = Issue::where('tenant_id', $tenant->id)
                    ->where('status', 'resolved')
                    ->whereNotNull('reopen_token') // token still present = parent never clicked
                    ->where('status_entered_at', '<=', now()->subDays(7))
                    ->count();

                if ($count === 0) {
                    return;
                }

                User::role('admin')->where('tenant_id', $tenant->id)->each(
                    fn ($admin) => $admin->notify(new ResolvedIssuesNudgeNotification($count, $tenant->id))
                );

                $this->info("Tenant {$tenant->id}: notified admins about {$count} stale resolved issue(s).");
            } finally {
                tenancy()->end();
            }
        });
    }
}
