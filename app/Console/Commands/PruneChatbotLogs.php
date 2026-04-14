<?php

namespace App\Console\Commands;

use App\Models\ChatbotLog;
use App\Models\Tenant;
use Illuminate\Console\Command;

class PruneChatbotLogs extends Command
{
    protected $signature = 'chatbot:prune-logs
                            {--tenant= : Process only a specific tenant ID}
                            {--delete-after=90 : Delete logs older than this many days (default: 90)}
                            {--anonymize-after=30 : Anonymize IP addresses older than this many days (default: 30)}
                            {--dry-run : Preview what would be deleted/anonymized without making changes}';

    protected $description = 'Delete old chatbot logs and anonymize IP addresses for GDPR compliance';

    public function handle(): int
    {
        $dryRun          = $this->option('dry-run');
        $deleteAfterDays = (int) $this->option('delete-after');
        $anonAfterDays   = (int) $this->option('anonymize-after');
        $specificTenant  = $this->option('tenant');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $tenants = $specificTenant
            ? Tenant::where('id', $specificTenant)->get()
            : Tenant::all();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return 1;
        }

        $totalDeleted    = 0;
        $totalAnonymized = 0;

        foreach ($tenants as $tenant) {
            tenancy()->initialize($tenant);

            try {
                [$deleted, $anonymized] = $this->processTenant(
                    $tenant,
                    $deleteAfterDays,
                    $anonAfterDays,
                    $dryRun
                );
                $totalDeleted    += $deleted;
                $totalAnonymized += $anonymized;
            } catch (\Throwable $e) {
                $this->error("  {$tenant->name}: {$e->getMessage()}");
            } finally {
                tenancy()->end();
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Tenants processed : {$tenants->count()}");
        $this->info("  Logs deleted      : {$totalDeleted}");
        $this->info("  IPs anonymized    : {$totalAnonymized}");

        if ($dryRun) {
            $this->warn('DRY RUN — no changes were made.');
        }

        return 0;
    }

    private function processTenant(Tenant $tenant, int $deleteDays, int $anonDays, bool $dryRun): array
    {
        $deleteThreshold = now()->subDays($deleteDays);
        $anonThreshold   = now()->subDays($anonDays);

        // Count rows to delete
        $toDelete = ChatbotLog::where('tenant_id', $tenant->id)
            ->where('created_at', '<', $deleteThreshold)
            ->count();

        // Count IPs to anonymize (older than $anonDays, not yet anonymized, not already being deleted)
        $toAnonymize = ChatbotLog::where('tenant_id', $tenant->id)
            ->where('created_at', '<', $anonThreshold)
            ->where('created_at', '>=', $deleteThreshold)
            ->whereNotNull('ip_address')
            ->count();

        $this->line(sprintf(
            '  %s: %d to delete (>%dd), %d IPs to anonymize (>%dd)',
            $tenant->name,
            $toDelete,
            $deleteDays,
            $toAnonymize,
            $anonDays
        ));

        if ($dryRun) {
            return [$toDelete, $toAnonymize];
        }

        // Delete old logs
        $deleted = ChatbotLog::where('tenant_id', $tenant->id)
            ->where('created_at', '<', $deleteThreshold)
            ->delete();

        // Anonymize IPs in the middle window (older than $anonDays, younger than $deleteDays)
        $anonymized = ChatbotLog::where('tenant_id', $tenant->id)
            ->where('created_at', '<', $anonThreshold)
            ->whereNotNull('ip_address')
            ->update(['ip_address' => null]);

        return [$deleted, $anonymized];
    }
}
