@extends('layouts.tenant_admin')
@section('page_title', 'Roster Contacts')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @if(session('import_warnings'))
            <div class="card border border-dashed border-warning mb-5">
                <div class="card-body py-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <i class="ki-duotone ki-information-5 fs-2x text-warning">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div class="fw-bold text-gray-800">Some rows were skipped during import</div>
                    </div>
                    <ul class="mb-0 ps-3">
                        @foreach(session('import_warnings') as $warn)
                            <li class="text-muted fs-7">{{ $warn }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Roster Contacts</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Roster Contacts</li>
            </ul>
        </div>
        @endpush

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.admin.contacts.index') }}" class="card card-body mb-5 py-4">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                <div class="flex-grow-1" style="min-width:200px">
                    <label class="form-label fs-7 mb-1">Search</label>
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-solid ps-10"
                               placeholder="Name, email or phone…">
                    </div>
                </div>

                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">Role</label>
                    <select name="role" class="form-select form-select-solid">
                        <option value="">All roles</option>
                        @foreach(['parent','teacher'] as $r)
                            <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width:160px">
                    <label class="form-label fs-7 mb-1">Branch</label>
                    <select name="branch_id" class="form-select form-select-solid">
                        <option value="">All branches</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="min-width:160px">
                    <label class="form-label fs-7 mb-1">Code Status</label>
                    <select name="code_status" class="form-select form-select-solid">
                        <option value="">Any (active)</option>
                        <option value="active"      @selected(request('code_status') === 'active')>Active</option>
                        <option value="not_sent"    @selected(request('code_status') === 'not_sent')>Not sent yet</option>
                        <option value="expired"     @selected(request('code_status') === 'expired')>Expired</option>
                        <option value="used"        @selected(request('code_status') === 'used')>Used</option>
                        <option value="none"        @selected(request('code_status') === 'none')>No code</option>
                        <option value="deactivated" @selected(request('code_status') === 'deactivated')>Deactivated</option>
                    </select>
                </div>

                <div class="d-flex gap-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.contacts.index') }}" class="btn btn-light">Reset</a>
                </div>

            </div>
        </form>

        {{-- Table --}}
        <form method="POST" action="{{ route('tenant.admin.contacts.bulk-send-code') }}" id="bulk-form">
        @csrf
        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $contacts->total() }} contact{{ $contacts->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-toolbar d-flex gap-2">
                    <a href="{{ route('tenant.admin.contacts.import') }}" class="btn btn-light-primary">
                        <i class="ki-duotone ki-file-up fs-2"><span class="path1"></span><span class="path2"></span></i>
                        Import
                    </a>
                    <a href="{{ route('tenant.admin.contacts.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Contact
                    </a>
                </div>
            </div>

            {{-- Bulk action bar — shown below header when rows are selected --}}
            <div id="bulk-bar" class="d-none px-6 py-3 border-top border-dashed border-gray-300 bg-light-primary">
                <div class="d-flex align-items-center gap-3">
                    <span id="bulk-count" class="text-primary fw-semibold fs-7"></span>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Bulk Actions
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="generate">
                                    <i class="ki-duotone ki-plus fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Generate Code
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" data-bulk-action="revoke">
                                    <i class="ki-duotone ki-trash fs-5 me-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    Revoke Code
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="send-email">
                                    <i class="ki-duotone ki-sms fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Send Access Code via Email
                                </a>
                            </li>
                            @if($smsEnabled)
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="send-sms">
                                    <i class="ki-duotone ki-phone fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Send Access Code via SMS
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#" data-bulk-action="change-branch">
                                    <i class="ki-duotone ki-geolocation fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Change Branch
                                </a>
                            </li>
                            @if($planAllowBroadcasting ?? false)
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-warning fw-semibold" href="#" id="bulk-broadcast">
                                    <i class="ki-duotone ki-send fs-5 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    Broadcast to Selected
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="w-30px">
                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="select-all">
                                </div>
                            </th>
                            <th>Contact</th>
                            <th>Branch</th>
                            <th>Email / Phone</th>
                            <th>Access Code</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($contacts as $contact)
                            @php
                                $code           = $contact->accessCodes->first();
                                $roleColor      = ['parent' => 'primary', 'teacher' => 'success'][$contact->role] ?? 'secondary';
                                $codeUsed       = $code && $code->used_at;
                                $codeExpired    = $code && !$codeUsed && $code->expires_at && $code->expires_at->isPast();
                                $codeActive     = $code && !$codeUsed && !$codeExpired;
                                $isDeactivated  = (bool) $contact->deactivated_at;
                            @endphp
                            <tr @if($isDeactivated) class="opacity-50" @endif>
                                {{-- Checkbox --}}
                                <td>
                                    <div class="form-check form-check-sm form-check-custom form-check-solid">
                                        <input class="form-check-input row-check" type="checkbox"
                                               name="contact_ids[]" value="{{ $contact->id }}">
                                    </div>
                                </td>
                                {{-- Contact --}}
                                <td class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                                        <div class="symbol-label fw-bold fs-6 bg-light-{{ $roleColor }} text-{{ $roleColor }}">
                                            {{ strtoupper(substr($contact->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('tenant.admin.contacts.edit', $contact) }}"
                                           class="text-gray-800 text-hover-primary fw-bold d-block">
                                            {{ $contact->name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="badge badge-light-{{ $roleColor }} fs-8">{{ ucfirst($contact->role) }}</span>
                                            @if($isDeactivated)
                                                <span class="badge badge-light-danger fs-8">Deactivated</span>
                                            @endif
                                            @if($contact->external_id)
                                                <span class="text-muted fs-8">ID: {{ $contact->external_id }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Branch --}}
                                <td>{{ $contact->branch->name ?? '—' }}</td>

                                {{-- Email / Phone --}}
                                <td>
                                    <div>{{ $contact->email ?? '—' }}</div>
                                    @if($contact->phone)
                                        <div class="text-muted fs-7">{{ $contact->phone }}</div>
                                    @endif
                                </td>

                                {{-- Access Code --}}
                                <td>
                                    @if($code)
                                        <div class="d-flex flex-column gap-1">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="badge badge-light-{{ $codeActive ? 'success' : ($codeExpired ? 'warning' : 'secondary') }} fw-bold font-monospace fs-7">
                                                    {{ $code->code }}
                                                </span>
                                                @if($codeActive)
                                                    <span class="badge badge-light-success fs-8">Active</span>
                                                @elseif($codeExpired)
                                                    <span class="badge badge-light-warning fs-8">Expired</span>
                                                @elseif($codeUsed)
                                                    <span class="badge badge-light-secondary fs-8">Used</span>
                                                @endif
                                            </div>
                                            <div class="text-muted fs-8">
                                                @if($codeUsed)
                                                    Used {{ $code->used_at->diffForHumans() }}
                                                @elseif($code->expires_at)
                                                    @if($codeExpired)
                                                        Expired {{ $code->expires_at->diffForHumans() }}
                                                    @else
                                                        Expires {{ $code->expires_at->diffForHumans() }}
                                                    @endif
                                                @else
                                                    No expiry set
                                                @endif
                                            </div>
                                            <div class="fs-8">
                                                @if($code->send_error)
                                                    <span class="text-danger fw-semibold" title="{{ $code->send_error }}">
                                                        <i class="ki-solid ki-cross-circle fs-8"></i>
                                                        Send failed
                                                    </span>
                                                    <div class="text-danger fs-8 text-truncate" style="max-width:200px" title="{{ $code->send_error }}">
                                                        {{ Str::limit($code->send_error, 55) }}
                                                    </div>
                                                @elseif($code->sent_at)
                                                    <span class="text-success">
                                                        <i class="ki-solid ki-check fs-8"></i>
                                                        Sent {{ $code->sent_at->diffForHumans() }}
                                                    </span>
                                                @else
                                                    <span class="text-warning fw-semibold">Not sent yet</span>
                                                @endif
                                            </div>
                                            @if($code && ($contact->email || ($smsEnabled && $contact->phone)))
                                                <div class="d-flex gap-1">
                                                    @if($contact->email)
                                                        <button type="button" class="btn btn-sm btn-light-primary py-1 px-2 fs-8 btn-row-send" title="Send via email"
                                                                data-url="{{ route('tenant.admin.contacts.send-code', $contact) }}"
                                                                data-channel="email"
                                                                data-name="{{ $contact->name }}"
                                                                data-contact="{{ $contact->email }}">
                                                            <i class="ki-duotone ki-sms fs-7"><span class="path1"></span><span class="path2"></span></i>
                                                        </button>
                                                    @endif
                                                    @if($smsEnabled && $contact->phone)
                                                        <button type="button" class="btn btn-sm btn-light-success py-1 px-2 fs-8 btn-row-send" title="Send via SMS"
                                                                data-url="{{ route('tenant.admin.contacts.send-code', $contact) }}"
                                                                data-channel="sms"
                                                                data-name="{{ $contact->name }}"
                                                                data-contact="{{ $contact->phone }}">
                                                            <i class="ki-duotone ki-phone fs-7"><span class="path1"></span><span class="path2"></span></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        @if($contact->revoke_reason)
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge badge-light-danger fs-8">Revoked</span>
                                                <span class="text-muted fs-8" title="{{ $contact->revoke_reason }}">
                                                    {{ Str::limit($contact->revoke_reason, 40) }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-muted fs-7">No code</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.contacts.edit', $contact) }}"
                                       class="btn btn-sm btn-light btn-active-light-primary me-1">Edit</a>

                                    @if($isDeactivated)
                                        <button type="button" class="btn btn-sm btn-light-success btn-row-post"
                                                data-url="{{ route('tenant.admin.contacts.reactivate', $contact) }}">Reactivate</button>
                                    @elseif(! $code)
                                        <button type="button" class="btn btn-sm btn-light-success btn-row-post"
                                                data-url="{{ route('tenant.admin.contacts.generate-code', $contact) }}">Generate</button>
                                    @elseif($codeExpired || $codeUsed)
                                        <button type="button" class="btn btn-sm btn-light-warning btn-row-post me-1"
                                                data-url="{{ route('tenant.admin.contacts.renew-code', $contact) }}">Renew</button>
                                        <button type="button" class="btn btn-sm btn-light-danger btn-revoke-single"
                                                data-name="{{ $contact->name }}"
                                                data-url="{{ route('tenant.admin.contacts.revoke-code', $contact) }}">Revoke</button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-light-danger btn-revoke-single"
                                                data-name="{{ $contact->name }}"
                                                data-url="{{ route('tenant.admin.contacts.revoke-code', $contact) }}">Revoke</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-10">
                                    No contacts found.
                                    <a href="{{ route('tenant.admin.contacts.create') }}">Add one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $contacts->links() }}
                </div>
            </div>
        </div>
        </form>

    </div>
</div>

{{-- Change Branch modal --}}
<div class="modal fade" id="changeBranchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">Change Branch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning d-flex gap-3 p-4 mb-4">
                    <i class="ki-duotone ki-warning-2 fs-2x text-warning flex-shrink-0">
                        <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                    </i>
                    <div class="fs-7">
                        <div class="fw-bold text-gray-800 mb-1">This action has immediate effects:</div>
                        <ul class="mb-0 ps-3 text-gray-700">
                            <li>Their <strong>access codes will be revoked</strong> — they'll need new ones</li>
                            <li>Any <strong>open issues will be auto-closed</strong></li>
                            <li>All changes are <strong>logged in the activity log</strong></li>
                        </ul>
                    </div>
                </div>
                <label class="form-label fw-semibold">Move selected contacts to:</label>
                <select id="branch-picker" class="form-select form-select-solid">
                    <option value="">— Select branch —</option>
                    @foreach($branches as $b)
                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="changeBranchOk">Yes, Move Contacts</button>
            </div>
        </div>
    </div>
</div>

{{-- Confirmation modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:420px">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="confirmModalTitle">Are you sure?</h5>
                    <div class="text-muted fs-7 mt-1" id="confirmModalBody"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmModalOk">Confirm</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form      = document.getElementById('bulk-form');
    const selectAll = document.getElementById('select-all');
    const bulkBar   = document.getElementById('bulk-bar');
    const bulkCount = document.getElementById('bulk-count');

    const modal      = new bootstrap.Modal(document.getElementById('confirmModal'));
    const modalTitle = document.getElementById('confirmModalTitle');
    const modalBody  = document.getElementById('confirmModalBody');

    const urls = {
        'generate'      : '{{ route('tenant.admin.contacts.bulk-generate-code') }}',
        'revoke'        : '{{ route('tenant.admin.contacts.bulk-revoke-code') }}',
        'send-email'    : '{{ route('tenant.admin.contacts.bulk-send-code') }}',
        'send-sms'      : '{{ route('tenant.admin.contacts.bulk-send-code') }}',
        'change-branch' : '{{ route('tenant.admin.contacts.bulk-change-branch') }}',
    };

    // ── Change Branch modal ────────────────────────────────────────────────
    const changeBranchModal  = new bootstrap.Modal(document.getElementById('changeBranchModal'));
    const branchPicker       = document.getElementById('branch-picker');
    document.getElementById('changeBranchOk').addEventListener('click', function () {
        const branchId = branchPicker.value;
        if (! branchId) { branchPicker.focus(); return; }
        changeBranchModal.hide();
        document.querySelectorAll('input[name="branch_id"]').forEach(i => i.remove());
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'branch_id'; input.value = branchId;
        form.appendChild(input);
        form.action = urls['change-branch'];
        form.submit();
    });

    // Show the confirm modal. btnClass defaults to 'btn-primary'; use 'btn-danger' for destructive actions.
    function askConfirm(title, body, onConfirm, btnClass) {
        btnClass = btnClass || 'btn-primary';
        modalTitle.textContent = title;
        modalBody.textContent  = body;
        // Always re-query so we always find the current button (may have been replaced before)
        const currentOk = document.getElementById('confirmModalOk');
        const fresh = document.createElement('button');
        fresh.type      = 'button';
        fresh.id        = 'confirmModalOk';
        fresh.className = 'btn ' + btnClass;
        fresh.textContent = 'Confirm';
        currentOk.replaceWith(fresh);
        fresh.addEventListener('click', function () {
            modal.hide();
            onConfirm();
        });
        modal.show();
    }

    // ── Checkbox / select-all ──────────────────────────────────────────────
    function updateBar() {
        const checked = document.querySelectorAll('.row-check:checked');
        if (checked.length > 0) {
            bulkBar.classList.remove('d-none');
            bulkCount.textContent = checked.length + ' selected';
        } else {
            bulkBar.classList.add('d-none');
            bulkCount.textContent = '';
        }
        const all = document.querySelectorAll('.row-check');
        selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
        selectAll.checked = all.length > 0 && checked.length === all.length;
    }

    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        updateBar();
    });
    document.querySelectorAll('.row-check').forEach(cb => cb.addEventListener('change', updateBar));

    // ── Bulk dropdown actions ──────────────────────────────────────────────
    function submitBulk(action) {
        document.querySelectorAll('input[name="channel"]').forEach(i => i.remove());
        if (action === 'send-email' || action === 'send-sms') {
            const input = document.createElement('input');
            input.type  = 'hidden';
            input.name  = 'channel';
            input.value = action === 'send-email' ? 'email' : 'sms';
            form.appendChild(input);
        }
        form.action = urls[action];
        form.submit();
    }

    document.querySelectorAll('[data-bulk-action]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            const action = this.dataset.bulkAction;
            const count  = document.querySelectorAll('.row-check:checked').length;

            if (action === 'change-branch') {
                branchPicker.value = '';
                changeBranchModal.show();
            } else if (action === 'revoke') {
                askConfirm(
                    'Revoke Access Codes',
                    'This will revoke access codes for ' + count + ' selected contact(s). They will no longer be able to log in until a new code is generated.',
                    () => submitBulk(action),
                    'btn-danger'
                );
            } else if (action === 'send-email') {
                askConfirm(
                    'Send Access Codes via Email',
                    'Queue access code emails for ' + count + ' selected contact(s)? Contacts without a code or email address will be skipped.',
                    () => submitBulk(action),
                    'btn-primary'
                );
            } else if (action === 'send-sms') {
                askConfirm(
                    'Send Access Codes via SMS',
                    'Queue access code SMS messages for ' + count + ' selected contact(s)? Contacts without a code or phone number will be skipped.',
                    () => submitBulk(action),
                    'btn-primary'
                );
            } else {
                submitBulk(action);
            }
        });
    });


    // ── submitPost: create a temp form outside bulk-form and POST ─────────
    function submitPost(url, extra) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = url;
        f.style.display = 'none';
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
        f.appendChild(csrf);
        if (extra) {
            Object.entries(extra).forEach(([k, v]) => {
                const i = document.createElement('input');
                i.type = 'hidden'; i.name = k; i.value = v;
                f.appendChild(i);
            });
        }
        document.body.appendChild(f);
        f.submit();
    }

    // ── Broadcast to selected ─────────────────────────────────────────────
    const bulkBroadcastBtn = document.getElementById('bulk-broadcast');
    if (bulkBroadcastBtn) {
        bulkBroadcastBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const ids = Array.from(document.querySelectorAll('.row-check:checked')).map(cb => cb.value);
            if (!ids.length) return;
            window.location.href = '{{ route('tenant.admin.contacts.broadcast') }}?ids=' + ids.join(',');
        });
    }

    // ── Per-row action buttons (generate / reactivate / renew) — no confirmation ──
    document.querySelectorAll('.btn-row-post').forEach(function (btn) {
        btn.addEventListener('click', function () {
            submitPost(this.dataset.url);
        });
    });

    // ── Per-row send icon buttons (email / SMS) — confirmation required ────
    document.querySelectorAll('.btn-row-send').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name    = this.dataset.name;
            const contact = this.dataset.contact;
            const channel = this.dataset.channel;
            const url     = this.dataset.url;
            const isEmail = channel === 'email';
            askConfirm(
                isEmail ? 'Send Access Code via Email' : 'Send Access Code via SMS',
                'Send the access code to ' + name + ' at ' + contact + (isEmail ? ' via email?' : ' via SMS?'),
                () => submitPost(url, { channel: channel }),
                'btn-primary'
            );
        });
    });

    // ── Per-row revoke buttons ─────────────────────────────────────────────
    document.querySelectorAll('.btn-revoke-single').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const name = this.dataset.name;
            const url  = this.dataset.url;
            askConfirm(
                'Revoke Access Code',
                'This will revoke the access code for ' + name + '. They will no longer be able to log in until a new code is generated.',
                () => submitPost(url),
                'btn-danger'
            );
        });
    });
})();
</script>
@endpush

@endsection
