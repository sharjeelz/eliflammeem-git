<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessDocumentText implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public int $backoff = 60;

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
                Log::warning("Document not found for text extraction", [
                    'document_id' => $this->documentId,
                    'tenant_id' => $this->tenantId,
                ]);
                return;
            }

            // Update status to processing
            $document->update([
                'text_extraction_status' => 'processing',
                'text_extraction_attempts' => $document->text_extraction_attempts + 1,
            ]);

            // Check if document supports text extraction
            if (! $document->supportsTextExtraction()) {
                $document->update([
                    'text_extraction_status' => 'failed',
                    'text_extraction_error' => 'File type does not support text extraction',
                ]);
                return;
            }

            // Get file content
            $filePath = $document->path;
            if (! Storage::disk($document->disk)->exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }

            $fullPath = Storage::disk($document->disk)->path($filePath);
            $extension = strtolower($document->file_extension);

            // Extract text based on file type
            $extractedText = match ($extension) {
                'pdf' => $this->extractTextFromPdf($fullPath),
                'docx' => $this->extractTextFromDocx($fullPath),
                'txt' => $this->extractTextFromTxt($fullPath),
                default => throw new \Exception("Unsupported file type: {$extension}"),
            };

            // Clean and normalize text
            $cleanedText = $this->cleanExtractedText($extractedText);

            // Update document with extracted text
            $document->update([
                'searchable_content' => $cleanedText,
                'text_extraction_status' => 'completed',
                'text_extraction_error' => null,
                'text_extracted_at' => now(),
            ]);

            Log::info("Text extraction completed", [
                'document_id' => $this->documentId,
                'tenant_id' => $this->tenantId,
                'text_length' => strlen($cleanedText),
            ]);

            // Dispatch embedding generation job if document is ready
            // (text extracted + include_in_chatbot = true)
            if ($document->isReadyForEmbedding()) {
                Log::info("Dispatching embedding generation job", [
                    'document_id' => $this->documentId,
                    'tenant_id' => $this->tenantId,
                ]);

                dispatch(new GenerateDocumentEmbeddings($document->id, $this->tenantId));
            }

        } catch (\Throwable $e) {
            Log::error("Text extraction failed", [
                'document_id' => $this->documentId,
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
                'attempts' => $this->attempts(),
            ]);

            if ($document ?? null) {
                $document->update([
                    'text_extraction_status' => 'failed',
                    'text_extraction_error' => $e->getMessage(),
                ]);
            }

            $this->fail($e);
        } finally {
            tenancy()->end();
        }
    }

    /**
     * Extract text from PDF file.
     * Primary: pdftotext (poppler-utils) — handles complex encodings, Arabic, Unicode.
     * Fallback: smalot/pdfparser (pure PHP).
     */
    private function extractTextFromPdf(string $filePath): string
    {
        // Primary: pdftotext (poppler-utils)
        $pdftotext = trim(shell_exec('which pdftotext 2>/dev/null') ?? '');
        if ($pdftotext !== '') {
            $escaped = escapeshellarg($filePath);
            $text = shell_exec("{$pdftotext} -enc UTF-8 -nopgbrk {$escaped} - 2>/dev/null");
            if (! empty(trim((string) $text))) {
                return $text;
            }
        }

        // Fallback: smalot/pdfparser
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf    = $parser->parseFile($filePath);
            $text   = $pdf->getText();
            if (! empty(trim($text))) {
                return $text;
            }
        }

        throw new \Exception("No text could be extracted from PDF. The file may be image-based or encrypted.");
    }

    /**
     * Extract text from DOCX file.
     */
    private function extractTextFromDocx(string $filePath): string
    {
        // Check if phpoffice/phpword is installed
        if (! class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            throw new \Exception("PHPWord library not installed. Run: composer require phpoffice/phpword");
        }

        $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
        $text = '';

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (method_exists($element, 'getText')) {
                    $text .= $element->getText() . "\n";
                } elseif (method_exists($element, 'getElements')) {
                    // Handle nested elements (like tables)
                    foreach ($element->getElements() as $childElement) {
                        if (method_exists($childElement, 'getText')) {
                            $text .= $childElement->getText() . "\n";
                        }
                    }
                }
            }
        }

        if (empty(trim($text))) {
            throw new \Exception("No text could be extracted from DOCX file.");
        }

        return $text;
    }

    /**
     * Extract text from TXT file.
     */
    private function extractTextFromTxt(string $filePath): string
    {
        $text = file_get_contents($filePath);

        if ($text === false) {
            throw new \Exception("Failed to read TXT file.");
        }

        if (empty(trim($text))) {
            throw new \Exception("TXT file is empty.");
        }

        return $text;
    }

    /**
     * Clean and normalize extracted text.
     */
    private function cleanExtractedText(string $text): string
    {
        // Remove excessive whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove control characters except newlines and tabs
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Normalize line breaks
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Remove multiple consecutive newlines
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Trim whitespace
        $text = trim($text);

        return $text;
    }
}
