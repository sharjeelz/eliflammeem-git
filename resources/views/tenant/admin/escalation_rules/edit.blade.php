@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Escalation Rule</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.escalation_rules.index') }}" class="text-muted text-hover-primary">Escalation Rules</a></li>
                <li class="breadcrumb-item text-gray-900">{{ $escalationRule->name }}</li>
            </ul>
        </div>
        @endpush

        <form method="POST" action="{{ route('tenant.admin.escalation_rules.update', $escalationRule) }}">
            @csrf @method('PUT')
            @include('tenant.admin.escalation_rules._form')
        </form>

    </div>
</div>
@endsection
