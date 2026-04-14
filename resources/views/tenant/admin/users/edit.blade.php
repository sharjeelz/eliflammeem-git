@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit User</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.users.index') }}" class="text-muted text-hover-primary">Users</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $user->name }}</li>
            </ul>
        </div>
        @endpush

        @php
            $roleColor = ['admin' => 'danger', 'branch_manager' => 'warning', 'staff' => 'primary'][$currentRole] ?? 'secondary';
        @endphp

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.users.update', $user) }}">
                    @csrf @method('PUT')

                    {{-- Account Details --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Account Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Full Name</label>
                                    <input name="name" value="{{ old('name', $user->name) }}"
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
                                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                               class="form-control form-control-solid @error('email') is-invalid @enderror"
                                               required>
                                    </div>
                                    @error('email')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        New Password
                                        <span class="text-muted fw-normal ms-1">(leave blank to keep)</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-lock fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input type="password" name="password"
                                               class="form-control form-control-solid @error('password') is-invalid @enderror"
                                               placeholder="Min. 8 characters">
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
                                        @foreach($roles as $r)
                                            <option value="{{ $r->name }}" @selected(old('role', $currentRole) === $r->name)>
                                                {{ ucwords(str_replace('_', ' ', $r->name)) }}
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
                                        <span class="text-muted fw-normal ms-1 fs-8">(required for branch manager & staff)</span>
                                    </label>
                                    <select name="branch_id"
                                            class="form-select form-select-solid @error('branch_id') is-invalid @enderror">
                                        <option value="">— None —</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" @selected(old('branch_id', $selectedBranchId) == $b->id)>
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

                    {{-- Category Assignments (staff only) --}}
                    <div class="card mb-5" id="categories-card" style="{{ old('role', $currentRole) !== 'staff' ? 'display:none' : '' }}">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Handles Categories</h3>
                            <div class="card-toolbar">
                                <span class="text-muted fs-7">Issues in these categories auto-assign to this staff</span>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($categories->isEmpty())
                                <div class="text-muted fs-7">No categories configured yet.</div>
                            @else
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($categories as $cat)
                                        <label class="d-flex align-items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="category_ids[]"
                                                   value="{{ $cat->id }}"
                                                   class="form-check-input"
                                                   {{ in_array($cat->id, old('category_ids', $selectedCategories)) ? 'checked' : '' }}>
                                            <span class="fs-7 fw-semibold text-gray-700">{{ $cat->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
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
                                        <input name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
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
                                        <input name="address" value="{{ old('address', $user->address) }}"
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
                            Save Changes
                        </button>
                        <a href="{{ route('tenant.admin.users.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Profile card --}}
                <div class="card mb-5">
                    <div class="card-body pt-8 pb-6">

                        {{-- Avatar + name --}}
                        <div class="d-flex flex-center flex-column mb-5">
                            <div class="symbol symbol-80px symbol-circle mb-4">
                                <div class="symbol-label fs-1 fw-bold bg-light-{{ $roleColor }} text-{{ $roleColor }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-gray-900 mb-1">{{ $user->name }}</div>
                            <div class="text-muted fs-7 mb-3">{{ $user->email }}</div>
                            <span class="badge badge-light-{{ $roleColor }} fw-bold fs-7 px-4 py-2">
                                {{ ucwords(str_replace('_', ' ', $currentRole)) }}
                            </span>
                        </div>

                        <div class="separator mb-5"></div>

                        {{-- Meta details --}}
                        @php
                            $details = [
                                'Account ID'   => $user->account_id,
                                'Branch'       => $selectedBranchName ?? '—',
                                'Phone'        => $user->phone_number ?? '—',
                                'Last Login'   => $user->last_login?->format('d M Y, H:i') ?? 'Never',
                                'Last Updated' => $user->updated_at->diffForHumans(),
                                'Member Since' => $user->created_at->format('d M Y'),
                            ];
                        @endphp
                        @foreach($details as $label => $value)
                            <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <span class="text-muted fs-7">{{ $label }}</span>
                                <span class="text-gray-800 fs-7 fw-semibold text-end">{{ $value }}</span>
                            </div>
                        @endforeach

                    </div>
                </div>

                {{-- Issues link --}}
                <div class="card mb-5">
                    <div class="card-body py-4">
                        <div class="text-muted fs-7 mb-3">View all issues assigned to this user.</div>
                        <a href="{{ route('tenant.admin.issues.assigned', $user->id) }}" class="btn btn-light-primary w-100">
                            <i class="ki-duotone ki-document fs-4"><span class="path1"></span><span class="path2"></span></i>
                            View Assigned Issues
                        </a>
                    </div>
                </div>

                {{-- Active Session --}}
                <div class="card mb-5">
                    @php $sessionActive = $user->active_session_id && $user->active_session_id !== 'terminated'; @endphp
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Active Session</h3>
                        <div class="card-toolbar">
                            @if($sessionActive)
                                <span class="badge badge-light-success">Online</span>
                            @elseif($user->active_session_id === 'terminated')
                                <span class="badge badge-light-danger">Terminated</span>
                            @else
                                <span class="badge badge-light-secondary">Offline</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body py-4">
                        @if($sessionActive)
                            <div class="d-flex flex-column gap-2 mb-4">
                                @if($user->last_login)
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fs-7">Signed in</span>
                                        <span class="text-gray-800 fs-7 fw-semibold">{{ $user->last_login->diffForHumans() }}</span>
                                    </div>
                                @endif
                                @if($user->last_login_ip)
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted fs-7">IP Address</span>
                                        <span class="text-gray-800 fs-7 fw-semibold font-monospace">{{ $user->last_login_ip }}</span>
                                    </div>
                                @endif
                                @if($user->last_login_user_agent)
                                    <div>
                                        <span class="text-muted fs-8 d-block">{{ Str::limit($user->last_login_user_agent, 60) }}</span>
                                    </div>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('tenant.admin.users.force-logout', $user) }}"
                                  onsubmit="return confirm('Terminate {{ addslashes($user->name) }}\'s session? They will be signed out immediately on their next request.')">
                                @csrf
                                <button class="btn btn-light-danger w-100">
                                    <i class="ki-duotone ki-exit-right fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    Force Sign Out
                                </button>
                            </form>
                        @elseif($user->active_session_id === 'terminated')
                            <div class="text-warning fs-7">Session was terminated. User will be signed out on next request.</div>
                        @else
                            <div class="text-muted fs-7">This user has no active session.</div>
                        @endif
                    </div>
                </div>

                {{-- Danger zone --}}
                <div class="card border border-dashed border-danger">
                    <div class="card-body py-4">
                        <div class="fw-bold text-gray-800 mb-1">Danger Zone</div>
                        <div class="text-muted fs-7 mb-4">
                            @if($user->trashed())
                                This account is currently <strong>disabled</strong>.
                            @else
                                Disabling removes portal access but keeps all issue history intact.
                            @endif
                        </div>

                        @if($user->trashed())
                            <form method="POST" action="{{ route('tenant.admin.users.enable', $user->id) }}">
                                @csrf
                                <button class="btn btn-light-success w-100">
                                    <i class="ki-duotone ki-check-circle fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Re-enable Account
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('tenant.admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Disable {{ addslashes($user->name) }}? They will lose portal access immediately.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-light-danger w-100">
                                    <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Disable Account
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.querySelector('[name="role"]').addEventListener('change', function () {
    document.getElementById('categories-card').style.display = this.value === 'staff' ? '' : 'none';
});
</script>
@endpush
@endsection
