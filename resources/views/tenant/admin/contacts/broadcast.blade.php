@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Send Announcement</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-muted"><a href="{{ route('tenant.admin.contacts.index') }}" class="text-muted text-hover-primary">Contacts</a></li>
                <li class="breadcrumb-item text-gray-900">Broadcast</li>
            </ul>
        </div>
        @endpush

        @php
            $preselectedIds = collect(explode(',', request('ids', '')))->map('intval')->filter()->values();
            $defaultAudience = old('audience', $preselectedIds->isNotEmpty() ? 'specific' : 'all');
        @endphp

        <form method="POST" action="{{ route('tenant.admin.contacts.broadcast.store') }}" id="broadcast-form" enctype="multipart/form-data">
            @csrf

            {{-- Pass selected IDs as hidden inputs when using specific audience --}}
            @php
                $contactIdsToUse = old('contact_ids') 
                    ? (array) old('contact_ids') 
                    : ($preselectedIds->isNotEmpty() ? $preselectedIds->toArray() : []);
            @endphp
            @if(!empty($contactIdsToUse))
                @foreach($contactIdsToUse as $pid)
                    <input type="hidden" name="contact_ids[]" value="{{ (int) $pid }}">
                @endforeach
            @endif

            <div class="row g-6">

                {{-- ── LEFT COLUMN ─────────────────────────────────────────────── --}}
                <div class="col-lg-8">

                    {{-- Step 1: Audience --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">
                                <span class="badge badge-square badge-primary me-3">1</span>
                                Who should receive this?
                            </h3>
                        </div>
                        <div class="card-body pt-5">

                            <div class="d-flex flex-column gap-4">

                                {{-- All contacts --}}
                                <label class="d-flex align-items-start gap-4 cursor-pointer p-4 border rounded-2 audience-option {{ $defaultAudience === 'all' ? 'border-primary bg-light-primary' : 'border-gray-200' }}">
                                    <input type="radio" name="audience" value="all" class="form-check-input mt-1 flex-shrink-0 audience-radio"
                                           {{ $defaultAudience === 'all' ? 'checked' : '' }}>
                                    <div>
                                        <div class="fw-semibold text-gray-800">All active contacts</div>
                                        <div class="text-muted fs-7">Send to every active parent and teacher in all branches.</div>
                                    </div>
                                </label>

                                {{-- Filter --}}
                                <label class="d-flex align-items-start gap-4 cursor-pointer p-4 border rounded-2 audience-option {{ $defaultAudience === 'filter' ? 'border-primary bg-light-primary' : 'border-gray-200' }}">
                                    <input type="radio" name="audience" value="filter" class="form-check-input mt-1 flex-shrink-0 audience-radio"
                                           {{ $defaultAudience === 'filter' ? 'checked' : '' }}>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-gray-800">Filter contacts</div>
                                        <div class="text-muted fs-7 mb-3">Narrow down by branch and/or role.</div>
                                        <div id="filter-fields" class="{{ $defaultAudience === 'filter' ? '' : 'd-none' }}">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7 mb-1">Branch</label>
                                                    <select name="branch_id" class="form-select form-select-solid filter-input">
                                                        <option value="">All branches</option>
                                                        @foreach($branches as $b)
                                                            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fs-7 mb-1">Role</label>
                                                    <select name="role" class="form-select form-select-solid filter-input">
                                                        <option value="">All roles</option>
                                                        @foreach(['parent', 'teacher'] as $r)
                                                            <option value="{{ $r }}" {{ old('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </label>

                                {{-- Specific selected contacts --}}
                                @php $specificIds = $preselectedIds->isNotEmpty() ? $preselectedIds : collect((array) old('contact_ids'))->map('intval')->filter(); @endphp
                                @if($specificIds->isNotEmpty())
                                <label class="d-flex align-items-start gap-4 cursor-pointer p-4 border rounded-2 audience-option {{ $defaultAudience === 'specific' ? 'border-primary bg-light-primary' : 'border-gray-200' }}">
                                    <input type="radio" name="audience" value="specific" class="form-check-input mt-1 flex-shrink-0 audience-radio"
                                           {{ $defaultAudience === 'specific' ? 'checked' : '' }}>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold text-gray-800 mb-2">
                                            Specific contacts
                                            <span class="badge badge-light-primary ms-2">{{ $specificIds->count() }} selected</span>
                                        </div>
                                        @if($selectedContacts->isNotEmpty())
                                            <div class="d-flex flex-column gap-2">
                                                @foreach($selectedContacts as $sc)
                                                @php $rc = ['parent' => 'primary', 'teacher' => 'success'][$sc->role] ?? 'secondary'; @endphp
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="symbol symbol-30px symbol-circle flex-shrink-0">
                                                        <div class="symbol-label fw-bold fs-8 bg-light-{{ $rc }} text-{{ $rc }}">
                                                            {{ strtoupper(substr($sc->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                                        <span class="fw-semibold fs-7 text-gray-800">{{ $sc->name }}</span>
                                                        <span class="badge badge-light-{{ $rc }} fs-9 py-1">{{ ucfirst($sc->role) }}</span>
                                                        @if($sc->email)
                                                            <span class="text-muted fs-8">{{ $sc->email }}</span>
                                                        @endif
                                                        @if($sc->phone)
                                                            <span class="text-muted fs-8">{{ $sc->phone }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-muted fs-7">Send only to the {{ $specificIds->count() }} contact(s) you selected.</div>
                                        @endif
                                    </div>
                                </label>
                                @endif

                            </div>
                        </div>
                    </div>

                    {{-- Step 2: Channel --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">
                                <span class="badge badge-square badge-primary me-3">2</span>
                                How to send?
                            </h3>
                        </div>
                        <div class="card-body pt-5">

                            @error('channel')
                                <div class="alert alert-danger py-2 fs-7 mb-4">{{ $message }}</div>
                            @enderror

                            <div class="d-flex gap-3 flex-wrap">

                                <label class="d-flex align-items-center gap-3 cursor-pointer p-4 border rounded-2 channel-option flex-grow-1 {{ old('channel', 'email') === 'email' ? 'border-primary bg-light-primary' : 'border-gray-200' }}"
                                       style="min-width:140px">
                                    <input type="radio" name="channel" value="email" class="form-check-input flex-shrink-0 channel-radio"
                                           {{ old('channel', 'email') === 'email' ? 'checked' : '' }}>
                                    <div>
                                        <div class="fw-semibold text-gray-800">
                                            <i class="ki-duotone ki-sms fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            Email
                                        </div>
                                        <div class="text-muted fs-8">Contacts with email address</div>
                                    </div>
                                </label>

                                <label class="d-flex align-items-center gap-3 cursor-pointer p-4 border rounded-2 channel-option flex-grow-1 {{ $smsEnabled ? '' : 'opacity-50' }} {{ old('channel') === 'sms' ? 'border-primary bg-light-primary' : 'border-gray-200' }}"
                                       style="min-width:140px">
                                    <input type="radio" name="channel" value="sms" class="form-check-input flex-shrink-0 channel-radio"
                                           {{ old('channel') === 'sms' ? 'checked' : '' }}
                                           {{ !$smsEnabled ? 'disabled' : '' }}>
                                    <div>
                                        <div class="fw-semibold text-gray-800">
                                            <i class="ki-duotone ki-phone fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            SMS
                                        </div>
                                        <div class="text-muted fs-8">{{ $smsEnabled ? 'Contacts with phone number' : 'Not configured' }}</div>
                                    </div>
                                </label>

                                <label class="d-flex align-items-center gap-3 cursor-pointer p-4 border rounded-2 channel-option flex-grow-1 {{ $whatsappEnabled ? '' : 'opacity-50' }} {{ old('channel') === 'whatsapp' ? 'border-primary bg-light-primary' : 'border-gray-200' }}"
                                       style="min-width:140px">
                                    <input type="radio" name="channel" value="whatsapp" class="form-check-input flex-shrink-0 channel-radio"
                                           {{ old('channel') === 'whatsapp' ? 'checked' : '' }}
                                           {{ !$whatsappEnabled ? 'disabled' : '' }}>
                                    <div>
                                        <div class="fw-semibold text-gray-800">
                                            <i class="ki-duotone ki-whatsapp fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                            WhatsApp
                                        </div>
                                        <div class="text-muted fs-8">{{ $whatsappEnabled ? 'Contacts with phone number' : 'Not configured' }}</div>
                                    </div>
                                </label>

                            </div>

                            @if(!$smsEnabled && !$whatsappEnabled)
                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mt-4 p-3 gap-3">
                                    <i class="ki-duotone ki-information fs-3 text-warning flex-shrink-0 mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <span class="text-gray-700 fs-7">SMS and WhatsApp are not configured. Set up a provider in School Settings to enable these channels.</span>
                                </div>
                            @elseif(!$smsEnabled)
                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mt-4 p-3 gap-3">
                                    <i class="ki-duotone ki-information fs-3 text-warning flex-shrink-0 mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <span class="text-gray-700 fs-7">SMS is not configured. Set up SMS in School Settings to enable.</span>
                                </div>
                            @elseif(!$whatsappEnabled)
                                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mt-4 p-3 gap-3">
                                    <i class="ki-duotone ki-information fs-3 text-warning flex-shrink-0 mt-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <span class="text-gray-700 fs-7">WhatsApp is not configured. Set up WhatsApp in School Settings to enable.</span>
                                </div>
                            @endif

                        </div>
                    </div>

                    {{-- Step 3: Message --}}
                    <div class="card mb-6">
                        <div class="card-header">
                            <h3 class="card-title fw-bold">
                                <span class="badge badge-square badge-primary me-3">3</span>
                                Message
                            </h3>
                        </div>
                        <div class="card-body pt-5">

                            {{-- Subject (email only) --}}
                            <div id="subject-field" class="{{ old('channel', 'email') === 'email' ? '' : 'd-none' }} mb-5">
                                <label class="form-label fw-semibold required">Subject</label>
                                <input name="subject" value="{{ old('subject') }}" maxlength="150"
                                       placeholder="e.g. Important update from the school"
                                       class="form-control form-control-solid @error('subject') is-invalid @enderror">
                                @error('subject')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                            </div>

                            {{-- Media (WhatsApp only) --}}
                            <div id="media-field" class="{{ old('channel') === 'whatsapp' ? '' : 'd-none' }} mb-5">
                                <label class="form-label fw-semibold">Attach Media (Optional)</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <select name="media_type" id="media-type-select" class="form-select form-select-solid @error('media_type') is-invalid @enderror">
                                            <option value="">No media</option>
                                            <option value="image" {{ old('media_type') === 'image' ? 'selected' : '' }}>Image</option>
                                            <option value="document" {{ old('media_type') === 'document' ? 'selected' : '' }}>Document</option>
                                            <option value="video" {{ old('media_type') === 'video' ? 'selected' : '' }}>Video</option>
                                            <option value="audio" {{ old('media_type') === 'audio' ? 'selected' : '' }}>Audio</option>
                                        </select>
                                        @error('media_type')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <input type="file" name="media" id="media-file" class="form-control form-control-solid @error('media') is-invalid @enderror">
                                        @error('media')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="text-muted fs-8 mt-2">Max file size: 16MB. Supported formats depend on media type.</div>
                            </div>

                            {{-- WhatsApp Template Selection --}}
                            <div id="template-field" class="{{ old('channel') === 'whatsapp' ? '' : 'd-none' }} mb-5">
                                <label class="form-label fw-semibold">WhatsApp Template (Optional)</label>
                                <select name="whatsapp_template_id" id="template-select" class="form-select form-select-solid @error('whatsapp_template_id') is-invalid @enderror">
                                    <option value="">No template - send as plain message</option>
                                    @foreach($whatsappTemplates as $template)
                                        <option value="{{ $template->id }}" 
                                                data-parameters="{{ is_array($template->parameters) ? count($template->parameters) : ($template->parameters ?: 0) }}"
                                                {{ old('whatsapp_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('whatsapp_template_id')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                <div class="text-muted fs-8 mt-2">Use a pre-approved template for better delivery rates. Note: Media upload is disabled when using templates.</div>
                            </div>

                            {{-- Template Parameters (dynamic) --}}
                            <div id="template-parameters-field" class="d-none mb-5">
                                <label class="form-label fw-semibold">Template Parameters</label>
                                <div id="parameters-container"></div>
                                <div class="text-muted fs-8 mt-2">Fill in the variables for your template.</div>
                            </div>

                            {{-- Body --}}
                            <div>
                                <label class="form-label fw-semibold required">Message Body</label>
                                <textarea name="message" id="message-body" rows="7" maxlength="1600"
                                          placeholder="Write your announcement here…"
                                          class="form-control form-control-solid @error('message') is-invalid @enderror">{{ old('message') }}</textarea>
                                @error('message')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                <div class="d-flex justify-content-between mt-2">
                                    <div class="text-muted fs-7" id="sms-hint"></div>
                                    <div class="text-muted fs-7"><span id="char-count">0</span> / 1600 chars</div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <button type="button" id="btn-preview" class="btn btn-primary btn-lg">
                            <i class="ki-duotone ki-send fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                            Preview & Send
                        </button>
                        <a href="{{ route('tenant.admin.contacts.index') }}" class="btn btn-light btn-lg">Cancel</a>
                    </div>

                </div>

                {{-- ── RIGHT COLUMN ─────────────────────────────────────────────── --}}
                <div class="col-lg-4">

                    {{-- Live recipient preview --}}
                    <div class="card mb-5">
                        <div class="card-header">
                            <h3 class="card-title fw-semibold fs-7 text-uppercase text-muted">Recipients Preview</h3>
                        </div>
                        <div class="card-body py-5 text-center" id="preview-box">
                            <div class="fs-2x fw-bold text-primary" id="preview-count">—</div>
                            <div class="text-muted fs-7 mt-1" id="preview-label">contacts will receive this message</div>
                            <div class="separator my-4"></div>
                            <div class="d-flex justify-content-around text-center">
                                <div>
                                    <div class="fw-bold text-gray-800" id="preview-email-count">—</div>
                                    <div class="text-muted fs-8">via Email</div>
                                </div>
                                <div>
                                    <div class="fw-bold text-gray-800" id="preview-sms-count">—</div>
                                    <div class="text-muted fs-8">via SMS</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tips --}}
                    <div class="card">
                        <div class="card-body py-5">
                            <div class="text-muted fs-7 fw-semibold text-uppercase mb-3">Tips</div>
                            <ul class="text-muted fs-7 ps-4 mb-0" style="line-height:1.8">
                                <li>SMS messages over 160 characters are split into multiple segments and may cost more.</li>
                                <li>Only <strong>active</strong> (non-deactivated) contacts are included.</li>
                                <li>Contacts without the required contact info for the chosen channel are automatically skipped.</li>
                                <li>Messages are sent in the background — large batches may take a few minutes.</li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>
        </form>

    </div>
</div>

{{-- Confirmation modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title fw-bold mb-0">Confirm Broadcast</h5>
                    <div class="text-muted fs-7 mt-1" id="confirmBody"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-4 fs-7 text-muted" id="confirmDetail"></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmOk">
                    <i class="ki-duotone ki-send fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                    Send Now
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const countUrl    = '{{ route('tenant.admin.contacts.broadcast.count') }}';
    const form        = document.getElementById('broadcast-form');
    const modal       = new bootstrap.Modal(document.getElementById('confirmModal'));
    const confirmOk   = document.getElementById('confirmOk');
    const confirmBody = document.getElementById('confirmBody');
    const confirmDetail = document.getElementById('confirmDetail');

    // ── Audience radio styling ────────────────────────────────────────────
    document.querySelectorAll('.audience-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.audience-option').forEach(el => {
                el.classList.remove('border-primary', 'bg-light-primary');
                el.classList.add('border-gray-200');
            });
            this.closest('.audience-option').classList.add('border-primary', 'bg-light-primary');
            this.closest('.audience-option').classList.remove('border-gray-200');

            const filterFields = document.getElementById('filter-fields');
            if (this.value === 'filter') {
                filterFields.classList.remove('d-none');
            } else {
                filterFields.classList.add('d-none');
            }
            fetchCount();
        });
    });

    // ── Channel radio styling + subject field toggle ──────────────────────
    document.querySelectorAll('.channel-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.channel-option').forEach(el => {
                el.classList.remove('border-primary', 'bg-light-primary');
                el.classList.add('border-gray-200');
            });
            if (! this.disabled) {
                this.closest('.channel-option').classList.add('border-primary', 'bg-light-primary');
                this.closest('.channel-option').classList.remove('border-gray-200');
            }

            const subjectField = document.getElementById('subject-field');
            const mediaField = document.getElementById('media-field');
            const templateField = document.getElementById('template-field');
            const templateParamsField = document.getElementById('template-parameters-field');
            const templateSelect = document.getElementById('template-select');
            
            if (this.value === 'email') {
                subjectField.classList.remove('d-none');
                mediaField.classList.add('d-none');
                templateField.classList.add('d-none');
                templateParamsField.classList.add('d-none');
            } else if (this.value === 'whatsapp') {
                subjectField.classList.add('d-none');
                templateField.classList.remove('d-none');
                
                // Show media only if no template is selected
                if (templateSelect && templateSelect.value) {
                    mediaField.classList.add('d-none');
                } else {
                    mediaField.classList.remove('d-none');
                }
                // Template params field visibility is controlled by template-select change event
            } else {
                subjectField.classList.add('d-none');
                mediaField.classList.add('d-none');
                templateField.classList.add('d-none');
                templateParamsField.classList.add('d-none');
            }

            updateSmsHint();
            fetchCount();
        });
    });

    // ── Filter inputs trigger re-count ────────────────────────────────────
    document.querySelectorAll('.filter-input').forEach(function (el) {
        el.addEventListener('change', fetchCount);
    });

    // ── Template selection handler ────────────────────────────────────────
    const templateSelect = document.getElementById('template-select');
    const templateParamsField = document.getElementById('template-parameters-field');
    const parametersContainer = document.getElementById('parameters-container');
    const mediaField = document.getElementById('media-field');

    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const parametersCount = selectedOption.dataset.parameters || 0;
            
            // Clear previous parameters
            parametersContainer.innerHTML = '';
            
            // Hide/show media field based on template selection
            if (this.value) {
                // Template selected - hide media upload
                mediaField.classList.add('d-none');
                
                // Clear media selection
                const mediaTypeSelect = document.getElementById('media-type-select');
                const mediaFile = document.getElementById('media-file');
                if (mediaTypeSelect) mediaTypeSelect.value = '';
                if (mediaFile) mediaFile.value = '';
            } else {
                // No template - show media upload
                mediaField.classList.remove('d-none');
            }
            
            if (parametersCount > 0 && this.value) {
                templateParamsField.classList.remove('d-none');
                
                // Generate input fields for each parameter
                for (let i = 1; i <= parametersCount; i++) {
                    const paramDiv = document.createElement('div');
                    paramDiv.className = 'mb-3';
                    
                    const label = document.createElement('label');
                    label.className = 'form-label fs-7 mb-1';
                    label.textContent = 'Parameter ' + i;
                    
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'template_parameters[]';
                    input.className = 'form-control form-control-solid';
                    input.placeholder = 'Enter value for {{' + i + '}}';
                    input.maxLength = 500;
                    
                    paramDiv.appendChild(label);
                    paramDiv.appendChild(input);
                    parametersContainer.appendChild(paramDiv);
                }
            } else {
                templateParamsField.classList.add('d-none');
            }
        });
        
        // Trigger on load if there's a selected template (for validation errors)
        if (templateSelect.value) {
            templateSelect.dispatchEvent(new Event('change'));
        }
    }

    // ── Character counter + SMS segment hint ─────────────────────────────
    const messageBody = document.getElementById('message-body');
    const charCount   = document.getElementById('char-count');
    const smsHint     = document.getElementById('sms-hint');

    function updateSmsHint() {
        const len     = messageBody.value.length;
        const channel = document.querySelector('.channel-radio:checked')?.value;
        charCount.textContent = len;

        if (channel === 'whatsapp') {
            const segments = len <= 160 ? 1 : Math.ceil(len / 153);
            smsHint.textContent = segments === 1
                ? 'WhatsApp: 1 segment (≤160 chars)'
                : `WhatsApp: ${segments} segments (${len} chars)`;
            smsHint.className = 'fs-7 ' + (segments > 1 ? 'text-warning fw-semibold' : 'text-muted');
        } else {
            smsHint.textContent = '';
        }
    }

    messageBody.addEventListener('input', updateSmsHint);
    updateSmsHint();

    // ── Live count fetch ──────────────────────────────────────────────────
    let fetchTimer = null;

    function fetchCount() {
        clearTimeout(fetchTimer);
        fetchTimer = setTimeout(doFetch, 250);
    }

    function buildParams() {
        const audience = document.querySelector('.audience-radio:checked')?.value || 'all';
        const channel  = document.querySelector('.channel-radio:checked')?.value  || 'email';
        const params   = new URLSearchParams({ audience, channel });

        if (audience === 'filter') {
            const branchId = document.querySelector('select[name="branch_id"]')?.value;
            const role     = document.querySelector('select[name="role"]')?.value;
            if (branchId) params.set('branch_id', branchId);
            if (role)     params.set('role', role);
        } else if (audience === 'specific') {
            document.querySelectorAll('input[name="contact_ids[]"]').forEach(function (inp) {
                params.append('contact_ids[]', inp.value);
            });
        }
        return params;
    }

    function doFetch() {
        document.getElementById('preview-count').textContent = '…';
        document.getElementById('preview-label').textContent = 'calculating…';

        fetch(countUrl + '?' + buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('preview-count').textContent      = data.channel_count;
            document.getElementById('preview-email-count').textContent = data.email_count;
            document.getElementById('preview-sms-count').textContent   = data.sms_count;
            document.getElementById('preview-label').textContent       = 'contact(s) will receive this';
        })
        .catch(() => {
            document.getElementById('preview-count').textContent = '?';
            document.getElementById('preview-label').textContent = 'could not load count';
        });
    }

    // Initial load
    doFetch();

    // ── Preview & Send button ─────────────────────────────────────────────
    document.getElementById('btn-preview').addEventListener('click', function () {
        const channel     = document.querySelector('.channel-radio:checked')?.value || 'email';
        const channelLabel = { email: 'Email', sms: 'SMS', whatsapp: 'WhatsApp' }[channel] || channel;
        const count       = document.getElementById('preview-count').textContent;
        const audienceVal = document.querySelector('.audience-radio:checked')?.value || 'all';
        const audienceLabel = audienceVal === 'filter' ? 'the filtered audience'
            : audienceVal === 'specific' ? 'the selected contacts'
            : 'all active contacts';

        confirmBody.textContent  = `Send this announcement via ${channelLabel} to ${count} contact(s) in ${audienceLabel}?`;
        confirmDetail.innerHTML  = '<strong>Note:</strong> This cannot be undone. Each contact will receive the message individually.';

        // Wire up confirm button fresh each time
        const fresh = confirmOk.cloneNode(true);
        confirmOk.replaceWith(fresh);
        fresh.addEventListener('click', function () {
            modal.hide();
            form.submit();
        });

        modal.show();
    });
})();
</script>
@endpush
@endsection
