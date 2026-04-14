@extends('layouts.tenant_admin')

@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="container-xxl" id="kt_content_container">

        @include('partials.alerts')

        @push('page-title')
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">My Profile</h1>
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted"><a href="{{ url('admin') }}" class="text-muted text-hover-primary">Main</a></li>
                <li class="breadcrumb-item text-gray-900">My Profile</li>
            </ul>
        </div>
        @endpush

        <div class="row g-6">

            {{-- Forms --}}
            <div class="col-lg-7">

                {{-- Profile info --}}
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Profile Information</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.admin.profile.update') }}">
                            @csrf @method('PUT')
                            <div class="row g-4">

                                <div class="col-md-6">
                                    <label class="form-label required">Full Name</label>
                                    <input name="name" value="{{ old('name', $user->name) }}"
                                        class="form-control form-control-solid @error('name') is-invalid @enderror" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">Email</label>
                                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                        class="form-control form-control-solid @error('email') is-invalid @enderror" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Phone Number</label>
                                    <input name="phone_number" value="{{ old('phone_number', $user->phone_number) }}"
                                        class="form-control form-control-solid @error('phone_number') is-invalid @enderror"
                                        placeholder="+1 555 0100">
                                    @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Address</label>
                                    <input name="address" value="{{ old('address', $user->address) }}"
                                        class="form-control form-control-solid @error('address') is-invalid @enderror">
                                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

                {{-- Change password --}}
                <div class="card mt-6">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.admin.profile.password') }}">
                            @csrf @method('PUT')
                            <div class="row g-4">

                                <div class="col-12">
                                    <label class="form-label required">Current Password</label>
                                    <input type="password" name="current_password"
                                        class="form-control form-control-solid @error('current_password') is-invalid @enderror"
                                        autocomplete="current-password">
                                    @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">New Password</label>
                                    <input type="password" name="password"
                                        class="form-control form-control-solid @error('password') is-invalid @enderror"
                                        autocomplete="new-password">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label required">Confirm New Password</label>
                                    <input type="password" name="password_confirmation"
                                        class="form-control form-control-solid"
                                        autocomplete="new-password">
                                </div>

                                <div class="col-12 pt-2">
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>

                            </div>
                        </form>
                    </div>
                </div>

            </div>

            {{-- Info sidebar --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body text-center py-10">

                        <div class="symbol symbol-100px symbol-circle mx-auto mb-5">
                            <div class="symbol-label fs-1 fw-bold text-white"
                                 style="background: linear-gradient(135deg,#4338ca,#6366f1)">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        </div>

                        <div class="fs-3 fw-bold text-gray-900 mb-1">{{ $user->name }}</div>
                        <div class="text-muted mb-3 text-break">{{ $user->email }}</div>

                        @php
                            $role = $user->getRoleNames()->first();
                            $roleColor = ['admin' => 'danger', 'branch_manager' => 'warning', 'staff' => 'primary'][$role] ?? 'secondary';
                        @endphp
                        <span class="badge badge-light-{{ $roleColor }} fs-7 fw-bold mb-6">
                            {{ ucfirst(str_replace('_', ' ', $role)) }}
                        </span>

                        <div class="separator separator-dashed mb-5"></div>

                        <div class="text-start">
                            @if($user->phone_number)
                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-phone fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                                <span class="text-gray-700">{{ $user->phone_number }}</span>
                            </div>
                            @endif

                            @if($user->address)
                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-geolocation fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                                <span class="text-gray-700">{{ $user->address }}</span>
                            </div>
                            @endif

                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-calendar fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                                <span class="text-gray-600 fs-7">Member since {{ $user->created_at->format('M Y') }}</span>
                            </div>

                            @if($user->last_login)
                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-time fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                                <div>
                                    <span class="text-gray-600 fs-7">Last login {{ $user->last_login->diffForHumans() }}</span>
                                    @if($user->last_login_ip)
                                        <br><span class="text-muted fs-8">IP: {{ $user->last_login_ip }}</span>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if($user->login_count)
                            <div class="d-flex align-items-center">
                                <i class="ki-duotone ki-shield-tick fs-3 text-muted me-3"><span class="path1"></span><span class="path2"></span></i>
                                <span class="text-gray-600 fs-7">{{ number_format($user->login_count) }} total logins</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if($user->account_id)
                <div class="card mt-6">
                    <div class="card-body">
                        <div class="text-muted fs-7 mb-1">Account ID</div>
                        <div class="fw-bold font-monospace text-gray-700">{{ $user->account_id }}</div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Two-Factor Authentication (admin + branch_manager only, plan-gated) --}}
            <div class="col-lg-7">
            @if($user->hasAnyRole(['admin', 'branch_manager']) && ($planAllow2fa ?? false))
            @php
                $tfaEnabled   = (bool) $user->two_factor_secret;
                $tfaConfirmed = $user->hasEnabledTwoFactorAuthentication();
            @endphp
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">Two-Factor Authentication</h3>
                    <div class="card-toolbar">
                        @if($tfaConfirmed)
                            <span class="badge badge-light-success fs-7">Active</span>
                        @elseif($tfaEnabled)
                            <span class="badge badge-light-warning fs-7">Setup Pending</span>
                        @else
                            <span class="badge badge-light-secondary fs-7">Disabled</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">

                    {{-- Inline flash messages for 2FA actions --}}
                    @if(session('2fa_status') === 'confirmed')
                        <div class="alert alert-success d-flex align-items-center p-4 mb-6">
                            <i class="ki-solid ki-shield-tick fs-2x text-success me-3"></i>
                            <div><strong>2FA is now active!</strong> Save your recovery codes below in case you lose access to your authenticator app.</div>
                        </div>
                    @elseif(session('2fa_status') === 'setup')
                        <div class="alert alert-info d-flex align-items-center p-4 mb-6">
                            <i class="ki-solid ki-information-5 fs-2x text-info me-3"></i>
                            <div>Scan the QR code with your authenticator app, then enter the 6-digit code to complete setup.</div>
                        </div>
                    @elseif(session('2fa_status') === 'disabled')
                        <div class="alert alert-warning d-flex align-items-center p-4 mb-6">
                            <i class="ki-solid ki-shield-cross fs-2x text-warning me-3"></i>
                            <div><strong>2FA disabled.</strong> Your account no longer requires an authenticator code at login.</div>
                        </div>
                    @elseif(session('2fa_status') === 'codes_regenerated')
                        <div class="alert alert-success d-flex align-items-center p-4 mb-6">
                            <i class="ki-solid ki-shield-tick fs-2x text-success me-3"></i>
                            <div><strong>Recovery codes regenerated.</strong> Your old codes are now invalid — save the new ones below.</div>
                        </div>
                    @endif

                    @if(! $tfaEnabled)
                        {{-- ── State 1: Not set up ── --}}
                        <p class="text-muted mb-5">
                            Two-factor authentication adds an extra layer of security to your account.
                            Once enabled, you'll need to enter a 6-digit code from your authenticator app
                            (Google Authenticator, Authy, etc.) each time you sign in.
                        </p>
                        <form method="POST" action="{{ route('tenant.admin.two-factor.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-duotone ki-shield-tick fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Enable Two-Factor Authentication
                            </button>
                        </form>

                    @elseif(! $tfaConfirmed)
                        {{-- ── State 2: Secret generated, awaiting confirmation ── --}}
                        <p class="text-gray-700 mb-4">
                            Scan the QR code below with your authenticator app, then enter the 6-digit code to complete setup.
                        </p>

                        <div class="text-center my-5 p-5 bg-light rounded d-inline-block">
                            {!! $user->twoFactorQrCodeSvg() !!}
                        </div>

                        @php
                            try { $manualKey = decrypt($user->two_factor_secret); } catch (\Exception) { $manualKey = null; }
                        @endphp
                        @if($manualKey)
                        <div class="mb-5">
                            <p class="text-muted fs-7 mb-1">Can't scan? Enter this setup key manually in your authenticator app:</p>
                            <code class="fs-7 user-select-all">{{ $manualKey }}</code>
                        </div>
                        @endif

                        <form method="POST" action="{{ route('tenant.admin.two-factor.confirm') }}" class="mt-5">
                            @csrf
                            <div class="mb-4">
                                <label class="form-label required fw-semibold">Authentication Code</label>
                                <input type="text" name="code" inputmode="numeric" maxlength="8"
                                       placeholder="000 000" autofocus
                                       style="max-width: 180px; letter-spacing: 0.2em;"
                                       class="form-control form-control-solid text-center font-monospace
                                              {{ $errors->has('2fa_code') ? 'is-invalid' : '' }}">
                                @if($errors->has('2fa_code'))
                                    <div class="invalid-feedback d-block">{{ $errors->first('2fa_code') }}</div>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="ki-duotone ki-shield-tick fs-3 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Confirm &amp; Activate
                            </button>
                        </form>

                    @else
                        {{-- ── State 3: Fully active ── --}}
                        <div class="d-flex align-items-center mb-6 p-4 rounded" style="background: #e8fff3;">
                            <i class="ki-solid ki-shield-tick fs-2x text-success me-3"></i>
                            <div>
                                <div class="fw-bold text-gray-900">Two-factor authentication is active</div>
                                <div class="text-muted fs-7">You'll be asked for a code from your authenticator app each time you sign in.</div>
                            </div>
                        </div>

                        {{-- Recovery codes --}}
                        <div class="mb-7">
                            <h5 class="fw-bold mb-1">Recovery Codes</h5>
                            <p class="text-muted fs-7 mb-3">
                                If you lose access to your authenticator app, use one of these one-time recovery codes to regain access.
                                Store them somewhere safe (e.g. a password manager).
                            </p>
                            @php
                                try {
                                    $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
                                } catch (\Exception) {
                                    $recoveryCodes = [];
                                }
                            @endphp
                            <div class="bg-light rounded p-4 font-monospace fs-7">
                                @if($recoveryCodes)
                                    <div class="row g-1">
                                        @foreach($recoveryCodes as $rc)
                                            <div class="col-6">{{ $rc }}</div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-muted">No recovery codes available. Regenerate below.</span>
                                @endif
                            </div>
                        </div>

                        {{-- Regenerate codes --}}
                        <div class="mb-6">
                            <h6 class="fw-semibold mb-1">Regenerate Recovery Codes</h6>
                            <p class="text-muted fs-7 mb-3">Generating new codes will immediately invalidate all existing ones.</p>
                            <form method="POST" action="{{ route('tenant.admin.two-factor.recovery-codes') }}">
                                @csrf
                                <div class="d-flex gap-2 align-items-start flex-wrap">
                                    <input type="password" name="password" placeholder="Confirm your password"
                                           class="form-control form-control-solid {{ $errors->has('2fa_password_regen') ? 'is-invalid' : '' }}"
                                           style="max-width: 260px;">
                                    <button type="submit" class="btn btn-light-primary">Regenerate</button>
                                </div>
                                @if($errors->has('2fa_password_regen'))
                                    <div class="text-danger fs-7 mt-1">{{ $errors->first('2fa_password_regen') }}</div>
                                @endif
                            </form>
                        </div>

                        <div class="separator separator-dashed mb-6"></div>

                        {{-- Disable 2FA --}}
                        <div>
                            <h6 class="fw-bold text-danger mb-1">Disable Two-Factor Authentication</h6>
                            <p class="text-muted fs-7 mb-3">Removing 2FA will make your account less secure.</p>
                            <form method="POST" action="{{ route('tenant.admin.two-factor.disable') }}">
                                @csrf
                                <div class="d-flex gap-2 align-items-start flex-wrap">
                                    <input type="password" name="password" placeholder="Confirm your password"
                                           class="form-control form-control-solid {{ $errors->has('2fa_password') ? 'is-invalid' : '' }}"
                                           style="max-width: 260px;">
                                    <button type="submit" class="btn btn-light-danger">Disable 2FA</button>
                                </div>
                                @if($errors->has('2fa_password'))
                                    <div class="text-danger fs-7 mt-1">{{ $errors->first('2fa_password') }}</div>
                                @endif
                            </form>
                        </div>
                    @endif

                </div>
            </div>
            @elseif($user->hasAnyRole(['admin', 'branch_manager']))
            {{-- Plan upgrade notice --}}
            <div class="card mt-6">
                <div class="card-header">
                    <h3 class="card-title">Two-Factor Authentication</h3>
                    <div class="card-toolbar">
                        <span class="badge badge-light-secondary fs-7">Not available</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-start gap-4">
                        <i class="ki-duotone ki-lock-2 fs-2x text-muted mt-1">
                            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
                        </i>
                        <div>
                            <p class="fw-semibold text-gray-700 mb-2">
                                Two-factor authentication is not included in your current plan.
                            </p>
                            <p class="text-muted fs-7 mb-4">
                                Upgrade to Growth or higher to enable 2FA for admin and manager accounts.
                            </p>
                            <a href="{{ route('tenant.admin.plan.index') }}" class="btn btn-sm btn-light-primary">
                                <i class="ki-duotone ki-rocket fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>
                                View Plans
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            </div>{{-- /col-lg-7 --}}

        </div>
    </div>
</div>
@endsection
