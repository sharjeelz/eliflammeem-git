<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IssueCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = IssueCategory::where('tenant_id', tenant('id'))
            ->orderBy('name');

        if ($search = $request->get('search')) {
            $q->where('name', 'ilike', "%{$search}%");
        }

        $categories = $q->paginate(25)->withQueryString();

        return view('tenant.admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('tenant.admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'default_sla_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
        ]);

        $tenantId = tenant('id');
        $slug = $this->uniqueSlug($data['name'], $tenantId);

        IssueCategory::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'slug' => $slug,
            'default_sla_hours' => $data['default_sla_hours'] ?? null,
        ]);

        return redirect()->route('tenant.admin.categories.index')
            ->with('ok', 'Category created successfully.');
    }

    public function edit(IssueCategory $category)
    {
        abort_unless($category->tenant_id === tenant('id'), 404);

        return view('tenant.admin.categories.edit', compact('category'));
    }

    public function update(Request $request, IssueCategory $category)
    {
        abort_unless($category->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'default_sla_hours' => ['nullable', 'integer', 'min:1', 'max:8760'],
        ]);

        // Re-slug only if name changed
        $slug = $category->name !== $data['name']
            ? $this->uniqueSlug($data['name'], tenant('id'), $category->id)
            : $category->slug;

        $category->update([
            'name' => $data['name'],
            'slug' => $slug,
            'default_sla_hours' => $data['default_sla_hours'] ?? null,
        ]);

        return redirect()->route('tenant.admin.categories.edit', $category)
            ->with('ok', 'Category updated.');
    }

    public function destroy(IssueCategory $category)
    {
        abort_unless($category->tenant_id === tenant('id'), 404);

        $category->delete();

        return redirect()->route('tenant.admin.categories.index')
            ->with('ok', 'Category deleted.');
    }

    private function uniqueSlug(string $name, string $tenantId, ?int $excludeId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (
            IssueCategory::where('tenant_id', $tenantId)
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
