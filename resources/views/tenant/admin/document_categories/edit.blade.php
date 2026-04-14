@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Category</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.document_categories.index') }}" class="text-muted text-hover-primary">Document Categories</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $documentCategory->name }}</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.document_categories.update', $documentCategory) }}">
                    @csrf
                    @method('PUT')

                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Category Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Category Name</label>
                                    <input name="name" value="{{ old('name', $documentCategory->name) }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           placeholder="e.g. School Policies" required>
                                    <div class="text-muted fs-8 mt-1">Current slug: <code>{{ $documentCategory->slug }}</code></div>
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
                                            <option value="{{ $cat->id }}" {{ old('parent_id', $documentCategory->parent_id) == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->full_path }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="text-muted fs-8 mt-1">Create hierarchical categories</div>
                                    @error('parent_id')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">
                                        Display Order
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <input type="number" name="display_order" value="{{ old('display_order', $documentCategory->display_order) }}"
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
                                              placeholder="Brief description of this category...">{{ old('description', $documentCategory->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">
                                        Icon
                                        <span class="text-muted fw-normal ms-1 fs-8">(optional)</span>
                                    </label>
                                    <input name="icon" value="{{ old('icon', $documentCategory->icon) }}"
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
                            Update Category
                        </button>
                        <a href="{{ route('tenant.admin.document_categories.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                <div class="card border border-dashed border-primary mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="ki-duotone ki-chart-simple fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span>
                            </i>
                            <div class="fw-bold text-gray-800">Category Stats</div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Documents:</span>
                            <span class="fw-bold">{{ $documentCategory->documents()->count() }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">FAQs:</span>
                            <span class="fw-bold">{{ $documentCategory->faqs()->count() }}</span>
                        </div>
                        @if($documentCategory->children()->count() > 0)
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Child Categories:</span>
                                <span class="fw-bold">{{ $documentCategory->children()->count() }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card border border-dashed border-warning">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="ki-duotone ki-information-5 fs-2x text-warning">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fw-bold text-gray-800">Deleting this Category</div>
                        </div>
                        <p class="text-muted fs-7 mb-0">
                            If you delete this category, all documents and FAQs will have their category set to "None". 
                            The items themselves will NOT be deleted.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
