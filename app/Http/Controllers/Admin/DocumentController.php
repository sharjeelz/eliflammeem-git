<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessDocumentText;
use App\Models\Document;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents with filters.
     */
    public function index(Request $request)
    {
        $query = Document::where('tenant_id', tenant('id'))
            ->with('category');

        // Search by title
        if ($search = $request->get('search')) {
            $query->where('title', 'ilike', "%{$search}%");
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        // Filter by chatbot inclusion
        if ($request->has('chatbot')) {
            $chatbot = $request->get('chatbot');
            if ($chatbot === 'enabled') {
                $query->where('include_in_chatbot', true);
            } elseif ($chatbot === 'disabled') {
                $query->where('include_in_chatbot', false);
            }
        }

        // Filter by text extraction status (Phase 2)
        if ($extractionStatus = $request->get('extraction_status')) {
            $query->where('text_extraction_status', $extractionStatus);
        }

        // Filter by embedding status (Phase 3)
        if ($embeddingStatus = $request->get('embedding_status')) {
            $query->where('embedding_status', $embeddingStatus);
        }

        $documents = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Get categories for filter dropdown
        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        // Document types for filter dropdown
        $types = [
            'policy' => 'Policy',
            'schedule' => 'Schedule',
            'event' => 'Event',
            'news' => 'News',
            'handbook' => 'Handbook',
            'form' => 'Form',
            'other' => 'Other',
        ];

        return view('tenant.admin.documents.index', compact('documents', 'categories', 'types'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        $types = [
            'policy' => 'Policy',
            'schedule' => 'Schedule',
            'event' => 'Event',
            'news' => 'News',
            'handbook' => 'Handbook',
            'form' => 'Form',
            'other' => 'Other',
        ];

        return view('tenant.admin.documents.create', compact('categories', 'types'));
    }

    /**
     * Store a newly uploaded document.
     */
    public function store(Request $request)
    {
        $maxSize = config('app.max_document_size_kb', 25600); // Default 25MB

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'type' => ['required', 'in:policy,schedule,event,news,handbook,form,other'],
            'allow_public_download' => ['boolean'],
            'include_in_chatbot' => ['boolean'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,txt', 'max:' . $maxSize],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validate category belongs to same tenant
        if ($request->filled('category_id')) {
            $category = DocumentCategory::find($data['category_id']);
            abort_unless($category && $category->tenant_id === tenant('id'), 404);
        }

        // Store file in tenant-scoped private disk
        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        // Generate unique slug
        $slug = $this->uniqueSlug($data['title'], tenant('id'));

        $isDownloadType = in_array($data['type'], ['form', 'handbook']);

        $document = Document::create([
            'tenant_id' => tenant('id'),
            'title' => $data['title'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'category_id' => $data['category_id'] ?? null,
            'type' => $data['type'],
            'include_in_chatbot'    => $isDownloadType ? false : $request->boolean('include_in_chatbot'),
            'allow_public_download' => $isDownloadType ? $request->boolean('allow_public_download') : false,
            'disk' => 'local',
            'path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'display_order' => $data['display_order'] ?? 0,
            'published_at' => now(), // Auto-publish for Phase 1
            'text_extraction_status' => 'pending',
        ]);

        // Only extract + embed if the document is included in the chatbot
        if ($document->include_in_chatbot) {
            dispatch(new ProcessDocumentText($document->id, tenant('id')));
        }

        return redirect()->route('tenant.admin.documents.index')
            ->with('ok', 'Document uploaded successfully.' . ($document->include_in_chatbot ? ' Text extraction in progress.' : ''));
    }

    /**
     * Show the form for editing a document.
     */
    public function edit(Document $document)
    {
        abort_unless($document->tenant_id === tenant('id'), 404);

        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        $types = [
            'policy' => 'Policy',
            'schedule' => 'Schedule',
            'event' => 'Event',
            'news' => 'News',
            'handbook' => 'Handbook',
            'form' => 'Form',
            'other' => 'Other',
        ];

        return view('tenant.admin.documents.edit', compact('document', 'categories', 'types'));
    }

    /**
     * Update the specified document (metadata and optionally replace file).
     */
    public function update(Request $request, Document $document)
    {
        abort_unless($document->tenant_id === tenant('id'), 404);

        $maxSize = config('app.max_document_size_kb', 25600);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'type' => ['required', 'in:policy,schedule,event,news,handbook,form,other'],
            'allow_public_download' => ['boolean'],
            'include_in_chatbot' => ['boolean'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,txt', 'max:' . $maxSize], // Optional file replacement
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validate category belongs to same tenant
        if ($request->filled('category_id')) {
            $category = DocumentCategory::find($data['category_id']);
            abort_unless($category && $category->tenant_id === tenant('id'), 404);
        }

        // If new file uploaded, replace old one
        if ($request->hasFile('file')) {
            // Delete old file
            Storage::disk($document->disk)->delete($document->path);

            // Store new file
            $file = $request->file('file');
            $path = $file->store('documents', 'local');

            $data['path'] = $path;
            $data['mime'] = $file->getMimeType();
            $data['size'] = $file->getSize();
            $data['disk'] = 'local';

            // Reset text extraction fields for re-processing
            $data['searchable_content'] = null;
            $data['text_extraction_status'] = 'pending';
            $data['text_extraction_error'] = null;
            $data['text_extracted_at'] = null;
            $data['text_extraction_attempts'] = 0;
        }

        // Re-slug if title changed
        if ($document->title !== $data['title']) {
            $data['slug'] = $this->uniqueSlug($data['title'], tenant('id'), $document->id);
        }

        $isDownloadType = in_array($data['type'], ['form', 'handbook']);
        $data['include_in_chatbot']    = $isDownloadType ? false : $request->boolean('include_in_chatbot');
        $data['allow_public_download'] = $isDownloadType ? $request->boolean('allow_public_download') : false;

        $document->update($data);

        // Only extract + embed if chatbot-included and a new file was uploaded
        if ($request->hasFile('file') && $document->include_in_chatbot) {
            dispatch(new ProcessDocumentText($document->id, tenant('id')));
        }

        return redirect()->route('tenant.admin.documents.edit', $document)
            ->with('ok', $request->hasFile('file')
                ? 'Document updated successfully. Text extraction in progress.'
                : 'Document updated successfully.');
    }

    /**
     * Remove the specified document (delete file + DB record).
     */
    public function destroy(Document $document)
    {
        abort_unless($document->tenant_id === tenant('id'), 404);

        // Delete file from storage
        Storage::disk($document->disk)->delete($document->path);

        // Delete DB record
        $document->delete();

        return redirect()->route('tenant.admin.documents.index')
            ->with('ok', 'Document deleted successfully.');
    }

    /**
     * Download document directly (admin only, no signed URL needed).
     */
    public function download(Document $document)
    {
        abort_unless($document->tenant_id === tenant('id'), 404);
        abort_unless(Storage::disk($document->disk)->exists($document->path), 404);

        $filename = Str::slug($document->title) . '.' . pathinfo($document->path, PATHINFO_EXTENSION);

        return Storage::disk($document->disk)->download($document->path, $filename);
    }

    /**
     * Generate a unique slug for the document.
     */
    private function uniqueSlug(string $title, string $tenantId, ?int $excludeId = null): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while (
            Document::where('tenant_id', $tenantId)
            ->where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
