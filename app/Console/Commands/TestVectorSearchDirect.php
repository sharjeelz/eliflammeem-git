<?php

namespace App\Console\Commands;

use App\Models\DocumentChunk;
use Illuminate\Console\Command;

class TestVectorSearchDirect extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:vector-search-direct';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test vector similarity search using existing embeddings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔎 Testing vector similarity search...');
        $this->newLine();

        // Get the first chunk's embedding to use as a test query
        $referenceChunk = DocumentChunk::whereNotNull('embedding')->first();

        if (! $referenceChunk) {
            $this->error('No chunks with embeddings found.');

            return 1;
        }

        $this->info("Using chunk #{$referenceChunk->id} as reference:");
        $this->line(mb_substr($referenceChunk->content, 0, 150).'...');
        $this->newLine();

        // Perform similarity search
        $this->info('Finding similar chunks...');

        $results = DocumentChunk::selectRaw('
                id,
                document_id,
                chunk_index,
                content,
                (1 - (embedding <=> (SELECT embedding FROM document_chunks WHERE id = ?))) as similarity
            ', [$referenceChunk->id])
            ->whereNot('id', $referenceChunk->id)
            ->orderByRaw('embedding <=> (SELECT embedding FROM document_chunks WHERE id = ?)', [$referenceChunk->id])
            ->limit(3)
            ->get();

        if ($results->isEmpty()) {
            $this->warn('No other chunks found.');

            return 0;
        }

        $this->info('📄 Most Similar Chunks:');
        $this->newLine();

        foreach ($results as $index => $result) {
            $this->line('─────────────────────────────────────────────────');
            $this->line('<fg=cyan>Result #'.($index + 1).'</>');
            $this->line('<fg=yellow>Chunk ID:</> '.$result->id);
            $this->line('<fg=yellow>Document ID:</> '.$result->document_id);
            $this->line('<fg=yellow>Chunk Index:</> '.$result->chunk_index);
            $this->line('<fg=yellow>Similarity:</> '.number_format($result->similarity * 100, 2).'%');
            $this->newLine();
            $this->line('<fg=green>Content Preview:</>');
            $this->line(mb_substr($result->content, 0, 200).'...');
            $this->newLine();
        }

        $this->line('─────────────────────────────────────────────────');
        $this->info('✓ Vector similarity search is working!');

        return 0;
    }
}
