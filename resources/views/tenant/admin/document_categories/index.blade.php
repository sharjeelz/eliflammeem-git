@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Document Categories</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Document Categories</li>
            </ul>
        </div>
        @endpush

        {{-- Filters --}}
        <form method="GET" action="{{ route('tenant.admin.document_categories.index') }}" class="card card-body mb-5 py-4">
            <div class="d-flex flex-wrap gap-3 align-items-end">

                <div class="flex-grow-1" style="min-width:200px">
                    <label class="form-label fs-7 mb-1">Search</label>
                    <div class="position-relative">
                        <i class="ki-duotone ki-magnifier fs-4 position-absolute ms-3 top-50 translate-middle-y">
                            <span class="path1"></span><span class="path2"></span>
                        </i>
                        <input type="text" name="search" value="{{ request('search') }}"
                               class="form-control form-control-solid ps-10"
                               placeholder="Category name…">
                    </div>
                </div>

                <div class="d-flex gap-2 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('tenant.admin.document_categories.index') }}" class="btn btn-light">Reset</a>
                </div>

            </div>
        </form>

        {{-- Table --}}
        <div class="card">
            <div class="card-header border-0 pt-5 pb-0">
                <div class="card-title">
                    <span class="text-muted fs-7">{{ $categories->total() }} categor{{ $categories->total() === 1 ? 'y' : 'ies' }}</span>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.document_categories.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Category
                    </a>
                </div>
            </div>

            <div class="card-body py-4">
                <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Category</th>
                            <th>Parent</th>
                            <th>Documents</th>
                            <th>FAQs</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold">
                        @forelse($categories as $cat)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if($cat->icon)
                                            <div class="symbol symbol-40px flex-shrink-0">
                                                <div class="symbol-label bg-light-primary">
                                                    <i class="ki-duotone {{ $cat->icon }} fs-2x text-primary">
                                                        <span class="path1"></span><span class="path2"></span>
                                                    </i>
                                                </div>
                                            </div>
                                        @else
                                            <div class="symbol symbol-40px symbol-circle flex-shrink-0">
                                                <div class="symbol-label fw-bold fs-6 bg-light-info text-info">
                                                    {{ strtoupper(substr($cat->name, 0, 1)) }}
                                                </div>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('tenant.admin.document_categories.edit', $cat) }}"
                                               class="text-gray-800 text-hover-primary fw-bold">
                                                {{ $cat->name }}
                                            </a>
                                            @if($cat->description)
                                                <div class="text-muted fs-8">{{ Str::limit($cat->description, 60) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($cat->parent)
                                        <span class="badge badge-light-secondary">{{ $cat->parent->name }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cat->documents_count > 0)
                                        <span class="badge badge-light-primary">{{ $cat->documents_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cat->faqs_count > 0)
                                        <span class="badge badge-light-success">{{ $cat->faqs_count }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('tenant.admin.document_categories.edit', $cat) }}"
                                       class="btn btn-sm btn-light btn-active-light-primary me-1">Edit</a>

                                    <form method="POST" action="{{ route('tenant.admin.document_categories.destroy', $cat) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete category {{ addslashes($cat->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light-danger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-10">
                                    No categories yet.
                                    <a href="{{ route('tenant.admin.document_categories.create') }}">Add one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>{{-- end table-responsive --}}

                <div class="mt-4">
                    {{ $categories->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
