<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\VectorSearchService;
use Illuminate\Console\Command;

class DebugVectorSearch extends Command
{
    protected $signature = 'debug:vector-search {tenant_id} {query}';

    protected $description = 'Debug vector search to see similarity scores';

    public function handle(VectorSearchService $vectorSearch)
    {
        $tenantId = $this->argument('tenant_id');
        $query = $this->argument('query');

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            $this->error("Tenant not found");
            return 1;
        }

        tenancy()->initialize($tenant);

        $this->info("Searching for: {$query}");
        $this->newLine();

        // Search with very low threshold to see all results
        $chunks = $vectorSearch->search($query, limit: 10, minSimilarity: 0.0);

        if ($chunks->isEmpty()) {
            $this->error("No chunks found at all!");
            return 1;
        }

        $this->info("Found {$chunks->count()} chunks:");
        $this->newLine();

        foreach ($chunks as $chunk) {
            $this->line("Chunk ID: {$chunk->id}");
            $this->line("Similarity: " . round($chunk->similarity ?? 0, 4));
            $this->line("Content: " . substr($chunk->content, 0, 150) . "...");
            $this->line("---");
        }

        return 0;
    }
}
