@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Branches</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Branches</li>
            </ul>
        </div>
        @endpush

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.admin.branches.index') }}" class="card card-body mb-5 py-4">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                <div class="flex-grow-1" style="min-width:200px">
                    <label class="form-label fs-7 mb-1">Search</label>
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-solid ps-10"
                               placeholder="Name, code or city…">
                    </div>
                </div>

                <div style="min-width:140px">
                    <label class="form-label fs-7 mb-1">Status</label>
                    <select name="status" class="form-select form-select-solid">
                        <option value="">All</option>
                        <option value="active"   @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="d-flex gap-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.branches.index') }}" class="btn btn-light">Reset</a>
                </div>

            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $branches->total() }} branch{{ $branches->total() === 1 ? '' : 'es' }}</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.branches.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Branch
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Branch</th>
                            <th>Code</th>
                            <th>City</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($branches as $branch)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                                            <div class="symbol-label fw-bold fs-6 bg-light-primary text-primary">
                                                {{ strtoupper(substr($branch->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div>
                                            <a href="{{ route('tenant.admin.branches.edit', $branch) }}"
                                               class="text-gray-800 text-hover-primary fw-bold d-block">
                                                {{ $branch->name }}
                                            </a>
                                            @if($branch->address)
                                                <span class="text-muted fs-8">{{ $branch->address }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-light font-monospace">{{ $branch->code }}</span></td>
                                <td>{{ $branch->city ?? '—' }}</td>
                                <td>
                                    @if($branch->status === 'active')
                                        <span class="badge badge-light-success fw-bold">Active</span>
                                    @else
                                        <span class="badge badge-light-danger fw-bold">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.branches.edit', $branch) }}"
                                       class="btn btn-sm btn-light btn-active-light-primary me-1">Edit</a>

                                    <form method="POST" action="{{ route('tenant.admin.branches.toggle_status', $branch) }}"
                                          class="d-inline">
                                        @csrf
                                        @if($branch->status === 'active')
                                            <button class="btn btn-sm btn-light-warning me-1"
                                                    onclick="return confirm('Deactivate branch \'{{ addslashes($branch->name) }}\'? Staff will no longer appear in assignment dropdowns and new issues cannot be submitted to it.')">
                                                Deactivate
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-light-success me-1">
                                                Activate
                                            </button>
                                        @endif
                                    </form>

                                    <form method="POST" action="{{ route('tenant.admin.branches.destroy', $branch) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete branch {{ addslashes($branch->name) }}? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">
                                    No branches yet.
                                    <a href="{{ route('tenant.admin.branches.create') }}">Add one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $branches->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
