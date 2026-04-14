@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Auto-assign Rules</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Auto-assign Rules</li>
            </ul>
        </div>
        @endpush

        {{-- Info banner --}}
        <div class="alert alert-dismissible bg-light-info d-flex align-items-center p-5 mb-7 border border-info border-dashed rounded">
            <i class="ki-duotone ki-information-5 fs-2tx text-info me-4">
                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
            </i>
            <div class="d-flex flex-column">
                <span class="fw-bold fs-6 text-gray-700">How auto-assignment works</span>
                <span class="fs-7 text-muted">
                    When a new issue arrives for a branch and category, the system finds the first
                    <strong>Staff</strong> member in that branch who handles that category.
                    If none is found, it falls back to the <strong>Branch Manager</strong>.
                    Configure assignments via <a href="{{ route('tenant.admin.users.index') }}" class="fw-semibold text-info">Users → Edit User</a>.
                </span>
            </div>
        </div>

        @if($matrix->isEmpty())
            <div class="card">
                <div class="card-body text-center py-15">
                    <i class="ki-duotone ki-geolocation fs-3x text-muted mb-4">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <p class="text-muted fs-6">No branches have been created yet.</p>
                    <a href="{{ route('tenant.admin.branches.create') }}" class="btn btn-primary btn-sm mt-2">Add Branch</a>
                </div>
            </div>
        @else
            @foreach($matrix as $row)
            @php
                $hasAnyRule    = $row['rules']->contains(fn($r) => $r['assignee'] !== null);
                $branchInitial = strtoupper(substr($row['branch']->name, 0, 1));
            @endphp

            <div class="card mb-6">
                {{-- Branch header --}}
                <div class="card-header border-0 pt-5 pb-0">
                    <div class="card-title d-flex align-items-center gap-3">
                        <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                            <div class="symbol-label fw-bold fs-6 bg-light-primary text-primary">
                                {{ $branchInitial }}
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-900 fw-bold fs-5">{{ $row['branch']->name }}</span>
                            @if($row['branch']->city)
                                <span class="text-muted fs-7 ms-2">{{ $row['branch']->city }}</span>
                            @endif
                        </div>
                    </div>

                    {{-- Fallback badge --}}
                    <div class="card-toolbar">
                        @if($row['manager'])
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fs-7">Fallback:</span>
                                <span class="badge badge-light-warning fw-semibold fs-7">
                                    <i class="ki-duotone ki-user fs-7 me-1"><span class="path1"></span><span class="path2"></span></i>
                                    {{ $row['manager']->name }}
                                    <span class="text-muted ms-1">(Branch Manager)</span>
                                </span>
                            </div>
                        @else
                            <span class="badge badge-light-danger fw-semibold fs-7">
                                No branch manager — unmatched issues will be unassigned
                            </span>
                        @endif
                    </div>
                </div>

                <div class="card-body py-4">

                    @if($categories->isEmpty())
                        <div class="text-center text-muted py-6 fs-7">
                            No issue categories configured.
                            <a href="{{ route('tenant.admin.categories.create') }}">Add one</a>
                        </div>
                    @else
                        <div class="table-responsive">
                        <table class="table align-middle table-row-dashed fs-6 gy-4">
                            <thead>
                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                    <th style="width:40%">Category</th>
                                    <th>Assigned Staff</th>
                                    <th>Will receive issues?</th>
                                </tr>
                            </thead>
                            <tbody class="text-gray-600 fw-semibold">
                                @foreach($row['rules'] as $rule)
                                <tr>
                                    <td>
                                        <span class="text-gray-800 fw-semibold">{{ $rule['category']->name }}</span>
                                    </td>
                                    <td>
                                        @if($rule['assignee'])
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="symbol symbol-30px symbol-circle">
                                                    <div class="symbol-label fw-semibold fs-8 bg-light-success text-success">
                                                        {{ strtoupper(substr($rule['assignee']->name, 0, 1)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <a href="{{ route('tenant.admin.users.edit', $rule['assignee']) }}"
                                                       class="text-gray-800 text-hover-primary fw-semibold fs-7">
                                                        {{ $rule['assignee']->name }}
                                                    </a>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted fs-7">— no staff assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rule['assignee'])
                                            <span class="badge badge-light-success">Yes — direct assign</span>
                                        @elseif($row['manager'])
                                            <span class="badge badge-light-warning">Yes — via branch manager</span>
                                        @else
                                            <span class="badge badge-light-danger">No — will be unassigned</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        </div>{{-- end table-responsive --}}
                    @endif

                    @if($row['staffWithoutCategories']->isNotEmpty())
                        <div class="mt-4 pt-4 border-top border-dashed">
                            <p class="text-muted fs-7 mb-2 fw-semibold text-uppercase">
                                Staff in this branch with no categories (manual assign only)
                            </p>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($row['staffWithoutCategories'] as $s)
                                    <a href="{{ route('tenant.admin.users.edit', $s) }}"
                                       class="badge badge-light text-gray-700 text-hover-primary fw-semibold fs-7">
                                        {{ $s->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            @endforeach
        @endif

    </div>
</div>
@endsection
