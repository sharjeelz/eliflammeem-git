<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolKudo;
use Illuminate\Http\Request;

class KudoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('staff')) {
            abort(403);
        }

        $query = SchoolKudo::where('tenant_id', tenant('id'))
            ->with('contact:id,name,role', 'category:id,name', 'branch:id,name')
            ->orderByDesc('created_at');

        // Branch managers see only their branches
        if ($user->hasRole('branch_manager')) {
            $branchIds = $user->branches()->pluck('branches.id')->all();
            $query->whereIn('branch_id', $branchIds ?: [-1]);
        }

        // Filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('issue_category_id', $request->integer('category_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $kudos = $query->paginate(25)->withQueryString();

        $branches   = \App\Models\Branch::where('tenant_id', tenant('id'))->orderBy('name')->get();
        $categories = \App\Models\IssueCategory::where('tenant_id', tenant('id'))->orderBy('name')->get();

        return view('tenant.admin.kudos.index', compact('kudos', 'branches', 'categories'));
    }

    public function destroy(int $id)
    {
        $kudo = SchoolKudo::where('tenant_id', tenant('id'))->findOrFail($id);
        $kudo->delete();

        return back()->with('success', 'Compliment deleted.');
    }
}
