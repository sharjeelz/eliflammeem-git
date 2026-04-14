<?php

namespace App\Console\Commands;

use App\Jobs\CheckEscalations;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CheckEscalationsCommand extends Command
{
    protected $signature = 'escalations:check';

    protected $description = 'Check all tenants for issues that have breached escalation rule thresholds';

    public function handle(): void
    {
        $tenants = Tenant::all('id');

        foreach ($tenants as $tenant) {
            CheckEscalations::dispatch($tenant->id);
        }

        $this->info("Dispatched escalation checks for {$tenants->count()} tenant(s).");
    }
}
