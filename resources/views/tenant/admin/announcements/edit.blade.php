@extends('layouts.tenant_admin')
@section('page_title', 'Edit Announcement')
@section('content')

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Announcement</h1>
        </div>
        @endpush

        @include('partials.alerts')

        <div class="card mw-800px mx-auto">
            <div class="card-header border-0 pt-6">
                <div class="card-title fs-3 fw-bold">{{ $announcement->title }}</div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('tenant.admin.announcements.update', $announcement) }}">
                    @csrf @method('PUT')
                    @include('tenant.admin.announcements._form')
                    <div class="d-flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <a href="{{ route('tenant.admin.announcements.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>
@endsection
