@extends('layouts.tenant_admin')
@section('page_title', 'Settings')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">School Settings</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">Settings</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Main form --}}
            <div class="col-lg-9">
                <form method="POST" action="{{ route('tenant.admin.settings.update') }}" enctype="multipart/form-data" id="settings-form">
                    @csrf @method('PUT')

                    @php
                        $settingsPlan      = \App\Services\PlanService::forCurrentTenant();
                        $planAllowSmtp     = $settingsPlan->allows('custom_smtp');
                        $planAllowSms      = $settingsPlan->allows('broadcasting');
                        $planAllowWhatsApp = $settingsPlan->allows('whatsapp');
                        $planAllowApi      = $settingsPlan->allows('api_access');
                    @endphp

                    {{-- Tab navigation --}}
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-0 fs-6 border-0">
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ !in_array(session('_settings_tab'), ['portal','issues','email','sms','whatsapp','api']) ? 'active' : '' }}"
                               data-bs-toggle="tab" href="#tab-general">
                                <i class="ki-duotone ki-home fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ session('_settings_tab') === 'portal' ? 'active' : '' }}"
                               data-bs-toggle="tab" href="#tab-portal">
                                <i class="ki-duotone ki-geolocation fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Portal
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ session('_settings_tab') === 'issues' ? 'active' : '' }}"
                               data-bs-toggle="tab" href="#tab-issues">
                                <i class="ki-duotone ki-message-text-2 fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                Issues
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ session('_settings_tab') === 'email' ? 'active' : '' }} {{ !$planAllowSmtp ? 'text-muted' : '' }}"
                               data-bs-toggle="tab" href="#tab-email">
                                <i class="ki-duotone ki-sms fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Email
                                @if(!$planAllowSmtp)
                                    <i class="ki-duotone ki-lock-2 fs-7 ms-1 text-warning" title="Requires Growth plan or higher"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                @elseif($school->setting('smtp_enabled'))
                                    <span class="badge badge-sm badge-success ms-1">On</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ session('_settings_tab') === 'sms' ? 'active' : '' }} {{ !$planAllowSms ? 'text-muted' : '' }}"
                               data-bs-toggle="tab" href="#tab-sms">
                                <i class="ki-duotone ki-phone fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                SMS
                                @if(!$planAllowSms)
                                    <i class="ki-duotone ki-lock-2 fs-7 ms-1 text-warning" title="Requires Growth plan or higher"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                @elseif($school->setting('sms_provider'))
                                    <span class="badge badge-sm badge-success ms-1">On</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-semibold {{ session('_settings_tab') === 'whatsapp' ? 'active' : '' }} {{ !$planAllowWhatsApp ? 'text-muted' : '' }}"
                               data-bs-toggle="tab" href="#tab-whatsapp">
                                <i class="ki-duotone ki-whatsapp fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                WhatsApp
                                @if(!$planAllowWhatsApp)
                                    <i class="ki-duotone ki-lock-2 fs-7 ms-1 text-warning" title="Requires Pro plan or higher"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                @elseif($school->setting('whatsapp_enabled'))
                                    <span class="badge badge-sm badge-success ms-1">On</span>
                                @endif
                            </a>
                        </li>
                        @if($settingsPlan->allows('api_access'))
                        <li class="nav-item">
                            <a class="nav-link fw-semibold js-api-tab-toggle {{ session('_settings_tab') === 'api' ? 'active' : '' }}"
                               href="#">
                                <i class="ki-duotone ki-abstract-26 fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                                API Keys
                            </a>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content mt-0">

                        {{-- ── TAB: GENERAL ──────────────────────────────────────── --}}
                        <div class="tab-pane fade {{ !in_array(session('_settings_tab'), ['portal','issues','email','sms','whatsapp','api']) ? 'show active' : '' }}" id="tab-general">
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7">
                                    <div class="row g-5">

                                        <div class="col-md-8">
                                            <label class="form-label required fw-semibold">School Name</label>
                                            <input name="name" value="{{ old('name', $school->name) }}"
                                                   class="form-control form-control-solid @error('name') is-invalid @enderror"
                                                   required maxlength="150">
                                            @error('name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">City</label>
                                            <input name="city" value="{{ old('city', $school->city) }}"
                                                   class="form-control form-control-solid @error('city') is-invalid @enderror"
                                                   placeholder="e.g. Karachi" maxlength="100">
                                            @error('city')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Portal Primary Color</label>
                                            @php $currentColor = old('primary_color', $school->setting('primary_color') ?: '#4338ca'); @endphp
                                            <div class="d-flex align-items-center gap-3">
                                                <input type="color" name="primary_color"
                                                       value="{{ $currentColor }}"
                                                       class="form-control form-control-color form-control-solid @error('primary_color') is-invalid @enderror"
                                                       style="width:56px;height:38px;padding:3px 4px;cursor:pointer;">
                                                <span id="color_hex_display" class="font-monospace text-muted fs-7">{{ $currentColor }}</span>
                                            </div>
                                            <div class="text-muted fs-7 mt-1">Accent color for public portal — buttons, links, gradients.</div>
                                            @error('primary_color')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        {{-- Logo --}}
                                        <div class="col-12">
                                            <div class="separator my-2"></div>
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">School Logo</label>

                                            @if($school->logo_url)
                                            <div class="d-flex align-items-center gap-4 mb-4">
                                                <div class="border rounded-2 p-3 bg-light d-inline-block">
                                                    <img src="{{ $school->logo_url }}" alt="Logo"
                                                         style="max-height:60px;max-width:180px;object-fit:contain;">
                                                </div>
                                                <label class="form-check form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox" name="remove_logo" value="1">
                                                    <span class="form-check-label text-danger fw-semibold fs-7">Remove logo</span>
                                                </label>
                                            </div>
                                            @endif

                                            <input type="file" name="logo" id="logo_input" accept="image/*"
                                                   class="form-control form-control-solid @error('logo') is-invalid @enderror">
                                            <div class="text-muted fs-7 mt-1">JPG, PNG, SVG, WebP — max 2 MB. Shown in sidebar and public portal header.</div>
                                            @error('logo')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror

                                            <div id="logo_preview_wrapper" class="mt-3 d-none">
                                                <div class="border rounded-2 p-3 bg-light d-inline-block">
                                                    <img id="logo_preview" src="#" alt="Preview" style="max-height:60px;max-width:180px;object-fit:contain;">
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Save Settings
                                </button>
                                <a href="{{ url('admin') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </div>

                        {{-- ── TAB: PORTAL ────────────────────────────────────────── --}}
                        <div class="tab-pane fade {{ session('_settings_tab') === 'portal' ? 'show active' : '' }}" id="tab-portal">
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7">
                                    <div class="row g-5">

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Full Address</label>
                                            <input name="address" value="{{ old('address', $school->setting('address')) }}"
                                                   class="form-control form-control-solid @error('address') is-invalid @enderror"
                                                   placeholder="e.g. 123 Main Street, Karachi" maxlength="255">
                                            @error('address')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Email</label>
                                            <input name="contact_email" type="email"
                                                   value="{{ old('contact_email', $school->setting('contact_email')) }}"
                                                   class="form-control form-control-solid @error('contact_email') is-invalid @enderror"
                                                   placeholder="info@school.edu" maxlength="150">
                                            @error('contact_email')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact Phone</label>
                                            <input name="contact_phone"
                                                   value="{{ old('contact_phone', $school->setting('contact_phone')) }}"
                                                   class="form-control form-control-solid @error('contact_phone') is-invalid @enderror"
                                                   placeholder="+92 300 0000000" maxlength="30">
                                            @error('contact_phone')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">School Website URL</label>
                                            <input name="website_url" type="url"
                                                   value="{{ old('website_url', $school->setting('website_url')) }}"
                                                   class="form-control form-control-solid @error('website_url') is-invalid @enderror"
                                                   placeholder="https://www.school.edu" maxlength="255">
                                            @error('website_url')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Portal Welcome Message</label>
                                            <textarea name="welcome_message" rows="2" maxlength="300"
                                                      class="form-control form-control-solid @error('welcome_message') is-invalid @enderror"
                                                      placeholder="e.g. Submit and track your concerns directly with our team.">{{ old('welcome_message', $school->setting('welcome_message')) }}</textarea>
                                            <div class="text-muted fs-7 mt-1">Shown as the subtitle under "Welcome to {{ $school->name }}" on the public portal.</div>
                                            @error('welcome_message')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Submission Thank-You Message</label>
                                            <textarea name="thankyou_message" rows="2" maxlength="300"
                                                      class="form-control form-control-solid @error('thankyou_message') is-invalid @enderror"
                                                      placeholder="e.g. Thank you! Our team will review your issue shortly.">{{ old('thankyou_message', $school->setting('thankyou_message')) }}</textarea>
                                            <div class="text-muted fs-7 mt-1">Shown in the success message after a parent or teacher submits an issue.</div>
                                            @error('thankyou_message')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Save Settings
                                </button>
                                <a href="{{ url('admin') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </div>

                        {{-- ── TAB: ISSUES ─────────────────────────────────────────── --}}
                        <div class="tab-pane fade {{ session('_settings_tab') === 'issues' ? 'show active' : '' }}" id="tab-issues">
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7">

                                    <div class="d-flex align-items-start justify-content-between gap-4 py-3">
                                        <div>
                                            <div class="fw-semibold text-gray-800 mb-1">Allow new issue submissions</div>
                                            <div class="text-muted fs-7">
                                                When disabled, parents and teachers will see a notice instead of the submission form.
                                                Existing issues and tracking are unaffected.
                                            </div>
                                        </div>
                                        <label class="form-check form-switch form-check-custom form-check-solid flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="allow_new_issues" value="1"
                                                   {{ old('allow_new_issues', $school->setting('allow_new_issues', true)) ? 'checked' : '' }}>
                                        </label>
                                    </div>

                                    <div class="separator"></div>

                                    @if($planAllowChatbot ?? false)
                                    <div class="d-flex align-items-start justify-content-between gap-4 py-3">
                                        <div>
                                            <div class="fw-semibold text-gray-800 mb-1">Enable AI chatbot for parents</div>
                                            <div class="text-muted fs-7">
                                                When enabled, parents and teachers can ask questions about school fees,
                                                schedules, and policies via the AI chatbot on the public portal.
                                                Requires documents to be uploaded in the Document Library.
                                            </div>
                                        </div>
                                        <label class="form-check form-switch form-check-custom form-check-solid flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="chatbot_enabled" value="1"
                                                   {{ old('chatbot_enabled', $school->setting('chatbot_enabled', false)) ? 'checked' : '' }}>
                                        </label>
                                    </div>

                                    <div class="separator"></div>
                                    @endif

                                    <div class="d-flex align-items-start justify-content-between gap-4 py-3">
                                        <div>
                                            <div class="fw-semibold text-gray-800 mb-1">Allow anonymous issue submissions</div>
                                            <div class="text-muted fs-7">
                                                When enabled, anyone can report an issue without an access code or identity.
                                                Anonymous reports are only visible to admins until assigned.
                                            </div>
                                        </div>
                                        <label class="form-check form-switch form-check-custom form-check-solid flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="allow_anonymous_issues" value="1"
                                                   {{ old('allow_anonymous_issues', $school->setting('allow_anonymous_issues', true)) ? 'checked' : '' }}>
                                        </label>
                                    </div>

                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Save Settings
                                </button>
                                <a href="{{ url('admin') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </div>

                        {{-- ── TAB: EMAIL (SMTP) ───────────────────────────────────── --}}
                        <div class="tab-pane fade {{ session('_settings_tab') === 'email' ? 'show active' : '' }}" id="tab-email">
                            @if(!$planAllowSmtp)
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7 text-center py-12">
                                    <i class="ki-duotone ki-lock-2 fs-3x text-warning mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <h4 class="fw-bold text-gray-800 mb-2">Custom SMTP requires Growth plan or higher</h4>
                                    <p class="text-muted fs-6">Upgrade your plan to configure a custom outgoing email server for transactional emails.</p>
                                </div>
                            </div>
                            @else
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7">

                                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-6 p-4 gap-4">
                                        <i class="ki-duotone ki-information fs-2tx text-primary flex-shrink-0 mt-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        <div class="text-gray-700 fs-7">
                                            When enabled, all outgoing emails for this school (access codes, status updates, CSAT surveys)
                                            are sent through your own SMTP server instead of the platform default.
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-start justify-content-between gap-4 py-3 mb-4">
                                        <div>
                                            <div class="fw-semibold text-gray-800 mb-1">Enable custom SMTP</div>
                                            <div class="text-muted fs-7">When on, the settings below will be used for all school emails.</div>
                                        </div>
                                        <label class="form-check form-switch form-check-custom form-check-solid flex-shrink-0">
                                            <input class="form-check-input" type="checkbox" name="smtp_enabled" value="1"
                                                   id="smtp_enabled_toggle"
                                                   {{ old('smtp_enabled', $school->setting('smtp_enabled')) ? 'checked' : '' }}>
                                        </label>
                                    </div>

                                    <div id="smtp_fields" class="{{ old('smtp_enabled', $school->setting('smtp_enabled')) ? '' : 'd-none' }}">
                                        <div class="separator mb-6"></div>
                                        <div class="row g-5">

                                            <div class="col-md-8">
                                                <label class="form-label fw-semibold">SMTP Host</label>
                                                <input name="smtp_host"
                                                       value="{{ old('smtp_host', $school->setting('smtp_host')) }}"
                                                       placeholder="smtp.example.com"
                                                       class="form-control form-control-solid @error('smtp_host') is-invalid @enderror">
                                                @error('smtp_host')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Port</label>
                                                <input name="smtp_port" type="number" min="1" max="65535"
                                                       value="{{ old('smtp_port', $school->setting('smtp_port', 587)) }}"
                                                       placeholder="587"
                                                       class="form-control form-control-solid @error('smtp_port') is-invalid @enderror">
                                                @error('smtp_port')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Username</label>
                                                <input name="smtp_username"
                                                       value="{{ old('smtp_username', $school->setting('smtp_username')) }}"
                                                       placeholder="your@email.com"
                                                       class="form-control form-control-solid @error('smtp_username') is-invalid @enderror">
                                                @error('smtp_username')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Password</label>
                                                <input name="smtp_password" type="password"
                                                       placeholder="{{ $school->setting('smtp_password') ? '••••••••  (saved)' : 'Enter password' }}"
                                                       autocomplete="new-password"
                                                       class="form-control form-control-solid @error('smtp_password') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Leave blank to keep the saved password.</div>
                                                @error('smtp_password')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Encryption</label>
                                                <select name="smtp_encryption" class="form-select form-select-solid">
                                                    <option value="tls" {{ old('smtp_encryption', $school->setting('smtp_encryption', 'tls')) === 'tls' ? 'selected' : '' }}>TLS (recommended)</option>
                                                    <option value="ssl" {{ old('smtp_encryption', $school->setting('smtp_encryption')) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                                    <option value=""   {{ old('smtp_encryption', $school->setting('smtp_encryption')) === ''    ? 'selected' : '' }}>None</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">From Address</label>
                                                <input name="smtp_from_address" type="email"
                                                       value="{{ old('smtp_from_address', $school->setting('smtp_from_address')) }}"
                                                       placeholder="noreply@school.edu"
                                                       class="form-control form-control-solid @error('smtp_from_address') is-invalid @enderror">
                                                @error('smtp_from_address')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">From Name</label>
                                                <input name="smtp_from_name"
                                                       value="{{ old('smtp_from_name', $school->setting('smtp_from_name')) }}"
                                                       placeholder="{{ $school->name }}"
                                                       class="form-control form-control-solid @error('smtp_from_name') is-invalid @enderror">
                                                @error('smtp_from_name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Save Settings
                                </button>
                                <a href="{{ url('admin') }}" class="btn btn-light">Cancel</a>
                            </div>
                            @endif
                        </div>

                        {{-- ── TAB: SMS ────────────────────────────────────────────── --}}
                        @php $currentProvider = old('sms_provider', $school->setting('sms_provider')); @endphp
                        <div class="tab-pane fade {{ session('_settings_tab') === 'sms' ? 'show active' : '' }}" id="tab-sms">
                            @if(!$planAllowSms)
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7 text-center py-12">
                                    <i class="ki-duotone ki-lock-2 fs-3x text-warning mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <h4 class="fw-bold text-gray-800 mb-2">SMS requires Growth plan or higher</h4>
                                    <p class="text-muted fs-6">Upgrade your plan to send access codes and broadcasts via SMS to parents and teachers.</p>
                                </div>
                            </div>
                            @else
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7">

                                    <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-6 p-4 gap-4">
                                        <i class="ki-duotone ki-information fs-2tx text-primary flex-shrink-0 mt-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        <div class="text-gray-700 fs-7">
                                            Connect your school's SMS provider to send access codes to parents and teachers.
                                            Choose <strong>Twilio</strong> (global) or <strong>Msegat</strong> (KSA/local).
                                            Select <em>None</em> to disable SMS for this school.
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label class="form-label fw-semibold required">SMS Provider</label>
                                        <select name="sms_provider" id="sms_provider_select" class="form-select form-select-solid">
                                            <option value=""    {{ ! $currentProvider           ? 'selected' : '' }}>None — SMS disabled</option>
                                            <option value="twilio" {{ $currentProvider === 'twilio' ? 'selected' : '' }}>Twilio</option>
                                            <option value="msegat" {{ $currentProvider === 'msegat' ? 'selected' : '' }}>Msegat (KSA)</option>
                                        </select>
                                    </div>

                                    <div id="twilio_fields" class="{{ $currentProvider === 'twilio' ? '' : 'd-none' }}">
                                        <div class="separator mb-6"></div>
                                        <div class="row g-5">

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Account SID</label>
                                                <input name="twilio_sid"
                                                       value="{{ old('twilio_sid', $school->setting('twilio_sid')) }}"
                                                       placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                                       class="form-control form-control-solid font-monospace @error('twilio_sid') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Found on your Twilio Console dashboard.</div>
                                                @error('twilio_sid')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Auth Token</label>
                                                <input name="twilio_token" type="password"
                                                       placeholder="{{ $school->setting('twilio_token') ? '••••••••  (saved)' : 'Enter auth token' }}"
                                                       autocomplete="new-password"
                                                       class="form-control form-control-solid @error('twilio_token') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Leave blank to keep the saved token.</div>
                                                @error('twilio_token')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">From Number</label>
                                                <input name="twilio_from"
                                                       value="{{ old('twilio_from', $school->setting('twilio_from')) }}"
                                                       placeholder="+12015550123"
                                                       class="form-control form-control-solid font-monospace @error('twilio_from') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Your Twilio phone number in E.164 format.</div>
                                                @error('twilio_from')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                        </div>
                                    </div>

                                    <div id="msegat_fields" class="{{ $currentProvider === 'msegat' ? '' : 'd-none' }}">
                                        <div class="separator mb-6"></div>
                                        <div class="row g-5">

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Username</label>
                                                <input name="msegat_username"
                                                       value="{{ old('msegat_username', $school->setting('msegat_username')) }}"
                                                       placeholder="Your Msegat account username"
                                                       class="form-control form-control-solid @error('msegat_username') is-invalid @enderror">
                                                @error('msegat_username')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">API Key</label>
                                                <input name="msegat_api_key" type="password"
                                                       placeholder="{{ $school->setting('msegat_api_key') ? '••••••••  (saved)' : 'Enter API key' }}"
                                                       autocomplete="new-password"
                                                       class="form-control form-control-solid @error('msegat_api_key') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Found in your Msegat account settings. Leave blank to keep saved key.</div>
                                                @error('msegat_api_key')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Sender Name</label>
                                                <input name="msegat_sender"
                                                       value="{{ old('msegat_sender', $school->setting('msegat_sender')) }}"
                                                       placeholder="e.g. MySchool"
                                                       maxlength="11"
                                                       class="form-control form-control-solid @error('msegat_sender') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Your approved sender name (up to 11 characters, English/numbers only).</div>
                                                @error('msegat_sender')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Save Settings
                                </button>
                                <a href="{{ url('admin') }}" class="btn btn-light">Cancel</a>
                            </div>
                            @endif
                        </div>

                        {{-- ── TAB: WHATSAPP ───────────────────────────────────────── --}}
                        @php $whatsappEnabled = old('whatsapp_enabled', $school->setting('whatsapp_enabled')); @endphp
                        <div class="tab-pane fade {{ session('_settings_tab') === 'whatsapp' ? 'show active' : '' }}" id="tab-whatsapp">
                            @if(!$planAllowWhatsApp)
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7 text-center py-12">
                                    <i class="ki-duotone ki-lock-2 fs-3x text-warning mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
                                    <h4 class="fw-bold text-gray-800 mb-2">WhatsApp requires Pro plan or higher</h4>
                                    <p class="text-muted fs-6">Upgrade to the Pro plan to connect WhatsApp Business and send messages to parents and teachers.</p>
                                </div>
                            </div>
                            @else
                            <div class="card border-top-0 rounded-top-0 mb-5">
                                <div class="card-body pt-7">

                                    <div class="notice d-flex bg-light-success rounded border-success border border-dashed mb-6 p-4 gap-4">
                                        <i class="ki-duotone ki-information fs-2tx text-success flex-shrink-0 mt-1">
                                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                                        </i>
                                        <div class="text-gray-700 fs-7">
                                            Connect your school's WhatsApp Business API to send announcements to parents and teachers.
                                            You'll need a <strong>Meta Developer Account</strong> and <strong>WhatsApp Business App</strong>.
                                            Select <em>Disable</em> to turn off WhatsApp broadcasting.
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label class="form-label fw-semibold">WhatsApp Broadcasting</label>
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="whatsapp_enabled" id="whatsapp_enabled"
                                                   {{ $whatsappEnabled ? 'checked' : '' }}>
                                            <label class="form-check-label text-gray-700" for="whatsapp_enabled">
                                                Enable WhatsApp broadcasts
                                            </label>
                                        </div>
                                        <div class="form-text">
                                            <a href="{{ route('tenant.admin.whatsapp.templates.index') }}" class="text-primary">
                                                <i class="ki-duotone ki-document fs-5"><span class="path1"></span><span class="path2"></span></i>
                                                Manage WhatsApp Templates
                                            </a>
                                        </div>
                                    </div>

                                    <div id="whatsapp_fields" class="{{ $whatsappEnabled ? '' : 'd-none' }}">
                                        <div class="separator mb-6"></div>
                                        <div class="row g-5">

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Phone Number ID</label>
                                                <input name="whatsapp_phone_number_id"
                                                       value="{{ old('whatsapp_phone_number_id', $school->setting('whatsapp_phone_number_id')) }}"
                                                       placeholder="123456789012345"
                                                       class="form-control form-control-solid font-monospace @error('whatsapp_phone_number_id') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Found in your WhatsApp Business API settings (Meta Developer Portal).</div>
                                                @error('whatsapp_phone_number_id')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Access Token</label>
                                                <input name="whatsapp_access_token" type="password"
                                                       placeholder="{{ $school->setting('whatsapp_access_token') ? '••••••••  (saved)' : 'Enter access token' }}"
                                                       autocomplete="new-password"
                                                       class="form-control form-control-solid @error('whatsapp_access_token') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">From Meta Developer Portal → WhatsApp → Configuration. Leave blank to keep saved token.</div>
                                                @error('whatsapp_access_token')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold required">Webhook Verify Token</label>
                                                <input name="whatsapp_webhook_verify_token"
                                                       value="{{ old('whatsapp_webhook_verify_token', $school->setting('whatsapp_webhook_verify_token')) }}"
                                                       placeholder="Your custom verify token"
                                                       class="form-control form-control-solid @error('whatsapp_webhook_verify_token') is-invalid @enderror">
                                                <div class="text-muted fs-7 mt-1">Enter any secret token of your choice. Use this same value in Meta's webhook configuration.</div>
                                                @error('whatsapp_webhook_verify_token')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Your Webhook URL</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control form-control-solid font-monospace bg-light"
                                                           value="{{ $whatsappWebhookUrl }}" readonly id="webhookUrlField">
                                                    <button type="button" class="btn btn-light-primary"
                                                            onclick="navigator.clipboard.writeText(document.getElementById('webhookUrlField').value).then(()=>this.textContent='Copied!').catch(()=>{})">
                                                        Copy
                                                    </button>
                                                </div>
                                                <div class="text-muted fs-7 mt-1">
                                                    Paste this URL in Meta Developer Portal → WhatsApp → Configuration → Webhook URL.
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ki-duotone ki-check fs-4"><span class="path1"></span><span class="path2"></span></i>
                                    Save Settings
                                </button>
                                <a href="{{ url('admin') }}" class="btn btn-light">Cancel</a>
                            </div>
                            @endif
                        </div>

                    </div>{{-- end tab-content --}}
                </form>

                {{-- ── TAB: API KEYS (outside main form — has its own forms) ── --}}
                <div class="tab-pane-api {{ session('_settings_tab') === 'api' ? '' : 'd-none' }}" id="tab-api-outer">
                    @include('tenant.admin.settings.partials.api-keys')
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-3">
                <div class="card mb-5">
                    <div class="card-body py-5">
                        <div class="d-flex flex-center flex-column mb-4">
                            @if($school->logo_url)
                                <div class="border rounded-2 p-3 bg-light d-inline-flex mb-3">
                                    <img src="{{ $school->logo_url }}" alt="{{ $school->name }}"
                                         style="max-height:55px;max-width:140px;object-fit:contain;">
                                </div>
                            @else
                                <div class="symbol symbol-50px symbol-circle mb-3">
                                    <div class="symbol-label fw-bold fs-2 bg-light-primary text-primary">
                                        {{ strtoupper(substr($school->name, 0, 1)) }}
                                    </div>
                                </div>
                            @endif
                            <div class="fs-5 fw-bold text-gray-900">{{ $school->name }}</div>
                            <span class="badge font-monospace badge-light mt-1">{{ $school->code }}</span>
                        </div>
                        <div class="separator mb-4"></div>
                        @php
                            $meta = [
                                'Status'  => $school->status === 'active' ? 'Active' : 'Inactive',
                                'City'    => $school->city ?? '—',
                                'Logo'    => $school->logo ? 'Uploaded' : 'Not set',
                                'Created' => $school->created_at->format('d M Y'),
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

                {{-- Integration status --}}
                <div class="card">
                    <div class="card-header py-4 min-h-auto">
                        <h3 class="card-title fw-bold fs-7 text-uppercase text-muted">Integrations</h3>
                    </div>
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ki-duotone ki-sms fs-4 text-gray-500"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-7 fw-semibold text-gray-700">Custom Email</span>
                            </div>
                            @if($school->setting('smtp_enabled') && $school->setting('smtp_host'))
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light text-muted">Off</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ki-duotone ki-phone fs-4 text-gray-500"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-7 fw-semibold text-gray-700">Custom SMS</span>
                            </div>
                            @if($school->setting('sms_provider'))
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light text-muted">Off</span>
                            @endif
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="ki-duotone ki-whatsapp fs-4 text-gray-500"><span class="path1"></span><span class="path2"></span></i>
                                <span class="fs-7 fw-semibold text-gray-700">WhatsApp</span>
                            </div>
                            @if($school->setting('whatsapp_enabled'))
                                <span class="badge badge-light-success">Active</span>
                            @else
                                <span class="badge badge-light text-muted">Off</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle SMTP fields
document.getElementById('smtp_enabled_toggle').addEventListener('change', function () {
    document.getElementById('smtp_fields').classList.toggle('d-none', !this.checked);
});

// Show/hide SMS provider credential sections
document.getElementById('sms_provider_select').addEventListener('change', function () {
    document.getElementById('twilio_fields').classList.toggle('d-none', this.value !== 'twilio');
    document.getElementById('msegat_fields').classList.toggle('d-none', this.value !== 'msegat');
});

// Toggle WhatsApp fields
const whatsappToggle = document.getElementById('whatsapp_enabled');
if (whatsappToggle) {
    whatsappToggle.addEventListener('change', function () {
        document.getElementById('whatsapp_fields').classList.toggle('d-none', !this.checked);
    });
}

// Color picker hex display
document.querySelector('input[name="primary_color"]').addEventListener('input', function () {
    document.getElementById('color_hex_display').textContent = this.value;
});

// Logo preview
document.getElementById('logo_input').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('logo_preview').src = e.target.result;
        document.getElementById('logo_preview_wrapper').classList.remove('d-none');
    };
    reader.readAsDataURL(file);
});

// Remember active tab across saves — store in sessionStorage
const tabs = document.querySelectorAll('[data-bs-toggle="tab"]');
tabs.forEach(tab => {
    tab.addEventListener('shown.bs.tab', function (e) {
        sessionStorage.setItem('settings_active_tab', e.target.getAttribute('href'));
    });
});

// Restore tab from sessionStorage on page load
const savedTab = sessionStorage.getItem('settings_active_tab');
if (savedTab) {
    const el = document.querySelector('[href="' + savedTab + '"]');
    if (el && !el.classList.contains('js-api-tab-toggle')) {
        bootstrap.Tab.getOrCreateInstance(el).show();
    }
}

// API Keys tab — lives outside the main form, toggled manually
(function () {
    const apiTabLink  = document.querySelector('.js-api-tab-toggle');
    const apiTabOuter = document.getElementById('tab-api-outer');
    const mainTabContent = document.getElementById('settings-form').querySelector('.tab-content');

    if (!apiTabLink || !apiTabOuter) return;

    function showApiTab() {
        // Hide all Bootstrap tab panes
        mainTabContent.querySelectorAll('.tab-pane').forEach(p => {
            p.classList.remove('show', 'active');
        });
        // Deactivate all tab nav links
        document.querySelectorAll('.nav-link[data-bs-toggle="tab"], .nav-link.js-api-tab-toggle').forEach(l => {
            l.classList.remove('active');
        });
        // Show API tab outer div and mark link active
        apiTabOuter.classList.remove('d-none');
        apiTabLink.classList.add('active');
        sessionStorage.setItem('settings_active_tab', '#tab-api');
    }

    apiTabLink.addEventListener('click', function (e) {
        e.preventDefault();
        showApiTab();
    });

    // Bootstrap tabs hide the API outer div when other tabs are clicked
    document.querySelectorAll('[data-bs-toggle="tab"]').forEach(tab => {
        tab.addEventListener('show.bs.tab', function () {
            apiTabOuter.classList.add('d-none');
            apiTabLink.classList.remove('active');
        });
    });

    // Restore API tab on page load if flagged by session
    if ('{{ session('_settings_tab') }}' === 'api') {
        showApiTab();
    }
})();
</script>
@endpush
@endsection
