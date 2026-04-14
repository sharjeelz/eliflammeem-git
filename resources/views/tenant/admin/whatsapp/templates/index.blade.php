@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">WhatsApp Templates</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.settings.edit') }}" class="text-muted text-hover-primary">Settings</a></li>
                <li class="breadcrumb-item text-gray-900">WhatsApp Templates</li>
            </ul>
        </div>
        @endpush

        <div class="card">
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4"><span class="path1"></span><span class="path2"></span></i>
                        <input type="text" id="search-box" class="form-control form-control-solid w-250px ps-12" placeholder="Search templates...">
                    </div>
                </div>
                <div class="card-toolbar">
                    <a href="{{ route('tenant.admin.whatsapp.templates.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        New Template
                    </a>
                </div>
            </div>

            <div class="card-body pt-0">

                @if($templates->isEmpty())
                    <div class="text-center py-10">
                        <i class="ki-duotone ki-file-deleted fs-5x text-muted mb-5"><span class="path1"></span><span class="path2"></span></i>
                        <p class="text-gray-600 fs-5 fw-semibold mb-5">No templates found</p>
                        <p class="text-muted fs-7 mb-5">Create your first WhatsApp message template to get started.</p>
                        <a href="{{ route('tenant.admin.whatsapp.templates.create') }}" class="btn btn-primary">
                            <i class="ki-duotone ki-plus fs-2"></i>
                            Create Template
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="templates-table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-200px">Template Name</th>
                                <th class="min-w-100px">Language</th>
                                <th class="min-w-100px">Category</th>
                                <th class="min-w-100px">Status</th>
                                <th class="min-w-100px">Created</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @foreach($templates as $template)
                            <tr>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-bold">{{ $template->name }}</span>
                                        <span class="text-muted fs-7">{{ $template->meta_template_name ?? $template->name }}</span>
                                        @if($template->description)
                                            <span class="text-gray-600 fs-8 mt-1">{{ Str::limit($template->description, 60) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-light-info">{{ strtoupper($template->language) }}</span>
                                </td>
                                <td>
                                    <span class="badge badge-light-primary">{{ $template->category }}</span>
                                </td>
                                <td>
                                    @if($template->status === 'approved')
                                        <span class="badge badge-light-success">Approved</span>
                                    @elseif($template->status === 'pending')
                                        <span class="badge badge-light-warning">Pending</span>
                                    @elseif($template->status === 'rejected')
                                        <span class="badge badge-light-danger" data-bs-toggle="tooltip" title="{{ $template->rejection_reason }}">Rejected</span>
                                    @else
                                        <span class="badge badge-light-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted">{{ $template->created_at->format('M d, Y') }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                        Actions
                                        <i class="ki-duotone ki-down fs-5 m-0"></i>
                                    </a>
                                    <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                        <div class="menu-item px-3">
                                            <a href="{{ route('tenant.admin.whatsapp.templates.edit', $template) }}" class="menu-link px-3">Edit</a>
                                        </div>
                                        <div class="menu-item px-3">
                                            <a href="#" class="menu-link px-3 text-danger" onclick="event.preventDefault(); if(confirm('Delete this template?')) document.getElementById('delete-form-{{ $template->id }}').submit();">
                                                Delete
                                            </a>
                                            <form id="delete-form-{{ $template->id }}" action="{{ route('tenant.admin.whatsapp.templates.destroy', $template) }}" method="POST" class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>{{-- end table-responsive --}}

                    <div class="d-flex justify-content-end mt-5">
                        {{ $templates->links() }}
                    </div>
                @endif

            </div>
        </div>

        {{-- Info Box --}}
        <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mt-6 p-6">
            <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <div class="d-flex flex-stack flex-grow-1">
                <div class="fw-semibold">
                    <h4 class="text-gray-900 fw-bold">About WhatsApp Templates</h4>
                    <div class="fs-6 text-gray-700">
                        WhatsApp Business API requires pre-approved message templates for sending messages. 
                        Templates must be submitted to Meta for review before they can be used. 
                        Approval typically takes 24-48 hours.
                        <br><br>
                        <strong>Template Guidelines:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Template names must be lowercase with underscores (e.g., welcome_message)</li>
                            <li>Body text can include variables using {{1}}, {{2}}, etc.</li>
                            <li>Maximum 3 buttons per template</li>
                            <li>Header is optional but recommended</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
// Simple search filter
document.getElementById('search-box')?.addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#templates-table tbody tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>
@endpush
@endsection
