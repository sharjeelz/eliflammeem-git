<?php

namespace App\Console\Commands;

use App\Models\DocumentChunk;
use Illuminate\Console\Command;
use Laravel\Ai\Embeddings;

class TestVectorSearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:vector-search {query}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test vector similarity search with a query';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = $this->argument('query');

        $this->info("🔍 Searching for: {$query}");
        $this->newLine();

        // Generate embedding for the query
        $this->info('📊 Generating query embedding...');
        $embeddingResult = Embeddings::for([$query])->generate(
            model: config('ai.embedding_model')
        );

        $queryEmbedding = $embeddingResult->embeddings[0];
        $this->info('✓ Query embedding generated ('.count($queryEmbedding).' dimensions)');
        $this->newLine();

        // Convert embedding array to pgvector format string
        $embeddingString = '['.implode(',', $queryEmbedding).']';

        // Perform vector similarity search using cosine distance
        $this->info('🔎 Searching document chunks...');
        $results = DocumentChunk::selectRaw('
                id,
                document_id,
                chunk_index,
                content,
                (1 - (embedding <=> ?::vector)) as similarity
            ', [$embeddingString])
            ->orderByRaw('embedding <=> ?::vector', [$embeddingString])
            ->limit(5)
            ->get();

        if ($results->isEmpty()) {
            $this->warn('No results found.');

            return;
        }

        $this->info('📄 Top 5 Results:');
        $this->newLine();

        foreach ($results as $index => $result) {
            $this->line('─────────────────────────────────────────────────');
            $this->line('<fg=cyan>Result #'.($index + 1).'</>');
            $this->line('<fg=yellow>Document ID:</> '.$result->document_id);
            $this->line('<fg=yellow>Chunk Index:</> '.$result->chunk_index);
            $this->line('<fg=yellow>Similarity:</> '.number_format($result->similarity * 100, 2).'%');
            $this->newLine();
            $this->line('<fg=green>Content Preview:</>');
            $this->line(mb_substr($result->content, 0, 200).'...');
            $this->newLine();
        }

        $this->line('─────────────────────────────────────────────────');
        $this->info('✓ Search completed successfully!');
    }
}
