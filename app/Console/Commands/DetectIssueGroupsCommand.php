<?php

namespace App\Console\Commands;

use App\Jobs\DetectIssueGroups;
use App\Models\Tenant;
use Illuminate\Console\Command;

class DetectIssueGroupsCommand extends Command
{
    protected $signature = 'groups:detect {--sync : Run synchronously instead of queuing}';

    protected $description = 'Detect AI issue groups across all tenants';

    public function handle(): void
    {
        $tenants = Tenant::all('id');

        foreach ($tenants as $tenant) {
            if ($this->option('sync')) {
                $this->info("Running synchronously for tenant {$tenant->id}...");
                (new DetectIssueGroups($tenant->id))->handle();
                $this->info("Done.");
            } else {
                DetectIssueGroups::dispatch($tenant->id);
                $this->info("Queued for tenant {$tenant->id}.");
            }
        }
    }
}
