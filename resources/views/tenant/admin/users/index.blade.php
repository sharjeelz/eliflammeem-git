@extends('layouts.tenant_admin')
@section('page_title', 'Users')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Staff Users</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted">User Management</li>
                <li class="breadcrumb-item text-gray-900">Users</li>
            </ul>
        </div>
        @endpush

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.admin.users.index') }}" class="card card-body mb-5 py-4">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                <div class="flex-grow-1" style="min-width:200px">
                    <label class="form-label fs-7 mb-1">Search</label>
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-solid ps-10" placeholder="Name or email…">
                    </div>
                </div>

                <div style="min-width:150px">
                    <label class="form-label fs-7 mb-1">Role</label>
                    <select name="role" class="form-select form-select-solid">
                        <option value="">All roles</option>
                        @foreach($roles as $r)
                            <option value="{{ $r->name }}" @selected(request('role') === $r->name)>
                                {{ ucwords(str_replace('_', ' ', $r->name)) }}
                            </option>
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

                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">Status</label>
                    <select name="status" class="form-select form-select-solid">
                        <option value="">All</option>
                        <option value="active"   @selected(request('status') === 'active')>Active</option>
                        <option value="disabled" @selected(request('status') === 'disabled')>Disabled</option>
                    </select>
                </div>

                <div class="d-flex gap-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.users.index') }}" class="btn btn-light">Reset</a>
                </div>

            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $users->total() }} user{{ $users->total() === 1 ? '' : 's' }}</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.users.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Add User
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>User</th>
                            <th>Branch</th>
                            <th class="text-center">Open Issues</th>
                            <th>Last Login</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($users as $user)
                            @php
                                $role      = $user->getRoleNames()->first() ?? '—';
                                $roleColor = ['admin' => 'danger', 'branch_manager' => 'warning', 'staff' => 'primary'][$role] ?? 'secondary';
                                $branch    = $user->branches->first()?->name ?? '—';
                            @endphp
                            <tr class="{{ $user->trashed() ? 'opacity-50' : '' }}">

                                {{-- User --}}
                                <td class="d-flex align-items-center gap-3">
                                    <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                                        <div class="symbol-label fw-bold fs-6 bg-light-{{ $roleColor }} text-{{ $roleColor }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <a href="{{ route('tenant.admin.users.edit', $user->id) }}"
                                           class="text-gray-800 text-hover-primary fw-bold d-block">
                                            {{ $user->name }}
                                        </a>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="text-muted fs-7">{{ $user->email }}</span>
                                            <span class="badge badge-light-{{ $roleColor }} fs-8">
                                                {{ ucwords(str_replace('_', ' ', $role)) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Branch --}}
                                <td>{{ $branch }}</td>

                                {{-- Open Issues (workload) --}}
                                <td class="text-center">
                                    @if(!$user->trashed())
                                        @php
                                            $count = $user->open_issues_count;
                                            $color = match(true) {
                                                $count === 0          => 'success',
                                                $count <= 3           => 'warning',
                                                $count <= 6           => 'danger',
                                                default               => 'danger',
                                            };
                                        @endphp
                                        @if($count > 0)
                                            <a href="{{ route('tenant.admin.issues.assigned', $user->id) }}"
                                               class="badge badge-light-{{ $color }} fs-7 fw-bold">
                                                {{ $count }}
                                            </a>
                                        @else
                                            <span class="badge badge-light-success fs-7 fw-bold">0</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                {{-- Last Login --}}
                                <td>
                                    @if($user->last_login)
                                        <span title="{{ $user->last_login->format('d M Y H:i') }}">
                                            {{ $user->last_login->diffForHumans() }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-7">Never</span>
                                    @endif
                                </td>

                                {{-- Joined --}}
                                <td>{{ $user->created_at->format('d M Y') }}</td>

                                {{-- Status --}}
                                <td>
                                    @if($user->trashed())
                                        <span class="badge badge-light-danger fw-bold">Disabled</span>
                                    @else
                                        <span class="badge badge-light-success fw-bold">Active</span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.users.edit', $user->id) }}"
                                       class="btn btn-sm btn-light btn-active-light-primary me-2">Edit</a>

                                    @if($user->trashed())
                                        <form method="POST" action="{{ route('tenant.admin.users.enable', $user->id) }}" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-light-success">Enable</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('tenant.admin.users.destroy', $user) }}" class="d-inline"
                                              onsubmit="return confirm('Disable {{ addslashes($user->name) }}? They will lose portal access.')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-light-danger">Disable</button>
                                        </form>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-10">
                                    No users found.
                                    <a href="{{ route('tenant.admin.users.create') }}">Add one now</a>
                                </td>
                            @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
