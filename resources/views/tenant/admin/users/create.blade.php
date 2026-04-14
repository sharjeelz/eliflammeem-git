@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Add Staff User</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.users.index') }}" class="text-muted text-hover-primary">Users</a></li>
                <li class="breadcrumb-item text-gray-900">Add User</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.users.store') }}">
                    @csrf

                    {{-- Account Details --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Account Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Full Name</label>
                                    <input name="name" value="{{ old('name') }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           placeholder="e.g. John Smith" required>
                                    @error('name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-7">
                                    <label class="form-label required fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-sms fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                               class="form-control form-control-solid @error('email') is-invalid @enderror"
                                               placeholder="staff@school.edu" required>
                                    </div>
                                    @error('email')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label required fw-semibold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-lock fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input type="password" name="password"
                                               class="form-control form-control-solid @error('password') is-invalid @enderror"
                                               placeholder="Min. 8 characters" required>
                                    </div>
                                    @error('password')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold">Role</label>
                                    <select name="role"
                                            class="form-select form-select-solid @error('role') is-invalid @enderror"
                                            required>
                                        <option value="">— Select role —</option>
                                        @foreach($roles as $r)
                                            <option value="{{ $r }}" @selected(old('role') === $r)>
                                                {{ ucwords(str_replace('_', ' ', $r)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Branch
                                        <span class="text-danger ms-1">*</span>
                                        <span class="text-muted fw-normal ms-1 fs-8">(required for branch manager & staff)</span>
                                    </label>
                                    <select id="branch_select" name="branch_id"
                                            class="form-select form-select-solid @error('branch_id') is-invalid @enderror">
                                        <option value="">— None —</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}"
                                                    data-taken="{{ in_array($b->id, $takenBranchIds) ? '1' : '0' }}"
                                                    @selected(old('branch_id') == $b->id)>
                                                {{ $b->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Profile Info --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Profile Info</h3>
                            <div class="card-toolbar">
                                <span class="text-muted fs-7">Optional</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-phone fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input name="phone_number" value="{{ old('phone_number') }}"
                                               class="form-control form-control-solid @error('phone_number') is-invalid @enderror"
                                               placeholder="+1 555 000 0000">
                                    </div>
                                    @error('phone_number')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-geolocation fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input name="address" value="{{ old('address') }}"
                                               class="form-control form-control-solid @error('address') is-invalid @enderror"
                                               placeholder="Street, City">
                                    </div>
                                    @error('address')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Create User
                        </button>
                        <a href="{{ route('tenant.admin.users.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Role guide --}}
                <div class="card mb-5">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Role Guide</h3>
                    </div>
                    <div class="card-body py-4">

                        <div class="d-flex align-items-start gap-3 mb-5">
                            <div class="symbol symbol-40px flex-shrink-0">
                                <div class="symbol-label bg-light-danger">
                                    <i class="ki-duotone ki-shield-tick fs-3 text-danger">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-800 mb-1">Admin</div>
                                <div class="text-muted fs-7">Full access to all issues, users, and contacts across the school. No branch required.</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-5">
                            <div class="symbol symbol-40px flex-shrink-0">
                                <div class="symbol-label bg-light-warning">
                                    <i class="ki-duotone ki-office-bag fs-3 text-warning">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-800 mb-1">Branch Manager</div>
                                <div class="text-muted fs-7">Sees all issues in their branch. Can assign issues to staff within their branch. <strong>Branch required.</strong></div>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="symbol symbol-40px flex-shrink-0">
                                <div class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-people fs-3 text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span><span class="path3"></span>
                                        <span class="path4"></span><span class="path5"></span>
                                    </i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-800 mb-1">Staff</div>
                                <div class="text-muted fs-7">Sees only issues assigned to them. Can update status and add comments. <strong>Branch required.</strong></div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Login tip --}}
                <div class="card border border-dashed border-primary">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="ki-duotone ki-information-5 fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fw-bold text-gray-800">Login Access</div>
                        </div>
                        <div class="text-muted fs-7 lh-lg">
                            The user will log in at
                            <strong>{{ request()->getHost() }}/admin/login</strong>
                            using their email and the password you set here.
                            They can change their password after logging in.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var roleSelect   = document.querySelector('select[name="role"]');
    var branchSelect = document.getElementById('branch_select');
    if (!roleSelect || !branchSelect) return;

    function applyBranchFilter() {
        var isBranchManager = roleSelect.value === 'branch_manager';
        Array.from(branchSelect.options).forEach(function (opt) {
            if (!opt.value) return; // skip "— None —"
            var taken = opt.dataset.taken === '1';
            if (isBranchManager && taken) {
                opt.disabled = true;
                if (!opt.dataset.originalText) opt.dataset.originalText = opt.textContent;
                opt.textContent = opt.dataset.originalText + ' (has manager)';
                // deselect if currently selected
                if (opt.selected) { branchSelect.value = ''; }
            } else {
                opt.disabled = false;
                if (opt.dataset.originalText) opt.textContent = opt.dataset.originalText;
            }
        });
    }

    roleSelect.addEventListener('change', applyBranchFilter);
    // Run on page load in case old() value restores branch_manager
    applyBranchFilter();
})();
</script>
@endpush

@endsection
