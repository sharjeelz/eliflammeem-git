@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Add Category</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.categories.index') }}" class="text-muted text-hover-primary">Categories</a></li>
                <li class="breadcrumb-item text-gray-900">Add Category</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.categories.store') }}">
                    @csrf

                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Category Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Category Name</label>
                                    <input name="name" value="{{ old('name') }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           placeholder="e.g. Academic Complaint" required>
                                    <div class="text-muted fs-8 mt-1">A URL-friendly slug will be generated automatically.</div>
                                    @error('name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Default SLA (hours)
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" name="default_sla_hours" value="{{ old('default_sla_hours') }}"
                                               class="form-control form-control-solid @error('default_sla_hours') is-invalid @enderror"
                                               placeholder="e.g. 48" min="1" max="8760">
                                        <span class="input-group-text border-0 bg-light text-muted">hrs</span>
                                    </div>
                                    <div class="text-muted fs-8 mt-1">How long staff have to resolve issues in this category.</div>
                                    @error('default_sla_hours')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Create Category
                        </button>
                        <a href="{{ route('tenant.admin.categories.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card border border-dashed border-info">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="ki-duotone ki-information-5 fs-2x text-info">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fw-bold text-gray-800">About Categories</div>
                        </div>
                        <ul class="text-muted fs-7 ps-4 mb-0">
                            <li class="mb-2">Categories help classify issues (e.g. <em>Academic Complaint</em>, <em>Fee Issue</em>, <em>Facilities</em>).</li>
                            <li class="mb-2">The <strong>SLA hours</strong> sets the resolution deadline automatically when an issue of this type is submitted.</li>
                            <li>Contacts select a category when submitting an issue from the public portal.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
