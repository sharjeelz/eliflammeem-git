@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Contact</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.index') }}" class="text-muted text-hover-primary">Contacts</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $contact->name }}</li>
            </ul>
        </div>
        @endpush

        <div class="row g-5">

            {{-- Edit form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.contacts.update', $contact) }}" class="card card-body">
                    @csrf @method('PUT')
                    <div class="row g-4">

                        <div class="col-md-6">
                            <label class="form-label required">Full Name</label>
                            <input name="name" value="{{ old('name', $contact->name) }}" class="form-control form-control-solid" required>
                            @error('name') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label required">Role</label>
                            <select name="role" class="form-select form-select-solid" required>
                                @foreach(['parent','teacher'] as $r)
                                    <option value="{{ $r }}" @selected(old('role', $contact->role) === $r)>{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                            @error('role') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Branch</label>
                            <select name="branch_id" class="form-select form-select-solid">
                                <option value="">— None —</option>
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}"
                                        @selected(old('branch_id', $contact->branch_id) == $b->id)
                                        @if($b->id == $contact->branch_id) data-current="1" @endif>
                                        {{ $b->name }}{{ $b->id == $contact->branch_id ? ' (current)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Email <span class="text-muted fw-normal fs-8">(at least one required)</span></label>
                            <input type="email" name="email" value="{{ old('email', $contact->email) }}" class="form-control form-control-solid">
                            @error('email') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Phone <span class="text-muted fw-normal fs-8">(at least one required)</span></label>
                            <input name="phone" value="{{ old('phone', $contact->phone) }}" class="form-control form-control-solid">
                            @error('phone') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">External ID</label>
                            <input name="external_id" value="{{ old('external_id', $contact->external_id) }}" class="form-control form-control-solid">
                            @error('external_id') <div class="text-danger fs-7 mt-1">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 d-flex gap-3 pt-2">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                            <a href="{{ route('tenant.admin.contacts.index') }}" class="btn btn-light">Cancel</a>
                        </div>

                    </div>
                </form>
            </div>

            {{-- Access Code panel --}}
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Access Code</h3>
                        @if($code)
                            @php
                                $codeUsed    = (bool) $code->used_at;
                                $codeExpired = !$codeUsed && $code->expires_at && $code->expires_at->isPast();
                                $codeActive  = !$codeUsed && !$codeExpired;
                            @endphp
                            <div class="card-toolbar">
                                @if($codeActive)
                                    <span class="badge badge-light-success">Active</span>
                                @elseif($codeExpired)
                                    <span class="badge badge-light-warning">Expired</span>
                                @else
                                    <span class="badge badge-light-secondary">Used</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        @if($code)
                            <div class="mb-5 text-center">
                                <span class="fs-1 fw-bold font-monospace {{ $codeActive ? 'text-success' : ($codeExpired ? 'text-warning' : 'text-muted') }}">
                                    {{ $code->code }}
                                </span>
                                <div class="text-muted fs-7 mt-1">Generated {{ $code->created_at->diffForHumans() }}</div>
                                @if($code->expires_at)
                                    <div class="fs-7 mt-1 {{ $codeExpired ? 'text-warning fw-semibold' : 'text-muted' }}">
                                        @if($codeExpired)
                                            <i class="ki-solid ki-warning fs-6 text-warning me-1"></i>
                                            Expired {{ $code->expires_at->diffForHumans() }}
                                        @else
                                            Expires {{ $code->expires_at->diffForHumans() }}
                                            ({{ $code->expires_at->format('d M Y') }})
                                        @endif
                                    </div>
                                @endif
                                @if($codeUsed)
                                    <div class="text-muted fs-7 mt-1">
                                        Used {{ $code->used_at->diffForHumans() }}
                                    </div>
                                @endif
                                @if($code->send_error)
                                    <div class="alert alert-danger py-2 px-3 mt-3 fs-7 text-start">
                                        <div class="fw-semibold mb-1">
                                            <i class="ki-solid ki-cross-circle fs-6 me-1"></i>Last send failed
                                        </div>
                                        <div class="font-monospace fs-8" style="word-break:break-word">{{ $code->send_error }}</div>
                                    </div>
                                @endif
                            </div>

                            @if($codeExpired || $codeUsed)
                                <form method="POST" action="{{ route('tenant.admin.contacts.renew-code', $contact) }}" class="mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="ki-duotone ki-arrows-circle fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                        Renew Code (new 7-day code)
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('tenant.admin.contacts.revoke-code', $contact) }}"
                                  id="revoke-form">
                                @csrf
                                <button type="button" class="btn btn-danger w-100" id="btn-revoke">Revoke Code</button>
                            </form>
                        @else
                            @if($contact->revoke_reason)
                                <div class="alert alert-danger py-3 mb-4 fs-7">
                                    <i class="ki-duotone ki-shield-slash fs-4 me-2"><span class="path1"></span><span class="path2"></span></i>
                                    <strong>Revoked:</strong> {{ $contact->revoke_reason }}
                                </div>
                            @else
                                <p class="text-muted fs-7 mb-4">This contact has no access code yet. Generate one so they can submit issues.</p>
                            @endif
                            <form method="POST" action="{{ route('tenant.admin.contacts.generate-code', $contact) }}">
                                @csrf
                                <button class="btn btn-success w-100">Generate Access Code</button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Send Code --}}
                @if($code)
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="card-title">Send Code</h3>
                    </div>
                    <div class="card-body">
                        @error('channel')
                            <div class="alert alert-danger py-2 fs-7 mb-3">{{ $message }}</div>
                        @enderror

                        <div class="d-flex flex-column gap-2">

                            <button type="button" id="btn-send-email"
                                    class="btn btn-light-primary w-100 {{ !$contact->email ? 'disabled' : '' }}"
                                    {{ !$contact->email ? 'disabled' : '' }}
                                    data-url="{{ route('tenant.admin.contacts.send-code', $contact) }}"
                                    data-contact="{{ $contact->email ?? '' }}">
                                <i class="ki-duotone ki-sms fs-3"><span class="path1"></span><span class="path2"></span></i>
                                Send via Email
                                @if(!$contact->email)
                                    <span class="fs-8 text-muted">(no email)</span>
                                @endif
                            </button>

                            @if($smsEnabled)
                            <button type="button" id="btn-send-sms"
                                    class="btn btn-light-success w-100 {{ !$contact->phone ? 'disabled' : '' }}"
                                    {{ !$contact->phone ? 'disabled' : '' }}
                                    data-url="{{ route('tenant.admin.contacts.send-code', $contact) }}"
                                    data-contact="{{ $contact->phone ?? '' }}">
                                <i class="ki-duotone ki-phone fs-3"><span class="path1"></span><span class="path2"></span></i>
                                Send via SMS
                                @if(!$contact->phone)
                                    <span class="fs-8 text-muted">(no phone)</span>
                                @endif
                            </button>
                            @endif

                        </div>
                    </div>
                </div>
                @endif

                {{-- Submitted Issues --}}
                <div class="card mt-5">
                    <div class="card-header">
                        <h3 class="card-title">
                            Submitted Issues
                            <span class="badge badge-light-primary ms-2">{{ $issueCount }}</span>
                            @if($spamCount > 0)
                                <span class="badge badge-light-danger ms-1">{{ $spamCount }} spam</span>
                            @endif
                        </h3>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('tenant.admin.contacts.issues', $contact) }}"
                           class="btn btn-light-primary w-100">
                            <i class="ki-duotone ki-eye fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                            View All Issues
                        </a>
                    </div>
                </div>

                {{-- Import Source --}}
                @php
                    $sourceChannel  = $code?->channel ?? null;
                    $sourceMap = [
                        'api'    => ['label' => 'REST API',    'icon' => 'ki-abstract-26',  'color' => 'primary'],
                        'csv'    => ['label' => 'CSV Import',  'icon' => 'ki-file-up',      'color' => 'success'],
                        'manual' => ['label' => 'Manual',      'icon' => 'ki-user-edit',    'color' => 'info'],
                        'email'  => ['label' => 'Auto (Email)','icon' => 'ki-sms',          'color' => 'warning'],
                        'sms'    => ['label' => 'Auto (SMS)',  'icon' => 'ki-phone',        'color' => 'warning'],
                    ];
                    $source = $sourceChannel ? ($sourceMap[$sourceChannel] ?? ['label' => ucfirst($sourceChannel), 'icon' => 'ki-information', 'color' => 'secondary']) : null;
                @endphp
                <div class="card mt-5">
                    <div class="card-header py-3 min-h-auto">
                        <h3 class="card-title fw-bold fs-7 text-muted text-uppercase">Contact Source</h3>
                    </div>
                    <div class="card-body py-4">
                        <div class="d-flex flex-column gap-3 fs-7">

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Added via</span>
                                @if($source)
                                    <span class="badge badge-light-{{ $source['color'] }} d-inline-flex align-items-center gap-1">
                                        <i class="ki-duotone {{ $source['icon'] }} fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        {{ $source['label'] }}
                                    </span>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Created</span>
                                <span class="fw-semibold text-gray-700">{{ $contact->created_at->format('d M Y') }}</span>
                            </div>

                            @if($contact->external_id)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">External ID</span>
                                <code class="fs-8 text-gray-700">{{ $contact->external_id }}</code>
                            </div>
                            @endif

                            @if($code?->sent_at)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Code sent</span>
                                <span class="fw-semibold text-gray-700">{{ $code->sent_at->diffForHumans() }}</span>
                            </div>
                            @endif

                        </div>
                    </div>
                </div>

                {{-- Delete --}}
                <div class="card mt-5">
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.admin.contacts.destroy', $contact) }}"
                              id="delete-form">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-light-danger w-100" id="btn-delete">Delete Contact</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
{{-- Confirmation modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="confirmModalTitle"></h5>
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
    const modal      = new bootstrap.Modal(document.getElementById('confirmModal'));
    const modalTitle = document.getElementById('confirmModalTitle');
    const modalBody  = document.getElementById('confirmModalBody');

    function askConfirm(title, body, onConfirm, btnClass, isHtml) {
        btnClass = btnClass || 'btn-danger';
        modalTitle.textContent = title;
        if (isHtml) { modalBody.innerHTML = body; } else { modalBody.textContent = body; }
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

    // Revoke
    const btnRevoke = document.getElementById('btn-revoke');
    if (btnRevoke) {
        btnRevoke.addEventListener('click', function () {
            askConfirm(
                'Revoke Access Code',
                'The contact will no longer be able to log in until a new code is generated.',
                () => document.getElementById('revoke-form').submit(),
                'btn-danger'
            );
        });
    }

    // Delete
    document.getElementById('btn-delete').addEventListener('click', function () {
        askConfirm(
            'Delete Contact',
            'This will permanently delete {{ addslashes($contact->name) }} and all their data. This cannot be undone.',
            () => document.getElementById('delete-form').submit(),
            'btn-danger'
        );
    });

    // Send via Email
    const btnSendEmail = document.getElementById('btn-send-email');
    if (btnSendEmail && !btnSendEmail.disabled) {
        btnSendEmail.addEventListener('click', function () {
            const contact = this.dataset.contact;
            const url     = this.dataset.url;
            askConfirm(
                'Send Access Code via Email',
                'Send the access code to {{ addslashes($contact->name) }} at ' + contact + '?',
                () => submitPost(url, { channel: 'email' }),
                'btn-primary'
            );
        });
    }

    // Send via SMS
    const btnSendSms = document.getElementById('btn-send-sms');
    if (btnSendSms && !btnSendSms.disabled) {
        btnSendSms.addEventListener('click', function () {
            const contact = this.dataset.contact;
            const url     = this.dataset.url;
            askConfirm(
                'Send Access Code via SMS',
                'Send the access code to {{ addslashes($contact->name) }} at ' + contact + ' via SMS?',
                () => submitPost(url, { channel: 'sms' }),
                'btn-primary'
            );
        });
    }
    // ── Branch change warning ──────────────────────────────────────────────
    const editForm      = document.querySelector('form[action="{{ route('tenant.admin.contacts.update', $contact) }}"]');
    const branchSelect  = editForm.querySelector('select[name="branch_id"]');
    const originalBranch = '{{ $contact->branch_id }}';
    let branchConfirmed  = false;

    editForm.addEventListener('submit', function (e) {
        const newBranch = branchSelect.value;
        if (newBranch && newBranch !== originalBranch && !branchConfirmed) {
            e.preventDefault();
            askConfirm(
                'Change Branch — Confirm Impact',
                '<p class="text-gray-700 mb-2">Moving this contact to a new branch will:</p>' +
                '<ul class="text-gray-800 mb-3" style="padding-left:1.2rem;line-height:1.8">' +
                  '<li><strong>Revoke</strong> their current access code — they will need a new one for the new branch</li>' +
                  '<li><strong>Auto-close</strong> all open issues tied to this contact</li>' +
                  '<li>Add a <strong>public message</strong> on each closed issue explaining the change</li>' +
                  '<li>Record an <strong>activity log entry</strong> on every affected issue</li>' +
                '</ul>' +
                '<p class="text-danger fw-semibold mb-0">This cannot be undone.</p>',
                () => { branchConfirmed = true; editForm.submit(); },
                'btn-danger',
                true
            );
        }
    });
})();
</script>
@endpush

@endsection
