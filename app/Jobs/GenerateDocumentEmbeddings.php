<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Models\Tenant;
use App\Services\MetadataExtractionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Embeddings;

class GenerateDocumentEmbeddings implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $timeout = 300; // 5 minutes for embedding generation

    public int $backoff = 120; // 2 minute backoff between retries

    public function __construct(
        public readonly int $documentId,
        public readonly string $tenantId,
    ) {}

    public function handle(): void
    {
        tenancy()->initialize(Tenant::find($this->tenantId));

        try {
            $document = Document::find($this->documentId);

            if (! $document) {
                Log::warning("Document not found for embedding generation", [
                    'document_id' => $this->documentId,
                    'tenant_id' => $this->tenantId,
                ]);
                return;
            }

            // Check if document is ready for embedding
            if (! $document->isReadyForEmbedding()) {
                Log::info("Document not ready for embedding generation", [
                    'document_id' => $this->documentId,
                    'text_extraction_status' => $document->text_extraction_status,
                    'include_in_chatbot' => $document->include_in_chatbot,
                ]);
                return;
            }

            // Update status to processing
            $document->update([
                'embedding_status' => 'processing',
                'embedding_attempts' => $document->embedding_attempts + 1,
            ]);

            // Delete existing chunks if regenerating
            $document->chunks()->delete();

            // Split content into chunks
            $chunks = $this->chunkText($document->searchable_content);

            if (empty($chunks)) {
                throw new \Exception("No text chunks created from document content");
            }

            Log::info("Created text chunks for embedding", [
                'document_id' => $this->documentId,
                'chunk_count' => count($chunks),
            ]);

            // Generate embeddings for all chunks at once (batch processing)
            $chunkTexts = array_map(fn($chunk) => $chunk['content'], $chunks);
            
            try {
                // Laravel AI SDK - pass model as second parameter to generate()
                $embeddingResult = Embeddings::for($chunkTexts)
                    ->generate(model: config('ai.embedding_model'));
                
                $embeddings = $embeddingResult->embeddings;
            } catch (\Exception $e) {
                Log::error("Failed to generate embeddings from AI service", [
                    'document_id' => $this->documentId,
                    'error' => $e->getMessage(),
                ]);
                throw new \Exception("AI embedding service failed: " . $e->getMessage());
            }

            // Initialize metadata extraction service
            $metadataService = app(MetadataExtractionService::class);

            // Store chunks with embeddings in database
            $chunkCount = 0;
            foreach ($chunks as $index => $chunkData) {
                // Extract metadata from chunk content
                $extractedMetadata = $metadataService->extractMetadata($chunkData['content']);

                $chunk = new DocumentChunk([
                    'tenant_id' => $this->tenantId,
                    'document_id' => $document->id,
                    'chunk_index' => $index,
                    'content' => $chunkData['content'],
                    'metadata' => array_merge([
                        'word_count' => $chunkData['word_count'],
                        'char_count' => strlen($chunkData['content']),
                        'start_position' => $chunkData['start_position'] ?? null,
                    ], $extractedMetadata),
                ]);

                // Set embedding from batch result
                if (isset($embeddings[$index])) {
                    $chunk->embedding = $embeddings[$index];
                }

                $chunk->save();
                $chunkCount++;
            }

            // Update document status
            $document->update([
                'embedding_status' => 'completed',
                'embedding_error' => null,
                'embeddings_generated_at' => now(),
                'chunk_count' => $chunkCount,
            ]);

            Log::info("Embedding generation completed", [
                'document_id' => $this->documentId,
                'tenant_id' => $this->tenantId,
                'chunk_count' => $chunkCount,
            ]);

        } catch (\Throwable $e) {
            Log::error("Embedding generation failed", [
                'document_id' => $this->documentId,
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempts' => $this->attempts(),
            ]);

            if ($document ?? null) {
                $document->update([
                    'embedding_status' => 'failed',
                    'embedding_error' => $e->getMessage(),
                ]);
            }

            $this->fail($e);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Split text into chunks of approximately 500 words.
     * Each chunk overlaps slightly with previous chunk for better context.
     */
    private function chunkText(string $text, int $targetWords = 500, int $overlapWords = 50): array
    {
        // Split text into sentences (basic sentence splitting)
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($sentences)) {
            return [];
        }

        $chunks = [];
        $currentChunk = '';
        $currentWordCount = 0;
        $startPosition = 0;

        foreach ($sentences as $sentence) {
            $sentenceWords = str_word_count($sentence);
            
            // If adding this sentence would exceed target, save current chunk
            if ($currentWordCount > 0 && ($currentWordCount + $sentenceWords) > $targetWords) {
                $chunks[] = [
                    'content' => trim($currentChunk),
                    'word_count' => $currentWordCount,
                    'start_position' => $startPosition,
                ];

                // Start new chunk with overlap (last few sentences from previous chunk)
                $overlapText = $this->getLastNWords($currentChunk, $overlapWords);
                $currentChunk = $overlapText . ' ' . $sentence;
                $currentWordCount = str_word_count($currentChunk);
                $startPosition += strlen($currentChunk) - strlen($sentence);
            } else {
                // Add sentence to current chunk
                $currentChunk .= ($currentChunk ? ' ' : '') . $sentence;
                $currentWordCount += $sentenceWords;
            }
        }

        // Add the last chunk if not empty
        if (trim($currentChunk) !== '') {
            $chunks[] = [
                'content' => trim($currentChunk),
                'word_count' => $currentWordCount,
                'start_position' => $startPosition,
            ];
        }

        return $chunks;
    }

    /**
     * Get the last N words from a text string.
     */
    private function getLastNWords(string $text, int $n): string
    {
        $words = preg_split('/\s+/', trim($text));
        $lastWords = array_slice($words, -$n);
        return implode(' ', $lastWords);
    }
}
