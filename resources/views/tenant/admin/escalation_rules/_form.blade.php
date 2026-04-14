{{-- Shared form partial for create and edit --}}
<div class="row g-6">

    <div class="col-lg-8">

        {{-- Trigger --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">Trigger Condition</h3>
            </div>
            <div class="card-body">
                <div class="row g-5">

                    <div class="col-12">
                        <label class="form-label required fw-semibold">Rule Name</label>
                        <input name="name" value="{{ old('name', $escalationRule->name ?? '') }}"
                               class="form-control form-control-solid @error('name') is-invalid @enderror"
                               placeholder="e.g. Urgent unassigned > 2h" required>
                        @error('name')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required fw-semibold">When issue status is</label>
                        <select name="trigger_status"
                                class="form-select form-select-solid @error('trigger_status') is-invalid @enderror">
                            @foreach(['new' => 'New', 'in_progress' => 'In Progress', 'resolved' => 'Resolved'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('trigger_status', $escalationRule->trigger_status ?? '') === $val)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('trigger_status')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required fw-semibold">For longer than (hours)</label>
                        <div class="input-group">
                            <input type="number" name="hours_threshold"
                                   value="{{ old('hours_threshold', $escalationRule->hours_threshold ?? '') }}"
                                   class="form-control form-control-solid @error('hours_threshold') is-invalid @enderror"
                                   placeholder="e.g. 4" min="1" max="8760" required>
                            <span class="input-group-text border-0 bg-light text-muted">hrs</span>
                        </div>
                        @error('hours_threshold')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Priority filter
                            <span class="text-muted fw-normal ms-1 fs-8">(optional — leave blank for any)</span>
                        </label>
                        <select name="priority_filter"
                                class="form-select form-select-solid @error('priority_filter') is-invalid @enderror">
                            <option value="">Any priority</option>
                            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('priority_filter', $escalationRule->priority_filter ?? '') === $val)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">Actions to Take</h3>
            </div>
            <div class="card-body">
                <div class="row g-5">

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Notify role
                            <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                        </label>
                        <select name="action_notify_role"
                                class="form-select form-select-solid @error('action_notify_role') is-invalid @enderror">
                            <option value="">No notification</option>
                            <option value="branch_manager" @selected(old('action_notify_role', $escalationRule->action_notify_role ?? '') === 'branch_manager')>Branch Manager</option>
                            <option value="admin" @selected(old('action_notify_role', $escalationRule->action_notify_role ?? '') === 'admin')>Admin</option>
                            <option value="both" @selected(old('action_notify_role', $escalationRule->action_notify_role ?? '') === 'both')>Both</option>
                        </select>
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <label class="form-check form-switch form-check-custom form-check-solid mb-3">
                            <input class="form-check-input" type="checkbox" name="action_bump_priority" value="1"
                                   @checked(old('action_bump_priority', ($escalationRule->action_bump_priority ?? false) ? '1' : ''))>
                            <span class="form-check-label fw-semibold">
                                Bump priority one level
                                <span class="text-muted d-block fw-normal fs-8">e.g. Medium → High</span>
                            </span>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        {{-- Scope --}}
        <div class="card mb-5">
            <div class="card-header">
                <h3 class="card-title fw-bold">Scope</h3>
            </div>
            <div class="card-body">
                <div class="row g-5">

                    <div class="col-md-6">
                        <label class="form-label required fw-semibold">Apply to</label>
                        <select name="scope_type" id="scope_type"
                                class="form-select form-select-solid @error('scope_type') is-invalid @enderror">
                            <option value="global" @selected(old('scope_type', $escalationRule->scope_type ?? 'global') === 'global')>All branches & categories</option>
                            <option value="branch" @selected(old('scope_type', $escalationRule->scope_type ?? '') === 'branch')>Specific branch</option>
                            <option value="category" @selected(old('scope_type', $escalationRule->scope_type ?? '') === 'category')>Specific category</option>
                        </select>
                    </div>

                    <div class="col-md-6" id="scope_id_wrapper" style="display:none">
                        <label class="form-label fw-semibold" id="scope_id_label">Select</label>

                        <select name="scope_id" id="scope_id_branch"
                                class="form-select form-select-solid scope-select @error('scope_id') is-invalid @enderror"
                                style="display:none">
                            <option value="">— Choose branch —</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" @selected(old('scope_id', $escalationRule->scope_id ?? '') == $b->id)>
                                    {{ $b->name }}
                                </option>
                            @endforeach
                        </select>

                        <select name="scope_id" id="scope_id_category"
                                class="form-select form-select-solid scope-select @error('scope_id') is-invalid @enderror"
                                style="display:none">
                            <option value="">— Choose category —</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected(old('scope_id', $escalationRule->scope_id ?? '') == $c->id)>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('scope_id')
                            <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        {{-- Active toggle + Sort --}}
        <div class="card mb-5">
            <div class="card-body">
                <div class="row g-5 align-items-center">
                    <div class="col-md-6">
                        <label class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                   @checked(old('is_active', ($escalationRule->is_active ?? true) ? '1' : ''))>
                            <span class="form-check-label fw-semibold">Rule is active</span>
                        </label>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Sort order <span class="text-muted fw-normal fs-8">(lower = runs first)</span></label>
                        <input type="number" name="sort_order"
                               value="{{ old('sort_order', $escalationRule->sort_order ?? 0) }}"
                               class="form-control form-control-solid" min="0">
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-3">
            <button type="submit" class="btn btn-primary">
                <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                {{ isset($escalationRule->id) ? 'Update Rule' : 'Create Rule' }}
            </button>
            <a href="{{ route('tenant.admin.escalation_rules.index') }}" class="btn btn-light">Cancel</a>
        </div>

    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="card border border-dashed border-warning">
            <div class="card-body py-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="ki-duotone ki-time fs-2x text-warning">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <div class="fw-bold text-gray-800">How rules work</div>
                </div>
                <ul class="text-muted fs-7 ps-4 mb-0">
                    <li class="mb-2">Rules are checked every <strong>15 minutes</strong> automatically.</li>
                    <li class="mb-2">Each rule fires <strong>once per issue</strong> — no repeated spam.</li>
                    <li class="mb-2"><strong>Priority bump</strong> escalates one step: Low → Medium → High → Urgent.</li>
                    <li class="mb-2">Branch managers are only notified for issues in <strong>their branch</strong>.</li>
                    <li>Inactive rules are saved but never run.</li>
                </ul>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    const scopeType    = document.getElementById('scope_type');
    const wrapper      = document.getElementById('scope_id_wrapper');
    const branchSel    = document.getElementById('scope_id_branch');
    const categorySel  = document.getElementById('scope_id_category');
    const label        = document.getElementById('scope_id_label');

    function toggle() {
        const val = scopeType.value;
        wrapper.style.display      = val === 'global' ? 'none' : '';
        branchSel.style.display    = val === 'branch' ? '' : 'none';
        branchSel.disabled         = val !== 'branch';
        categorySel.style.display  = val === 'category' ? '' : 'none';
        categorySel.disabled       = val !== 'category';
        label.textContent          = val === 'branch' ? 'Select branch' : 'Select category';
    }

    scopeType.addEventListener('change', toggle);
    toggle(); // run on load
})();
</script>
@endpush
