@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Add Branch</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.branches.index') }}" class="text-muted text-hover-primary">Branches</a></li>
                <li class="breadcrumb-item text-gray-900">Add Branch</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.branches.store') }}">
                    @csrf

                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Branch Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-md-8">
                                    <label class="form-label required fw-semibold">Branch Name</label>
                                    <input name="name" value="{{ old('name') }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           placeholder="e.g. Main Campus" required>
                                    @error('name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required fw-semibold">Branch Code</label>
                                    <input name="code" value="{{ old('code') }}"
                                           class="form-control form-control-solid font-monospace @error('code') is-invalid @enderror"
                                           placeholder="e.g. MAIN" style="text-transform:uppercase" required>
                                    <div class="text-muted fs-8 mt-1">Unique short identifier</div>
                                    @error('code')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">City</label>
                                    <input name="city" value="{{ old('city') }}"
                                           class="form-control form-control-solid @error('city') is-invalid @enderror"
                                           placeholder="e.g. Karachi">
                                    @error('city')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold">Status</label>
                                    <select name="status" class="form-select form-select-solid @error('status') is-invalid @enderror">
                                        <option value="active"   @selected(old('status', 'active') === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input name="address" value="{{ old('address') }}"
                                           class="form-control form-control-solid @error('address') is-invalid @enderror"
                                           placeholder="Street address">
                                    @error('address')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Create Branch
                        </button>
                        <a href="{{ route('tenant.admin.branches.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar tip --}}
            <div class="col-lg-4">
                <div class="card border border-dashed border-primary">
                    <div class="card-body py-5">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="ki-duotone ki-information-5 fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fw-bold text-gray-800">About Branches</div>
                        </div>
                        <ul class="text-muted fs-7 ps-4 mb-0">
                            <li class="mb-2">Each branch has its own <strong>branch manager</strong> who receives new issue assignments automatically.</li>
                            <li class="mb-2">Contacts (parents/teachers) are linked to a branch via their access code.</li>
                            <li class="mb-2">The <strong>branch code</strong> is used during CSV imports to match contacts to branches.</li>
                            <li>Inactive branches are hidden from public portal submission.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
