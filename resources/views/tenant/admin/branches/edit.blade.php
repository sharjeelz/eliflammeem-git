@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Branch</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.branches.index') }}" class="text-muted text-hover-primary">Branches</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $branch->name }}</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.branches.update', $branch) }}">
                    @csrf @method('PUT')

                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Branch Details</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-md-8">
                                    <label class="form-label required fw-semibold">Branch Name</label>
                                    <input name="name" value="{{ old('name', $branch->name) }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           required>
                                    @error('name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label required fw-semibold">Branch Code</label>
                                    <input name="code" value="{{ old('code', $branch->code) }}"
                                           class="form-control form-control-solid font-monospace @error('code') is-invalid @enderror"
                                           style="text-transform:uppercase" required>
                                    @error('code')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">City</label>
                                    <input name="city" value="{{ old('city', $branch->city) }}"
                                           class="form-control form-control-solid @error('city') is-invalid @enderror"
                                           placeholder="e.g. Karachi">
                                    @error('city')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required fw-semibold">Status</label>
                                    <select name="status" class="form-select form-select-solid @error('status') is-invalid @enderror">
                                        <option value="active"   @selected(old('status', $branch->status) === 'active')>Active</option>
                                        <option value="inactive" @selected(old('status', $branch->status) === 'inactive')>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input name="address" value="{{ old('address', $branch->address) }}"
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
                            Save Changes
                        </button>
                        <a href="{{ route('tenant.admin.branches.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">

                {{-- Meta card --}}
                <div class="card mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex flex-center flex-column mb-4">
                            <div class="symbol symbol-60px symbol-circle mb-3">
                                <div class="symbol-label fw-bold fs-2 bg-light-primary text-primary">
                                    {{ strtoupper(substr($branch->name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-gray-900">{{ $branch->name }}</div>
                            <span class="badge font-monospace badge-light mt-1">{{ $branch->code }}</span>
                        </div>
                        <div class="separator mb-4"></div>
                        @php
                            $meta = [
                                'Status'  => $branch->status === 'active' ? 'Active' : 'Inactive',
                                'City'    => $branch->city ?? '—',
                                'Created' => $branch->created_at->format('d M Y'),
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

                {{-- Danger zone --}}
                <div class="card border border-dashed border-danger">
                    <div class="card-body py-4">
                        <div class="fw-bold text-gray-800 mb-1">Danger Zone</div>
                        <div class="text-muted fs-7 mb-4">Deleting a branch is permanent. Make sure no users or contacts are assigned to it first.</div>
                        <form method="POST" action="{{ route('tenant.admin.branches.destroy', $branch) }}"
                              onsubmit="return confirm('Delete branch {{ addslashes($branch->name) }}? This cannot be undone.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-light-danger w-100">
                                <i class="ki-duotone ki-trash fs-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                Delete Branch
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
