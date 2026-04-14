@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Add Document Category</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.document_categories.index') }}" class="text-muted text-hover-primary">Document Categories</a></li>
                <li class="breadcrumb-item text-gray-900">Add Category</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.document_categories.store') }}">
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
                                           placeholder="e.g. School Policies" required>
                                    <div class="text-muted fs-8 mt-1">A URL-friendly slug will be generated automatically.</div>
                                    @error('name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Parent Category
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <select name="parent_id" class="form-select form-select-solid @error('parent_id') is-invalid @enderror">
                                        <option value="">— None (Root Category) —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('parent_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->full_path }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-muted fs-8 mt-1">Create hierarchical categories (e.g., Policies > Attendance)</div>
                                    @error('parent_id')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Display Order
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <input type="number" name="display_order" value="{{ old('display_order', 0) }}"
                                           class="form-control form-control-solid @error('display_order') is-invalid @enderror"
                                           placeholder="0" min="0">
                                    <div class="text-muted fs-8 mt-1">Lower numbers appear first</div>
                                    @error('display_order')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Description
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <textarea name="description" rows="3"
                                              class="form-control form-control-solid @error('description') is-invalid @enderror"
                                              placeholder="Brief description of this category...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Icon
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <input name="icon" value="{{ old('icon') }}"
                                           class="form-control form-control-solid @error('icon') is-invalid @enderror"
                                           placeholder="e.g. ki-document, ki-folder, ki-file-down">
                                    <div class="text-muted fs-8 mt-1">
                                        Use Keenicons class names. Examples: 
                                        <code>ki-document</code>, <code>ki-folder</code>, <code>ki-book</code>, <code>ki-shield-tick</code>
                                    </div>
                                    @error('icon')
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
                        <a href="{{ route('tenant.admin.document_categories.index') }}" class="btn btn-light">Cancel</a>
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
                            <div class="fw-bold text-gray-800">About Document Categories</div>
                        </div>
                        <ul class="text-muted fs-7 ps-4 mb-0">
                            <li class="mb-2">Categories help organize documents and FAQs (e.g. <em>School Policies</em>, <em>Event Calendars</em>).</li>
                            <li class="mb-2">You can create <strong>nested categories</strong> by setting a parent category.</li>
                            <li>Categories are used for filtering documents and will be shown in the parent portal.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
