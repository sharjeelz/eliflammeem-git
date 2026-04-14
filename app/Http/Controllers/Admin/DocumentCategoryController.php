<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DocumentCategoryController extends Controller
{
    /**
     * Display a listing of document categories.
     */
    public function index(Request $request)
    {
        $query = DocumentCategory::where('tenant_id', tenant('id'))
            ->with(['parent', 'children'])
            ->withCount(['documents', 'faqs']);

        if ($search = $request->get('search')) {
            $query->where('name', 'ilike', "%{$search}%");
        }

        // Get root categories and eager load all descendants
        $categories = $query->orderBy('display_order')
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('tenant.admin.document_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        // Get all categories for parent dropdown
        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->orderBy('name')
            ->get();

        return view('tenant.admin.document_categories.create', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'exists:document_categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $tenantId = tenant('id');

        // Validate parent belongs to same tenant
        if ($request->filled('parent_id')) {
            $parent = DocumentCategory::find($data['parent_id']);
            abort_unless($parent && $parent->tenant_id === $tenantId, 404);
        }

        $slug = $this->uniqueSlug($data['name'], $tenantId);

        DocumentCategory::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
        ]);

        return redirect()->route('tenant.admin.document_categories.index')
            ->with('ok', 'Category created successfully.');
    }

    /**
     * Show the form for editing a category.
     */
    public function edit(DocumentCategory $documentCategory)
    {
        abort_unless($documentCategory->tenant_id === tenant('id'), 404);

        // Get all categories except this one and its descendants (prevent circular reference)
        $categories = DocumentCategory::where('tenant_id', tenant('id'))
            ->where('id', '!=', $documentCategory->id)
            ->orderBy('name')
            ->get();

        return view('tenant.admin.document_categories.edit', compact('documentCategory', 'categories'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, DocumentCategory $documentCategory)
    {
        abort_unless($documentCategory->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'parent_id' => ['nullable', 'exists:document_categories,id'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        // Validate parent belongs to same tenant and prevent circular reference
        if ($request->filled('parent_id')) {
            $parent = DocumentCategory::find($data['parent_id']);
            abort_unless($parent && $parent->tenant_id === tenant('id'), 404);
            
            // Prevent setting self as parent
            abort_if($data['parent_id'] == $documentCategory->id, 422, 'Cannot set category as its own parent.');
            
            // Prevent circular reference (setting a child as parent)
            $checkParent = $parent;
            while ($checkParent->parent_id) {
                abort_if($checkParent->parent_id == $documentCategory->id, 422, 'Cannot create circular reference.');
                $checkParent = $checkParent->parent;
            }
        }

        // Re-slug only if name changed
        $slug = $documentCategory->name !== $data['name']
            ? $this->uniqueSlug($data['name'], tenant('id'), $documentCategory->id)
            : $documentCategory->slug;

        $documentCategory->update([
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $data['parent_id'] ?? null,
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'display_order' => $data['display_order'] ?? 0,
        ]);

        return redirect()->route('tenant.admin.document_categories.edit', $documentCategory)
            ->with('ok', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(DocumentCategory $documentCategory)
    {
        abort_unless($documentCategory->tenant_id === tenant('id'), 404);

        // Documents and FAQs will have category_id set to null (onDelete set null in migration)
        $documentCategory->delete();

        return redirect()->route('tenant.admin.document_categories.index')
            ->with('ok', 'Category deleted successfully.');
    }

    /**
     * Generate a unique slug for the category.
     */
    private function uniqueSlug(string $name, string $tenantId, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            DocumentCategory::where('tenant_id', $tenantId)
                ->where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
