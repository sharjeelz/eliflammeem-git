<?php

namespace App\Services;

use App\Models\DocumentChunk;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;

class VectorSearchService
{
    /**
     * Generate embedding for a query string.
     *
     * @param string $query
     * @return array Array of 1536 floats
     * @throws \Exception
     */
    public function generateQueryEmbedding(string $query): array
    {
        try {
            $result = Embeddings::for([$query])->generate(model: 'text-embedding-3-small');
            
            if (empty($result->embeddings)) {
                throw new \Exception('No embedding returned from OpenAI API');
            }

            return $result->embeddings[0];
        } catch (\Exception $e) {
            Log::error('Failed to generate query embedding', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Search for similar document chunks using vector similarity.
     *
     * @param array $embedding Query embedding (1536 floats)
     * @param int $limit Number of results to return
     * @param float $minSimilarity Minimum similarity threshold (0-1)
     * @param array $metadataFilters Optional metadata filters (grade, fee_range, topics, etc.)
     * @return Collection Collection of DocumentChunk models with similarity scores
     */
    public function searchSimilarChunks(
        array $embedding, 
        int $limit = 5, 
        float $minSimilarity = 0.7,
        array $metadataFilters = []
    ): Collection
    {
        if (!tenant()) {
            Log::warning('VectorSearchService called without tenant context');
            return collect([]);
        }

        try {
            // Convert embedding array to PostgreSQL vector format.
            // We interpolate the literal directly (safe: it is an array of floats from OpenAI)
            // to avoid binding the same large value three times as separate PDO parameters,
            // which causes a syntax error in some PostgreSQL driver versions.
            // PostgreSQL pgvector requires the '[...]' bracket notation.
            $embeddingLiteral = "'[" . implode(',', array_map('floatval', $embedding)) . "]'::vector";

            // Build base query with vector similarity
            $query = DocumentChunk::selectRaw(
                    "document_chunks.*, (1 - (embedding <=> {$embeddingLiteral})) as similarity"
                )
                ->whereRaw("(1 - (embedding <=> {$embeddingLiteral})) >= ?", [$minSimilarity]);

            // Apply metadata filters if provided
            $query = $this->applyMetadataFilters($query, $metadataFilters);

            // Execute query
            $chunks = $query
                ->orderByRaw("embedding <=> {$embeddingLiteral}")
                ->limit($limit)
                ->get();

            Log::info('Vector search completed', [
                'tenant_id' => tenant()->id,
                'results_count' => $chunks->count(),
                'min_similarity' => $minSimilarity,
                'metadata_filters' => $metadataFilters,
            ]);

            return $chunks;
        } catch (\Exception $e) {
            Log::error('Vector search failed', [
                'tenant_id' => tenant()->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Search for document chunks by query string.
     *
     * @param string $query User query
     * @param int $limit Number of results
     * @param float $minSimilarity Minimum similarity threshold
     * @param array $metadataFilters Optional metadata filters
     * @return Collection Collection of DocumentChunk models with similarity scores
     */
    public function search(
        string $query, 
        int $limit = 5, 
        float $minSimilarity = 0.7,
        array $metadataFilters = []
    ): Collection
    {
        $embedding = $this->generateQueryEmbedding($query);
        return $this->searchSimilarChunks($embedding, $limit, $minSimilarity, $metadataFilters);
    }

    /**
     * Hybrid search combining vector similarity and keyword matching.
     * Uses Reciprocal Rank Fusion (RRF) to merge results.
     *
     * @param string $query User query
     * @param int $limit Number of results to return
     * @param float $minSimilarity Minimum similarity threshold for vector search
     * @param array $metadataFilters Optional metadata filters
     * @return Collection Collection of DocumentChunk models with combined scores
     */
    public function hybridSearch(
        string $query,
        int $limit = 5,
        float $minSimilarity = 0.35,
        array $metadataFilters = []
    ): Collection
    {
        try {
            // 1. Vector similarity search
            $embedding = $this->generateQueryEmbedding($query);
            $vectorResults = $this->searchSimilarChunks(
                $embedding, 
                $limit * 2, // Get more results for merging
                $minSimilarity,
                $metadataFilters
            );

            // 2. Keyword search using PostgreSQL full-text search
            $keywordResults = $this->keywordSearch($query, $limit * 2, $metadataFilters);

            // 3. Merge using Reciprocal Rank Fusion (RRF)
            $mergedResults = $this->reciprocalRankFusion($vectorResults, $keywordResults, $limit);

            Log::info('Hybrid search completed', [
                'tenant_id' => tenant()->id,
                'query' => $query,
                'vector_results' => $vectorResults->count(),
                'keyword_results' => $keywordResults->count(),
                'merged_results' => $mergedResults->count(),
            ]);

            return $mergedResults;
        } catch (\Exception $e) {
            Log::error('Hybrid search failed', [
                'tenant_id' => tenant()->id,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Keyword search using PostgreSQL full-text search.
     *
     * @param string $query Search query
     * @param int $limit Number of results
     * @param array $metadataFilters Optional metadata filters
     * @return Collection Collection of DocumentChunk models with relevance scores
     */
    protected function keywordSearch(string $query, int $limit = 10, array $metadataFilters = []): Collection
    {
        if (!tenant()) {
            return collect([]);
        }

        try {
            // Build base query with full-text search
            $queryBuilder = DocumentChunk::selectRaw('
                    document_chunks.*,
                    ts_rank(content_tsv, plainto_tsquery(\'english\', ?)) as relevance
                ', [$query])
                ->whereRaw('content_tsv @@ plainto_tsquery(\'english\', ?)', [$query]);

            // Apply metadata filters
            $queryBuilder = $this->applyMetadataFilters($queryBuilder, $metadataFilters);

            // Execute query
            $chunks = $queryBuilder
                ->orderByDesc('relevance')
                ->limit($limit)
                ->get();

            return $chunks;
        } catch (\Exception $e) {
            Log::error('Keyword search failed', [
                'tenant_id' => tenant()->id,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            return collect([]);
        }
    }

    /**
     * Apply metadata filters to query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters Metadata filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyMetadataFilters($query, array $filters)
    {
        // Filter by grade (e.g., grade = 3)
        if (isset($filters['grade'])) {
            $grade = (int) $filters['grade'];
            $query->whereRaw("
                metadata::jsonb->'grade_ranges' IS NOT NULL AND
                EXISTS (
                    SELECT 1 FROM jsonb_array_elements(metadata::jsonb->'grade_ranges') AS gr
                    WHERE ? = ANY(
                        SELECT jsonb_array_elements_text(gr->'includes')::int
                    )
                )
            ", [$grade]);
        }

        // Filter by age range (e.g., age = 8)
        if (isset($filters['age'])) {
            $age = (int) $filters['age'];
            $query->whereRaw("
                metadata::jsonb->'age_ranges' IS NOT NULL AND
                EXISTS (
                    SELECT 1 FROM jsonb_array_elements(metadata::jsonb->'age_ranges') AS ar
                    WHERE ? BETWEEN (ar->>'min')::int AND (ar->>'max')::int
                )
            ", [$age]);
        }

        // Filter by topics (e.g., topics = ['fees', 'payment_schedule'])
        if (isset($filters['topics']) && is_array($filters['topics'])) {
            foreach ($filters['topics'] as $topic) {
                $query->whereRaw("metadata::jsonb->'topics' @> ?::jsonb", [json_encode([$topic])]);
            }
        }

        // Filter by minimum fee amount (e.g., min_fee = 100)
        if (isset($filters['min_fee'])) {
            $minFee = (float) $filters['min_fee'];
            $query->whereRaw("
                metadata::jsonb->'fee_amounts' IS NOT NULL AND
                EXISTS (
                    SELECT 1 FROM jsonb_array_elements(metadata::jsonb->'fee_amounts') AS fa
                    WHERE (fa->>'amount')::float >= ?
                )
            ", [$minFee]);
        }

        // Filter by maximum fee amount (e.g., max_fee = 1000)
        if (isset($filters['max_fee'])) {
            $maxFee = (float) $filters['max_fee'];
            $query->whereRaw("
                metadata::jsonb->'fee_amounts' IS NOT NULL AND
                EXISTS (
                    SELECT 1 FROM jsonb_array_elements(metadata::jsonb->'fee_amounts') AS fa
                    WHERE (fa->>'amount')::float <= ?
                )
            ", [$maxFee]);
        }

        return $query;
    }

    /**
     * Merge search results using Reciprocal Rank Fusion (RRF).
     * RRF formula: score(d) = Σ 1 / (k + rank(d))
     *
     * @param Collection $vectorResults Results from vector search
     * @param Collection $keywordResults Results from keyword search
     * @param int $limit Number of final results
     * @param int $k RRF constant (default: 60)
     * @return Collection Merged and re-ranked results
     */
    protected function reciprocalRankFusion(
        Collection $vectorResults,
        Collection $keywordResults,
        int $limit = 5,
        int $k = 60
    ): Collection
    {
        $scores = [];

        // Calculate RRF scores for vector results
        foreach ($vectorResults as $rank => $chunk) {
            $chunkId = $chunk->id;
            $scores[$chunkId] = ($scores[$chunkId] ?? 0) + (1 / ($k + $rank + 1));
            
            // Store the chunk object for later retrieval
            if (!isset($scores[$chunkId . '_chunk'])) {
                $scores[$chunkId . '_chunk'] = $chunk;
            }
        }

        // Calculate RRF scores for keyword results
        foreach ($keywordResults as $rank => $chunk) {
            $chunkId = $chunk->id;
            $scores[$chunkId] = ($scores[$chunkId] ?? 0) + (1 / ($k + $rank + 1));
            
            // Store the chunk object if not already stored
            if (!isset($scores[$chunkId . '_chunk'])) {
                $scores[$chunkId . '_chunk'] = $chunk;
            }
        }

        // Sort by RRF score (descending) and take top N
        $rankedChunks = collect();
        $sortedScores = collect($scores)
            ->filter(fn($value, $key) => !str_ends_with($key, '_chunk'))
            ->sortDesc()
            ->take($limit);

        foreach ($sortedScores as $chunkId => $score) {
            $chunk = $scores[$chunkId . '_chunk'];
            $chunk->rrf_score = round($score, 4);
            $rankedChunks->push($chunk);
        }

        return $rankedChunks;
    }

    /**
     * Get context from chunks for LLM prompts.
     *
     * @param Collection $chunks Collection of DocumentChunk models
     * @return array Array of context items with document info
     */
    public function getContextFromChunks(Collection $chunks): array
    {
        return $chunks->map(function ($chunk) {
            return [
                'chunk_id' => $chunk->id,
                'content' => $chunk->content,
                'document_id' => $chunk->document_id,
                'document_title' => $chunk->document->title ?? 'Unknown',
                'document_category' => $chunk->document->category->name ?? 'Unknown',
                'similarity' => round($chunk->similarity ?? 0, 3),
            ];
        })->toArray();
    }

    /**
     * Re-rank search results (optional enhancement for hybrid search).
     * Can be implemented later with additional scoring logic.
     *
     * @param Collection $chunks
     * @param string $query
     * @return Collection
     */
    public function rerankResults(Collection $chunks, string $query): Collection
    {
        // TODO: Implement re-ranking logic
        // Could use:
        // - BM25 keyword matching
        // - Recency boosting
        // - Document category prioritization
        // - Cross-encoder models
        
        return $chunks;
    }
}
