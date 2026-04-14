<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of FAQs with filters.
     */
    public function index(Request $request)
    {
        $query = Faq::where('tenant_id', tenant('id'))
            ->with('category');

        // Search by question or answer
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'ilike', "%{$search}%")
                  ->orWhere('answer', 'ilike', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by published status
        if ($request->has('published')) {
            $published = $request->get('published');
            if ($published === 'yes') {
                $query->where('is_published', true);
            } elseif ($published === 'no') {
                $query->where('is_published', false);
            }
        }

        $faqs = $query->orderBy('display_order')
            ->orderBy('created_at', 'desc')
            ->paginate(25)
            ->withQueryString();

        // Get categories for filter dropdown
        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        return view('tenant.admin.faqs.index', compact('faqs', 'categories'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        $documents = Document::where('tenant_id', tenant('id'))
            ->orderBy('title')
            ->get();

        return view('tenant.admin.faqs.create', compact('categories', 'documents'));
    }

    /**
     * Store a newly created FAQ.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'is_published' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'related_document_ids' => ['nullable', 'array'],
            'related_document_ids.*' => ['exists:documents,id'],
        ]);

        // Validate category belongs to same tenant
        if ($request->filled('category_id')) {
            $category = DocumentCategory::find($data['category_id']);
            abort_unless($category && $category->tenant_id === tenant('id'), 404);
        }

        // Validate related documents belong to same tenant
        if ($request->filled('related_document_ids')) {
            $documents = Document::whereIn('id', $data['related_document_ids'])
                ->where('tenant_id', tenant('id'))
                ->pluck('id')
                ->toArray();
            $data['related_document_ids'] = $documents;
        }

        Faq::create([
            'tenant_id' => tenant('id'),
            'question' => $data['question'],
            'answer' => $data['answer'],
            'category_id' => $data['category_id'] ?? null,
            'is_published' => $request->boolean('is_published', true),
            'display_order' => $data['display_order'] ?? 0,
            'related_document_ids' => $data['related_document_ids'] ?? null,
        ]);

        return redirect()->route('tenant.admin.faqs.index')
            ->with('ok', 'FAQ created successfully.');
    }

    /**
     * Show the form for editing an FAQ.
     */
    public function edit(Faq $faq)
    {
        abort_unless($faq->tenant_id === tenant('id'), 404);

        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        $documents = Document::where('tenant_id', tenant('id'))
            ->orderBy('title')
            ->get();

        return view('tenant.admin.faqs.edit', compact('faq', 'categories', 'documents'));
    }

    /**
     * Update the specified FAQ.
     */
    public function update(Request $request, Faq $faq)
    {
        abort_unless($faq->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'question' => ['required', 'string', 'max:500'],
            'answer' => ['required', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'is_published' => ['boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'related_document_ids' => ['nullable', 'array'],
            'related_document_ids.*' => ['exists:documents,id'],
        ]);

        // Validate category belongs to same tenant
        if ($request->filled('category_id')) {
            $category = DocumentCategory::find($data['category_id']);
            abort_unless($category && $category->tenant_id === tenant('id'), 404);
        }

        // Validate related documents belong to same tenant
        if ($request->filled('related_document_ids')) {
            $documents = Document::whereIn('id', $data['related_document_ids'])
                ->where('tenant_id', tenant('id'))
                ->pluck('id')
                ->toArray();
            $data['related_document_ids'] = $documents;
        } else {
            $data['related_document_ids'] = null;
        }

        $faq->update([
            'question' => $data['question'],
            'answer' => $data['answer'],
            'category_id' => $data['category_id'] ?? null,
            'is_published' => $request->boolean('is_published'),
            'display_order' => $data['display_order'] ?? 0,
            'related_document_ids' => $data['related_document_ids'],
        ]);

        return redirect()->route('tenant.admin.faqs.edit', $faq)
            ->with('ok', 'FAQ updated successfully.');
    }

    /**
     * Remove the specified FAQ.
     */
    public function destroy(Faq $faq)
    {
        abort_unless($faq->tenant_id === tenant('id'), 404);

        $faq->delete();

        return redirect()->route('tenant.admin.faqs.index')
            ->with('ok', 'FAQ deleted successfully.');
    }
}
