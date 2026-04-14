@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Edit Template</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.whatsapp.templates.index') }}" class="text-muted text-hover-primary">Templates</a></li>
                <li class="breadcrumb-item text-gray-900">Edit</li>
            </ul>
        </div>
        @endpush

        <form method="POST" action="{{ route('tenant.admin.whatsapp.templates.update', $template) }}">
            @csrf
            @method('PUT')

            <div class="row g-6">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Template Information</h3>
                        </div>
                        <div class="card-body">

                            {{-- General Error Message --}}
                            @error('error')
                                <div class="alert alert-danger d-flex align-items-center mb-5">
                                    <i class="ki-duotone ki-shield-cross fs-2hx text-danger me-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <div class="d-flex flex-column">
                                        <h4 class="mb-1">Error</h4>
                                        <span>{{ $message }}</span>
                                    </div>
                                </div>
                            @enderror

                            {{-- Display Name --}}
                            <div class="mb-5">
                                <label class="form-label required">Display Name</label>
                                <input type="text" name="name" value="{{ old('name', $template->name) }}" 
                                       class="form-control form-control-solid @error('name') is-invalid @enderror"
                                       placeholder="e.g. Welcome Message">
                                <div class="form-text">Friendly name for internal use</div>
                                @error('name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Meta Template Name --}}
                            <div class="mb-5">
                                <label class="form-label required">Meta Template Name</label>
                                <input type="text" name="meta_template_name" value="{{ old('meta_template_name', $template->meta_template_name ?? $template->name) }}" 
                                       class="form-control form-control-solid @error('meta_template_name') is-invalid @enderror"
                                       placeholder="e.g. welcome_message">
                                <div class="form-text">The exact template name from your Meta Business Manager</div>
                                @error('meta_template_name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Language & Category --}}
                            <div class="row g-5 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label required">Language</label>
                                    <select name="language" class="form-select form-select-solid @error('language') is-invalid @enderror">
                                        <option value="en" {{ old('language', $template->language) === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ old('language', $template->language) === 'ar' ? 'selected' : '' }}>Arabic</option>
                                        <option value="es" {{ old('language', $template->language) === 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ old('language', $template->language) === 'fr' ? 'selected' : '' }}>French</option>
                                        <option value="pt" {{ old('language', $template->language) === 'pt' ? 'selected' : '' }}>Portuguese</option>
                                        <option value="de" {{ old('language', $template->language) === 'de' ? 'selected' : '' }}>German</option>
                                    </select>
                                    @error('language')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Category</label>
                                    <select name="category" class="form-select form-select-solid @error('category') is-invalid @enderror">
                                        <option value="UTILITY" {{ old('category', $template->category) === 'UTILITY' ? 'selected' : '' }}>Utility</option>
                                        <option value="MARKETING" {{ old('category', $template->category) === 'MARKETING' ? 'selected' : '' }}>Marketing</option>
                                        <option value="AUTHENTICATION" {{ old('category', $template->category) === 'AUTHENTICATION' ? 'selected' : '' }}>Authentication</option>
                                    </select>
                                    @error('category')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-5">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" rows="3" 
                                          class="form-control form-control-solid @error('description') is-invalid @enderror"
                                          placeholder="Brief description of what this template is used for">{{ old('description', $template->description) }}</textarea>
                                @error('description')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="separator my-6"></div>

                            <h4 class="mb-4">Template Variables</h4>

                            @php
                                $paramNames = old('parameter_names');
                                if (!$paramNames && $template->parameters) {
                                    $paramNames = collect($template->parameters)->pluck('name')->implode(', ');
                                }
                            @endphp

                            {{-- Parameter Names --}}
                            <div class="mb-5">
                                <label class="form-label">Parameter Names (Optional)</label>
                                <input type="text" name="parameter_names" value="{{ $paramNames }}" 
                                       class="form-control form-control-solid @error('parameter_names') is-invalid @enderror"
                                       placeholder="e.g. student_name, amount, due_date">
                                <div class="form-text">
                                    Friendly names for template variables {{1}}, {{2}}, {{3}}, etc. (comma-separated)
                                </div>
                                @error('parameter_names')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="separator my-6"></div>

                            {{-- Active Status --}}
                            <div class="mb-5">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                           {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label text-gray-700" for="is_active">
                                        Template is active and can be used
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary">Update Template</button>
                        <a href="{{ route('tenant.admin.whatsapp.templates.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            
                            <div class="mb-4">
                                <span class="text-muted fs-7">Current Status:</span>
                                @if($template->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($template->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($template->status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </div>

                            @if($template->rejection_reason)
                            <div class="alert alert-danger">
                                <strong>Rejection Reason:</strong><br>
                                {{ $template->rejection_reason }}
                            </div>
                            @endif

                            <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-4 mt-4">
                                <i class="ki-duotone ki-information-5 fs-2tx text-info me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-semibold">
                                        <div class="fs-7 text-gray-700">
                                            You can update the display name, description, and parameters here. 
                                            To change the actual template content, edit it in Meta Business Manager.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
