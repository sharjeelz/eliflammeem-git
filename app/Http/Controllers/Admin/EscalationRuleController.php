<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\EscalationRule;
use App\Models\IssueCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EscalationRuleController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $rules = EscalationRule::where('tenant_id', tenant('id'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('tenant.admin.escalation_rules.index', compact('rules'));
    }

    public function create(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $branches   = Branch::active()->where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $categories = IssueCategory::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);

        return view('tenant.admin.escalation_rules.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->hasRole('admin'), 403);

        $data = $this->validated($request);

        EscalationRule::create(array_merge($data, ['tenant_id' => tenant('id')]));

        return redirect()->route('tenant.admin.escalation_rules.index')
            ->with('ok', 'Escalation rule created.');
    }

    public function edit(Request $request, EscalationRule $escalationRule)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($escalationRule->tenant_id === tenant('id'), 404);

        $branches   = Branch::active()->where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $categories = IssueCategory::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);

        return view('tenant.admin.escalation_rules.edit', compact('escalationRule', 'branches', 'categories'));
    }

    public function update(Request $request, EscalationRule $escalationRule)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($escalationRule->tenant_id === tenant('id'), 404);

        $escalationRule->update($this->validated($request));

        return redirect()->route('tenant.admin.escalation_rules.edit', $escalationRule)
            ->with('ok', 'Rule updated.');
    }

    public function destroy(Request $request, EscalationRule $escalationRule)
    {
        abort_unless($request->user()->hasRole('admin'), 403);
        abort_unless($escalationRule->tenant_id === tenant('id'), 404);

        $escalationRule->delete();

        return redirect()->route('tenant.admin.escalation_rules.index')
            ->with('ok', 'Rule deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'is_active'            => ['sometimes', 'boolean'],
            'trigger_status'       => ['required', Rule::in(['new', 'in_progress', 'resolved'])],
            'hours_threshold'      => ['required', 'integer', 'min:1', 'max:8760'],
            'priority_filter'      => ['nullable', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'action_notify_role'   => ['nullable', Rule::in(['admin', 'branch_manager', 'both'])],
            'action_bump_priority' => ['sometimes', 'boolean'],
            'scope_type'           => ['required', Rule::in(['global', 'branch', 'category'])],
            'scope_id'             => ['nullable', 'integer', 'required_if:scope_type,branch', 'required_if:scope_type,category'],
            'sort_order'           => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
