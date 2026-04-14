@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Add Contact</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.index') }}" class="text-muted text-hover-primary">Contacts</a></li>
                <li class="breadcrumb-item text-gray-900">Add Contact</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Form --}}
            <div class="col-lg-8">
                <form method="POST" action="{{ route('tenant.admin.contacts.store') }}">
                    @csrf

                    {{-- Basic Info --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-md-7">
                                    <label class="form-label required fw-semibold">Full Name</label>
                                    <input name="name" value="{{ old('name') }}"
                                           class="form-control form-control-solid @error('name') is-invalid @enderror"
                                           placeholder="e.g. Jane Smith" required>
                                    @error('name')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label required fw-semibold">Role</label>
                                    <select name="role"
                                            class="form-select form-select-solid @error('role') is-invalid @enderror"
                                            required>
                                        <option value="">— Select role —</option>
                                        @foreach(['parent','teacher'] as $r)
                                            <option value="{{ $r }}" @selected(old('role') === $r)>
                                                {{ ucfirst($r) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-semibold">Branch</label>
                                    <select name="branch_id" class="form-select form-select-solid @error('branch_id') is-invalid @enderror">
                                        <option value="">— None —</option>
                                        @foreach($branches as $b)
                                            <option value="{{ $b->id }}" @selected(old('branch_id') == $b->id)>
                                                {{ $b->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Contact Info --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">Contact Details</h3>
                            <div class="card-toolbar">
                                <span class="text-muted fs-7">Used to send access codes</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row g-5">

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address <span class="text-muted fw-normal fs-8">(at least one of email or phone required)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-sms fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input type="email" name="email" value="{{ old('email') }}"
                                               class="form-control form-control-solid @error('email') is-invalid @enderror"
                                               placeholder="parent@example.com">
                                    </div>
                                    @error('email')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number <span class="text-muted fw-normal fs-8">(at least one of email or phone required)</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text border-0 bg-light">
                                            <i class="ki-duotone ki-phone fs-4 text-gray-500">
                                                <span class="path1"></span><span class="path2"></span>
                                            </i>
                                        </span>
                                        <input name="phone" value="{{ old('phone') }}"
                                               class="form-control form-control-solid @error('phone') is-invalid @enderror"
                                               placeholder="+1 555 000 0000">
                                    </div>
                                    @error('phone')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">
                                        External ID
                                        <span class="text-muted fw-normal ms-1">(optional)</span>
                                    </label>
                                    <input name="external_id" value="{{ old('external_id') }}"
                                           class="form-control form-control-solid @error('external_id') is-invalid @enderror"
                                           placeholder="e.g. STU-001">
                                    @error('external_id')
                                        <div class="text-danger fs-7 mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="text-muted fs-8 mt-1">Your internal student or staff ID for reference.</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Create Contact
                        </button>
                        <a href="{{ route('tenant.admin.contacts.index') }}" class="btn btn-light">Cancel</a>
                    </div>

                </form>
            </div>

            {{-- Info sidebar --}}
            <div class="col-lg-4">

                {{-- Roles guide --}}
                <div class="card mb-5">
                    <div class="card-header">
                        <h3 class="card-title fw-bold">Role Guide</h3>
                    </div>
                    <div class="card-body py-4">
                        <div class="d-flex align-items-start gap-3 mb-5">
                            <div class="symbol symbol-40px flex-shrink-0">
                                <div class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-people fs-3 text-primary">
                                        <span class="path1"></span><span class="path2"></span>
                                        <span class="path3"></span><span class="path4"></span><span class="path5"></span>
                                    </i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-800 mb-1">Parent</div>
                                <div class="text-muted fs-7">A student's guardian. Can submit issues and track their status via the public portal.</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-start gap-3">
                            <div class="symbol symbol-40px flex-shrink-0">
                                <div class="symbol-label bg-light-success">
                                    <i class="ki-duotone ki-teacher fs-3 text-success">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-gray-800 mb-1">Teacher</div>
                                <div class="text-muted fs-7">A school staff member who can report issues on behalf of students or classrooms.</div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Access code tip --}}
                <div class="card border border-dashed border-primary">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <i class="ki-duotone ki-information-5 fs-2x text-primary">
                                <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                            </i>
                            <div class="fw-bold text-gray-800">About Access Codes</div>
                        </div>
                        <div class="text-muted fs-7 lh-lg">
                            After creating the contact, generate an access code from their profile.
                            The code can be sent via <strong>email</strong> or <strong>SMS</strong> so they can log into the public portal.
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
