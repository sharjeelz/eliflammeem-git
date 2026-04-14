@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Import WhatsApp Template</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.whatsapp.templates.index') }}" class="text-muted text-hover-primary">Templates</a></li>
                <li class="breadcrumb-item text-gray-900">Import</li>
            </ul>
        </div>
        @endpush

        <form method="POST" action="{{ route('tenant.admin.whatsapp.templates.store') }}">
            @csrf

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
                                <input type="text" name="name" value="{{ old('name') }}" 
                                       class="form-control form-control-solid @error('name') is-invalid @enderror"
                                       placeholder="e.g. Welcome Message">
                                <div class="form-text">Friendly name for internal use</div>
                                @error('name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Meta Template Name --}}
                            <div class="mb-5">
                                <label class="form-label required">Meta Template Name</label>
                                <input type="text" name="meta_template_name" value="{{ old('meta_template_name') }}" 
                                       class="form-control form-control-solid @error('meta_template_name') is-invalid @enderror"
                                       placeholder="e.g. welcome_message">
                                <div class="form-text">The exact template name from your Meta Business Manager (lowercase with underscores)</div>
                                @error('meta_template_name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Language & Category --}}
                            <div class="row g-5 mb-5">
                                <div class="col-md-6">
                                    <label class="form-label required">Language</label>
                                    <select name="language" class="form-select form-select-solid @error('language') is-invalid @enderror">
                                        <option value="en" {{ old('language', 'en') === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ old('language') === 'ar' ? 'selected' : '' }}>Arabic</option>
                                        <option value="es" {{ old('language') === 'es' ? 'selected' : '' }}>Spanish</option>
                                        <option value="fr" {{ old('language') === 'fr' ? 'selected' : '' }}>French</option>
                                        <option value="pt" {{ old('language') === 'pt' ? 'selected' : '' }}>Portuguese</option>
                                        <option value="de" {{ old('language') === 'de' ? 'selected' : '' }}>German</option>
                                    </select>
                                    @error('language')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label required">Category</label>
                                    <select name="category" class="form-select form-select-solid @error('category') is-invalid @enderror">
                                        <option value="UTILITY" {{ old('category', 'UTILITY') === 'UTILITY' ? 'selected' : '' }}>Utility</option>
                                        <option value="MARKETING" {{ old('category') === 'MARKETING' ? 'selected' : '' }}>Marketing</option>
                                        <option value="AUTHENTICATION" {{ old('category') === 'AUTHENTICATION' ? 'selected' : '' }}>Authentication</option>
                                    </select>
                                    @error('category')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mb-5">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" rows="3" 
                                          class="form-control form-control-solid @error('description') is-invalid @enderror"
                                          placeholder="Brief description of what this template is used for">{{ old('description') }}</textarea>
                                @error('description')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            <div class="separator my-6"></div>

                            <h4 class="mb-4">Template Variables</h4>

                            {{-- Parameter Names --}}
                            <div class="mb-5">
                                <label class="form-label">Parameter Names (Optional)</label>
                                <input type="text" name="parameter_names" value="{{ old('parameter_names') }}" 
                                       class="form-control form-control-solid @error('parameter_names') is-invalid @enderror"
                                       placeholder="e.g. student_name, amount, due_date">
                                <div class="form-text">
                                    If your template has variables like {{1}}, {{2}}, {{3}}, enter friendly names separated by commas.
                                    This helps you remember what each variable represents when sending messages.
                                </div>
                                @error('parameter_names')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                        </div>
                    </div>

                    <div class="d-flex gap-3 mt-6">
                        <button type="submit" class="btn btn-primary">Import Template</button>
                        <a href="{{ route('tenant.admin.whatsapp.templates.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card bg-light">
                        <div class="card-header">
                            <h3 class="card-title">How to Import Templates</h3>
                        </div>
                        <div class="card-body">
                            
                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-4 mb-4">
                                <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                <div class="d-flex flex-stack flex-grow-1">
                                    <div class="fw-semibold">
                                        <div class="fs-7 text-gray-700">
                                            Import templates that are already created and approved in your Meta Business Manager.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="mb-3">Steps:</h5>
                            <ol class="text-gray-700 fs-7 mb-4 ps-4">
                                <li class="mb-3">Log into your <strong>Meta Business Manager</strong></li>
                                <li class="mb-3">Go to <strong>WhatsApp Manager → Message Templates</strong></li>
                                <li class="mb-3">Find your approved template</li>
                                <li class="mb-3">Copy the template name (e.g., <code>payment_reminder</code>)</li>
                                <li class="mb-3">Paste it in the "Meta Template Name" field above</li>
                                <li class="mb-3">Fill in the other details and click "Import Template"</li>
                            </ol>

                            <h5 class="mb-3">Example:</h5>
                            <div class="bg-light-info p-4 rounded mb-4">
                                <div class="mb-2"><strong>Display Name:</strong> Payment Reminder</div>
                                <div class="mb-2"><strong>Meta Template Name:</strong> payment_reminder</div>
                                <div class="mb-2"><strong>Language:</strong> English</div>
                                <div class="mb-2"><strong>Category:</strong> Utility</div>
                                <div class="mb-2"><strong>Parameters:</strong> student_name, amount, due_date</div>
                            </div>

                            <h5 class="mb-3">Important Notes:</h5>
                            <ul class="text-gray-700 fs-7 mb-0 ps-4">
                                <li class="mb-2">Only import templates that are <strong>already approved</strong> by Meta</li>
                                <li class="mb-2">Template name must match exactly (case-sensitive)</li>
                                <li class="mb-2">Parameters help you remember variable order when sending</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>
@endsection
