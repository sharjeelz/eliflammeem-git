<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Setup Wizard — {{ config('app.name', default: 'Schoolytics') }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('theme/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('theme/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        body { background: #f5f8fa; }
        .wizard-card { max-width: 720px; margin: 60px auto; }
        .step-indicator { display: flex; align-items: center; gap: 0; margin-bottom: 2rem; }
        .step-item { display: flex; align-items: center; flex: 1; }
        .step-circle {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 600; font-size: 14px; flex-shrink: 0;
        }
        .step-circle.done { background: #50cd89; color: #fff; }
        .step-circle.active { background: #009ef7; color: #fff; }
        .step-circle.pending { background: #e4e6ef; color: #a1a5b7; }
        .step-line { flex: 1; height: 2px; background: #e4e6ef; margin: 0 8px; }
        .step-line.done { background: #50cd89; }
        .step-label { font-size: 12px; font-weight: 600; margin-top: 4px; white-space: nowrap; }
        .terms-scroll-box {
            max-height: 400px;
            overflow-y: scroll;
            border: 1px solid #e4e6ef;
            border-radius: 8px;
            padding: 1.5rem;
            background: #fafafa;
            font-size: 14px;
            line-height: 1.7;
        }
        .terms-scroll-box h6 { font-weight: 700; margin-top: 1rem; margin-bottom: 0.25rem; color: #181c32; }
        .terms-scroll-box p, .terms-scroll-box ul { color: #5e6278; }
        .terms-scroll-box ul { padding-left: 1.25rem; }
    </style>
</head>
<body>

<div class="wizard-card">
    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="mb-4">
            <span class="svg-icon svg-icon-3tx svg-icon-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none">
                    <path d="M3 8L12 3L21 8V21H15V15H9V21H3V8Z" fill="currentColor" opacity="0.3"/>
                    <path d="M12 3L21 8H3L12 3Z" fill="currentColor"/>
                </svg>
            </span>
        </div>
        <h2 class="fw-bolder fs-1 text-dark mb-1">Welcome to Schoolytics!</h2>
        <p class="text-muted fs-6">Let's get your school set up in just a few steps.</p>
    </div>

    {{-- Step Indicator --}}
    <div class="step-indicator px-4 mb-8">
        {{-- Step 1 --}}
        <div class="d-flex flex-column align-items-center" style="flex-shrink:0">
            <div class="step-circle {{ $step > 1 ? 'done' : ($step === 1 ? 'active' : 'pending') }}">
                @if($step > 1)
                    <i class="bi bi-check-lg text-white" style="font-size:16px"></i>
                @else
                    1
                @endif
            </div>
            <div class="step-label text-{{ $step === 1 ? 'primary' : ($step > 1 ? 'success' : 'muted') }}">School Profile</div>
        </div>
        <div class="step-line {{ $step > 1 ? 'done' : '' }}" style="margin-bottom: 18px"></div>
        {{-- Step 2 --}}
        <div class="d-flex flex-column align-items-center" style="flex-shrink:0">
            <div class="step-circle {{ $step > 2 ? 'done' : ($step === 2 ? 'active' : 'pending') }}">
                @if($step > 2)
                    <i class="bi bi-check-lg text-white" style="font-size:16px"></i>
                @else
                    2
                @endif
            </div>
            <div class="step-label text-{{ $step === 2 ? 'primary' : ($step > 2 ? 'success' : 'muted') }}">Terms &amp; Conditions</div>
        </div>
        <div class="step-line {{ $step > 2 ? 'done' : '' }}" style="margin-bottom: 18px"></div>
        {{-- Step 3 --}}
        <div class="d-flex flex-column align-items-center" style="flex-shrink:0">
            <div class="step-circle {{ $step === 3 ? 'active' : 'pending' }}">3</div>
            <div class="step-label text-{{ $step === 3 ? 'primary' : 'muted' }}">Done</div>
        </div>
    </div>

    {{-- Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible mb-6">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mb-6">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Card --}}
    <div class="card shadow-sm">
        <div class="card-body p-8">

            @if($step === 1)
            {{-- ─── Step 1: School Profile ─── --}}
            <h4 class="fw-bold mb-2">Step 1 — School Profile</h4>
            <p class="text-muted mb-6">Fill in your school's basic information. You can always update this later in Settings.</p>

            <form method="POST" action="{{ route('tenant.admin.onboarding.profile') }}" enctype="multipart/form-data">
                @csrf

                <div class="row g-5">
                    <div class="col-12">
                        <label class="form-label required fw-semibold">School Name</label>
                        <input type="text" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror"
                               value="{{ old('name', $school->name) }}" placeholder="e.g. Greenwood International School" required />
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror"
                               value="{{ old('city', $school->city) }}" placeholder="e.g. Riyadh" />
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Contact Phone</label>
                        <input type="text" name="contact_phone" class="form-control @error('contact_phone') is-invalid @enderror"
                               value="{{ old('contact_phone', $school->settings['contact_phone'] ?? '') }}" placeholder="+966 5x xxxx xxxx" />
                        @error('contact_phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Contact Email</label>
                        <input type="email" name="contact_email" class="form-control @error('contact_email') is-invalid @enderror"
                               value="{{ old('contact_email', $school->settings['contact_email'] ?? '') }}" placeholder="admin@school.edu" />
                        @error('contact_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" class="form-control @error('address') is-invalid @enderror"
                               value="{{ old('address', $school->settings['address'] ?? '') }}" placeholder="Full address" />
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">School Logo</label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror" accept="image/*" />
                        @error('logo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">JPG, PNG or SVG. Max 2 MB. Displayed on your public portal.</div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-8">
                    <button type="submit" class="btn btn-primary btn-lg px-8">
                        Save &amp; Continue <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            @elseif($step === 2)
            {{-- ─── Step 2: Terms & Conditions ─── --}}
            <h4 class="fw-bold mb-2">Step 2 — Terms &amp; Conditions</h4>
            <p class="text-muted mb-6">
                Please read the key terms of your Schoolytics Service Agreement below, then accept to continue.
                You can also <a href="{{ route('tenant.admin.contract') }}" target="_blank" class="fw-semibold">view the full contract</a> which will be saved to your account.
            </p>

            <div class="terms-scroll-box mb-6">
                <p class="fw-bold text-dark mb-1" style="font-size:15px;">Schoolytics Service Agreement — Key Terms Summary</p>
                <p class="text-muted" style="font-size:13px;">Last updated: March 2026 &nbsp;|&nbsp; Version 1.0</p>

                <h6>1. Service Description</h6>
                <p>Schoolytics provides a cloud-hosted school issue tracking and parent communication platform. The platform enables issue submission by parents and teachers, staff assignment and tracking, AI-powered analysis, reporting dashboards, and optional modules (chatbot, broadcasting, WhatsApp integration) depending on your plan.</p>

                <h6>2. Authorised Use</h6>
                <p>The platform is licensed exclusively to accredited educational institutions. Use of the platform is restricted to legitimate school communication and issue management purposes. Resale or sublicensing to third parties is not permitted.</p>

                <h6>3. Data Ownership</h6>
                <p>Your school retains full ownership of all data submitted through the platform — including issue reports, contact information, messages, and attachments. Schoolytics does not claim any rights over your school's data. You may request a full data export at any time.</p>

                <h6>4. Data Security</h6>
                <p>We implement industry-standard security measures including encrypted storage, role-based access controls, and regular backups. You are responsible for safeguarding your staff login credentials and the parent/teacher access codes your school distributes. Do not share credentials.</p>

                <h6>5. AI Features</h6>
                <p>The platform uses artificial intelligence to analyse issues and surface insights such as sentiment, urgency, and emerging trends. AI outputs are <strong>advisory only</strong> — they do not constitute professional, legal, medical, or psychological advice. All decisions made based on AI recommendations remain the sole responsibility of your school.</p>

                <h6>6. Payment &amp; Subscription</h6>
                <ul>
                    <li>Fees are based on your selected plan at the time of sign-up.</li>
                    <li>Subscriptions auto-renew at the end of each billing period unless cancelled.</li>
                    <li>Payment is due on the 1st of each billing period. Late payment may result in service suspension after 7 days.</li>
                    <li>Plan tier features may be updated with 30 days advance notice.</li>
                </ul>

                <h6>7. Acceptable Use</h6>
                <p>You agree not to use the platform to send spam, harass users, publish illegal content, attempt to access other schools' data, or reverse-engineer any part of the service. Violations may result in immediate account suspension.</p>

                <h6>8. Termination</h6>
                <p>Either party may terminate this agreement with 30 days written notice. Upon termination, your school data will remain available for export for 14 days before being permanently deleted from our servers.</p>

                <h6>9. Uptime &amp; Support</h6>
                <p>We target 99.5% monthly uptime. Scheduled maintenance will be communicated in advance. Support is available via email during business hours.</p>

                <h6>10. Limitation of Liability</h6>
                <p>The service is provided on an "as-is" basis. Schoolytics' total liability for any claim shall not exceed the fees paid in the 3 months preceding the claim. We are not liable for indirect, consequential, or incidental damages.</p>

                <h6>11. Governing Law</h6>
                <p>This agreement is governed by applicable local law. Disputes shall first be attempted through good-faith negotiation, and if unresolved, through binding arbitration.</p>

                <h6>12. Updates to These Terms</h6>
                <p>We may update these terms with reasonable notice. Continued use of the platform after the effective date of changes constitutes acceptance of the revised terms.</p>
            </div>

            <div class="d-flex align-items-start gap-3 mb-2">
                <a href="{{ route('tenant.admin.contract') }}" target="_blank" class="btn btn-light btn-sm">
                    <i class="bi bi-file-earmark-text me-1"></i> View Full Contract
                </a>
            </div>

            <form method="POST" action="{{ route('tenant.admin.onboarding.terms') }}" class="mt-4">
                @csrf

                <div class="form-check mb-6">
                    <input class="form-check-input @error('accept_terms') is-invalid @enderror"
                           type="checkbox" name="accept_terms" id="accept_terms" value="1"
                           {{ old('accept_terms') ? 'checked' : '' }} />
                    <label class="form-check-label fw-semibold" for="accept_terms">
                        I have read and agree to the Terms of Service and the Service Agreement above.
                    </label>
                    @error('accept_terms')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary btn-lg px-8">
                        Accept &amp; Continue <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </form>

            @elseif($step === 3)
            {{-- ─── Step 3: Done ─── --}}
            <div class="text-center py-6">
                <div class="mb-6">
                    <span class="svg-icon svg-icon-5tx text-success">
                        <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" fill="#e8fff3"/>
                            <path d="M7 12.5L10 15.5L17 8.5" stroke="#50cd89" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>
                <h3 class="fw-bolder fs-2 text-dark mb-2">Step 3 — Done!</h3>
                <p class="text-muted fs-6 mb-8">
                    Your school profile is configured and your agreement has been signed. Here's what you can do next:
                </p>

                <div class="row g-4 text-start mb-8">
                    <div class="col-md-4">
                        <div class="card border h-100 p-4">
                            <i class="bi bi-people-fill text-primary fs-2 mb-3"></i>
                            <h6 class="fw-bold mb-1">Import Contacts</h6>
                            <p class="text-muted fs-7 mb-0">Add parents and teachers via CSV import or manually.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border h-100 p-4">
                            <i class="bi bi-diagram-3-fill text-warning fs-2 mb-3"></i>
                            <h6 class="fw-bold mb-1">Set Up Branches</h6>
                            <p class="text-muted fs-7 mb-0">Organise your school into campuses or departments.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border h-100 p-4">
                            <i class="bi bi-shield-check-fill text-success fs-2 mb-3"></i>
                            <h6 class="fw-bold mb-1">Invite Staff</h6>
                            <p class="text-muted fs-7 mb-0">Create user accounts and assign roles to your team.</p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <a href="{{ route('tenant.admin.contract') }}" target="_blank" class="btn btn-light btn-sm">
                        <i class="bi bi-file-earmark-text me-1"></i> View Your Signed Contract
                    </a>
                </div>

                <form method="POST" action="{{ route('tenant.admin.onboarding.complete') }}">
                    @csrf
                    <button type="submit" class="btn btn-success btn-lg px-10">
                        Go to Dashboard <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>

    <div class="text-center mt-4 text-muted fs-7">
        {{ config('app.name', default: 'Schoolytics') }} &mdash; School Management Platform
    </div>
</div>

<script src="{{ asset('theme/plugins/global/plugins.bundle.js') }}"></script>
<script src="{{ asset('theme/js/scripts.bundle.js') }}"></script>
</body>
</html>
