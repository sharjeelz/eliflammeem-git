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
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.categories.index') }}" class="text-muted text-hover-primary">Categories</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $category->name }}</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.categories.update', $category) }}">
                    @csrf @method('PUT')

                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Category Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-12">
                                    <label class="form-label required fw-semibold">Category Name</label>
                                    <input name="name" value="{{ old('name', $category->name) }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           required>
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
                                        <input type="number" name="default_sla_hours"
                                               value="{{ old('default_sla_hours', $category->default_sla_hours) }}"
                                               class="form-control form-control-solid @error('default_sla_hours') is-invalid @enderror"
                                               placeholder="e.g. 48" min="1" max="8760">
                                        <span class="input-group-text border-0 bg-light text-muted">hrs</span>
                                    </div>
                                    @error('default_sla_hours')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Slug</label>
                                    <input value="{{ $category->slug }}" class="form-control form-control-solid font-monospace bg-light" readonly>
                                    <div class="text-muted fs-8 mt-1">Auto-updated if name changes.</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Save Changes
                        </button>
                        <a href="{{ route('tenant.admin.categories.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                <div class="card mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex flex-center flex-column mb-4">
                            <div class="symbol symbol-60px symbol-circle mb-3">
                                <div class="symbol-label fw-bold fs-2 bg-light-info text-info">
                                    {{ strtoupper(substr($category->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-gray-900">{{ $category->name }}</div>
                            <code class="text-muted fs-8 mt-1">{{ $category->slug }}</code>
                        </div>
                        <div class="separator mb-4"></div>
                        @php
                            $meta = [
                                'SLA'     => $category->default_sla_hours ? $category->default_sla_hours.'h' : '—',
                                'Created' => $category->created_at->format('d M Y'),
                            ];
                        @endphp
                        @foreach($meta as $label => $value)
                            <div class="d-flex justify-content-between py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                                <span class="text-muted fs-7">{{ $label }}</span>
                                <span class="text-gray-800 fs-7 fw-semibold">{{ $value }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card border border-dashed border-danger">
                    <div class="card-body py-4">
                        <div class="fw-bold text-gray-800 mb-1">Danger Zone</div>
                        <div class="text-muted fs-7 mb-4">Deleting a category will remove it from all future issue submissions. Existing issues keep their category label.</div>
                        <form method="POST" action="{{ route('tenant.admin.categories.destroy', $category) }}"
                              onsubmit="return confirm('Delete category {{ addslashes($category->name) }}?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light-danger w-100">
                                <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                Delete Category
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
