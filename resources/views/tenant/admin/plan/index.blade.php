@extends('layouts.tenant_admin')

@section('page_title', 'Plan & Features')

@push('page-title')
<div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
    data-kt-swapper="true" data-kt-swapper-mode="prepend"
    data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
    <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">Plan &amp; Features</h1>
</div>
@endpush

@section('content')
@php
    $planColors = ['starter' => 'secondary', 'growth' => 'info', 'pro' => 'primary', 'enterprise' => 'warning'];
    $color      = $planColors[$currentKey] ?? 'secondary';

    $features = [
        ['key' => 'feat_ai_analysis',      'label' => 'AI Issue Analysis'],
        ['key' => 'feat_ai_trends',        'label' => 'AI Trend Detection'],
        ['key' => 'feat_chatbot',          'label' => 'Public Chatbot'],
        ['key' => 'feat_broadcasting',     'label' => 'Broadcasting (SMS / Email)'],
        ['key' => 'feat_whatsapp',         'label' => 'WhatsApp Integration'],
        ['key' => 'feat_document_library', 'label' => 'Document Library & FAQs'],
        ['key' => 'feat_custom_smtp',      'label' => 'Custom SMTP'],
        ['key' => 'feat_reports_full',     'label' => 'Full Reports & Analytics'],
        ['key' => 'feat_csv_export',       'label' => 'CSV Export'],
        ['key' => 'feat_csat',             'label' => 'CSAT Surveys'],
        ['key' => 'feat_two_factor',       'label' => 'Two-Factor Auth (Admins & Managers)'],
        ['key' => 'feat_api_access',       'label' => 'REST API Access'],
    ];

    $supportEmail = config('mail.support_address', config('mail.from.address'));
@endphp

<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
<div class="container-xxl" id="kt_content_container">

    @include('partials.alerts')

    {{-- Page sub-header --}}
    <div class="mb-7">
        <span class="text-muted fs-6">Manage your subscription and see what's included.</span>
    </div>

    {{-- Top row: current plan + billing --}}
    <div class="row g-6 mb-7">

        {{-- Current plan card --}}
        <div class="col-12 col-lg-7">
            <div class="card h-100 border border-dashed border-{{ $color }}">
                <div class="card-header pt-5 pb-0 border-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge badge-light-{{ $color }} fs-6 fw-bold px-4 py-2">
                            {{ $current->label }}
                        </span>
                        @php $fp = $current->formattedPrice(); @endphp
                        <span class="fs-3 fw-bold text-gray-900">
                            @if($fp === '$0')
                                <span class="text-success">Free</span>
                            @elseif($fp !== null)
                                {{ $fp }}<span class="text-muted fs-7 fw-normal"> / month</span>
                            @else
                                <span class="text-muted fs-6 fw-normal">Custom pricing</span>
                            @endif
                        </span>
                        @if($current->tagline)
                            <span class="text-muted fs-7">— {{ $current->tagline }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body pt-5">
                    {{-- Count limits --}}
                    <div class="row g-3 mb-6">
                        @foreach([
                            ['label' => 'Branches',       'value' => $current->max_branches,         'icon' => 'ki-geolocation'],
                            ['label' => 'Staff Users',    'value' => $current->max_users,            'icon' => 'ki-people'],
                            ['label' => 'Contacts',       'value' => $current->max_contacts,         'icon' => 'ki-address-book'],
                            ['label' => 'Issues/Month',   'value' => $current->max_issues_per_month, 'icon' => 'ki-message-edit'],
                            ['label' => 'Chatbot/Day',    'value' => $current->feat_chatbot ? ($current->feat_chatbot_daily ?? null) : false, 'icon' => 'ki-message-text-2'],
                            ['label' => 'API Calls/Day', 'value' => $current->feat_api_access ? ($current->feat_api_daily_limit ?? null) : false, 'icon' => 'ki-abstract-26'],
                        ] as $lim)
                        <div class="col-6 col-sm-4 col-md">
                            <div class="bg-light rounded-2 p-3 text-center h-100">
                                <i class="ki-duotone {{ $lim['icon'] }} fs-2x text-{{ $color }} mb-1">
                                    <span class="path1"></span><span class="path2"></span>
                                </i>
                                <div class="fs-3 fw-bolder mt-1">
                                    @if($lim['value'] === null)
                                        <span class="text-success">∞</span>
                                    @elseif($lim['value'] === false || $lim['value'] === 0)
                                        <span class="text-danger fs-5">—</span>
                                    @else
                                        {{ number_format($lim['value']) }}
                                    @endif
                                </div>
                                <div class="text-muted fs-9 fw-semibold mt-1">{{ $lim['label'] }}</div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Feature checklist --}}
                    <div class="row g-2">
                        @foreach($features as $f)
                        @php $on = (bool) ($current->{$f['key']} ?? false); @endphp
                        <div class="col-12 col-sm-6">
                            <div class="d-flex align-items-center gap-2">
                                @if($on)
                                    <i class="ki-solid ki-check-circle fs-5 text-success flex-shrink-0"></i>
                                @else
                                    <i class="ki-solid ki-cross-circle fs-5 text-danger opacity-40 flex-shrink-0"></i>
                                @endif
                                <span class="fs-7 {{ $on ? 'text-gray-800 fw-semibold' : 'text-muted' }}">{{ $f['label'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing / contract card --}}
        <div class="col-12 col-lg-5">
            <div class="card h-100">
                <div class="card-header pt-5 pb-0 border-0">
                    <h5 class="card-title fw-bold fs-6 text-gray-800">Subscription & Contract</h5>
                </div>
                <div class="card-body pt-4">
                    <div class="d-flex flex-column gap-4">

                        {{-- Subscription period --}}
                        <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-dashed">
                            <div class="d-flex align-items-center gap-3">
                                <span class="symbol symbol-40px symbol-circle bg-light-{{ $color }} flex-shrink-0">
                                    <i class="ki-duotone ki-calendar fs-2 text-{{ $color }} ms-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <div>
                                    <div class="fw-semibold fs-7 text-gray-800">Subscription Period</div>
                                    <div class="text-muted fs-8">
                                        @if($tenant?->subscription_starts_at)
                                            From {{ $tenant->subscription_starts_at->format('M j, Y') }}
                                        @else
                                            Start date not set
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($tenant?->contract_type)
                                <span class="badge badge-light-secondary fw-bold">{{ ucfirst($tenant->contract_type) }}</span>
                            @endif
                        </div>

                        {{-- Renewal / expiry --}}
                        @php
                            $endsAt  = $tenant?->subscription_ends_at;
                            $expired = $tenant?->subscriptionExpired();
                            $daysLeft = $tenant?->subscriptionDaysRemaining();
                        @endphp
                        <div class="d-flex align-items-center justify-content-between py-3 border-bottom border-dashed">
                            <div class="d-flex align-items-center gap-3">
                                <span class="symbol symbol-40px symbol-circle bg-light-{{ $expired ? 'danger' : 'warning' }} flex-shrink-0">
                                    <i class="ki-duotone ki-calendar-tick fs-2 text-{{ $expired ? 'danger' : 'warning' }} ms-2">
                                        <span class="path1"></span><span class="path2"></span>
                                    </i>
                                </span>
                                <div>
                                    <div class="fw-semibold fs-7 text-gray-800">
                                        {{ $expired ? 'Subscription Expired' : 'Renews / Expires' }}
                                    </div>
                                    @if($endsAt)
                                        <div class="text-muted fs-8">
                                            @if($expired)
                                                Expired {{ $endsAt->diffForHumans() }}
                                            @elseif($daysLeft !== null && $daysLeft <= 30)
                                                <span class="text-warning fw-semibold">{{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} remaining</span>
                                            @else
                                                {{ $endsAt->diffForHumans() }}
                                            @endif
                                        </div>
                                    @else
                                        <div class="text-muted fs-8">Not set</div>
                                    @endif
                                </div>
                            </div>
                            @if($endsAt)
                                <span class="fw-bold fs-7 {{ $expired ? 'text-danger' : 'text-gray-800' }}">
                                    {{ $endsAt->format('M j, Y') }}
                                </span>
                            @else
                                <span class="text-muted fs-8">—</span>
                            @endif
                        </div>


                        {{-- Upgrade CTA --}}
                        <div class="mt-2 pt-2">
                            <p class="text-muted fs-8 mb-3">
                                Need more features or higher limits? Contact us at
                                <a href="mailto:{{ $supportEmail }}" class="fw-semibold">{{ $supportEmail }}</a>.
                            </p>
                            <a href="mailto:{{ $supportEmail }}?subject=Upgrade request — {{ $current->label }} plan"
                               class="btn btn-light-primary btn-sm w-100">
                                <i class="ki-duotone ki-rocket fs-5 me-1"><span class="path1"></span><span class="path2"></span></i>
                                Request Plan Upgrade
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Service Agreement / Contract --}}
    <div class="card mb-7">
        <div class="card-header pt-5 pb-0 border-0 d-flex align-items-center justify-content-between">
            <h5 class="card-title fw-bold fs-6 text-gray-800">Service Agreement</h5>
            @if($tenant?->terms_accepted_at)
                <a href="{{ route('tenant.admin.contract') }}" target="_blank" class="btn btn-sm btn-light-primary">
                    <i class="ki-duotone ki-file-down fs-5"><span class="path1"></span><span class="path2"></span></i>
                    View Full Contract &amp; Save as PDF
                </a>
            @endif
        </div>
        <div class="card-body pt-4">
            @if($tenant?->terms_accepted_at)
                <div class="d-flex flex-wrap gap-6">
                    <div class="d-flex align-items-center gap-3">
                        <span class="symbol symbol-35px symbol-circle bg-light-success flex-shrink-0">
                            <i class="ki-solid ki-shield-tick fs-4 text-success ms-2"></i>
                        </span>
                        <div>
                            <div class="fw-semibold fs-7 text-gray-800">Terms Accepted</div>
                            <div class="text-muted fs-8">{{ $tenant->terms_accepted_at->format('d M Y, H:i') }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="symbol symbol-35px symbol-circle bg-light-info flex-shrink-0">
                            <i class="ki-duotone ki-geolocation fs-4 text-info ms-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                        <div>
                            <div class="fw-semibold fs-7 text-gray-800">Accepted From</div>
                            <div class="text-muted fs-8">{{ $tenant->terms_accepted_ip ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="symbol symbol-35px symbol-circle bg-light-warning flex-shrink-0">
                            <i class="ki-duotone ki-document fs-4 text-warning ms-2"><span class="path1"></span><span class="path2"></span></i>
                        </span>
                        <div>
                            <div class="fw-semibold fs-7 text-gray-800">Agreement Version</div>
                            <div class="text-muted fs-8">v{{ $tenant->terms_accepted_version ?? '1.0' }} &bull; Ref: SA-{{ $tenant->id }}-{{ $tenant->terms_accepted_at->year }}</div>
                        </div>
                    </div>
                </div>

            @else
                <div class="d-flex align-items-center gap-4 p-5 bg-light-warning rounded-2">
                    <i class="ki-duotone ki-shield-cross fs-2x text-warning"><span class="path1"></span><span class="path2"></span></i>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-gray-900 mb-1">Terms &amp; Conditions not yet accepted</div>
                        <div class="text-muted fs-7">Please review and accept the Service Agreement to complete your account setup.</div>
                    </div>
                    <a href="{{ route('tenant.admin.terms') }}" class="btn btn-warning btn-sm text-nowrap">
                        Review &amp; Accept &rarr;
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Plan comparison table --}}
    <div class="card">
        <div class="card-header pt-5 pb-0 border-0">
            <h5 class="card-title fw-bold fs-6 text-gray-800">Compare All Plans</h5>
        </div>
        <div class="card-body pt-4">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center fs-7 mb-0" style="min-width:560px">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="text-start ps-4" style="width:200px">Feature</th>
                            @foreach($plans as $p)
                            @php $pc = $planColors[$p->key] ?? 'secondary'; @endphp
                            <th class="{{ $p->key === $currentKey ? 'bg-light-'.$pc : '' }} px-3">
                                <span class="badge badge-light-{{ $pc }} fw-bold px-3 py-2">{{ $p->label }}</span>
                                @if($p->key === $currentKey)
                                    <div class="text-muted fs-9 mt-1 fw-normal">your plan</div>
                                @endif
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Price --}}
                        <tr>
                            <td class="text-start ps-4 fw-semibold text-gray-700">Monthly Price</td>
                            @foreach($plans as $p)
                            @php $fp2 = $p->formattedPrice(); @endphp
                            <td class="{{ $p->key === $currentKey ? 'fw-bold bg-light-'.($planColors[$p->key] ?? 'secondary').' bg-opacity-25' : '' }}">
                                @if($fp2 === '$0') <span class="text-success fw-bold">Free</span>
                                @elseif($fp2 !== null) {{ $fp2 }}
                                @else <span class="text-muted fst-italic fs-8">Contact us</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        {{-- Count limits --}}
                        @foreach([
                            ['col' => 'max_branches',         'label' => 'Branches'],
                            ['col' => 'max_users',            'label' => 'Staff Users'],
                            ['col' => 'max_contacts',         'label' => 'Contacts'],
                            ['col' => 'max_issues_per_month', 'label' => 'Issues / Month'],
                        ] as $row)
                        <tr>
                            <td class="text-start ps-4 text-gray-700">{{ $row['label'] }}</td>
                            @foreach($plans as $p)
                            <td class="{{ $p->key === $currentKey ? 'fw-bold bg-light-'.($planColors[$p->key] ?? 'secondary').' bg-opacity-25' : '' }}">
                                @if($p->{$row['col']} === null)
                                    <span class="text-success fw-bold">∞</span>
                                @else
                                    {{ number_format($p->{$row['col']}) }}
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                        {{-- Chatbot daily --}}
                        <tr>
                            <td class="text-start ps-4 text-gray-700">Chatbot / Day</td>
                            @foreach($plans as $p)
                            <td class="{{ $p->key === $currentKey ? 'fw-bold bg-light-'.($planColors[$p->key] ?? 'secondary').' bg-opacity-25' : '' }}">
                                @if(! $p->feat_chatbot)
                                    <i class="ki-solid ki-cross-circle text-danger opacity-40 fs-5"></i>
                                @elseif($p->feat_chatbot_daily === null)
                                    <span class="text-success fw-bold">∞</span>
                                @else
                                    {{ $p->feat_chatbot_daily }}
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        {{-- API calls/day --}}
                        <tr>
                            <td class="text-start ps-4 text-gray-700">API Calls / Day</td>
                            @foreach($plans as $p)
                            <td class="{{ $p->key === $currentKey ? 'fw-bold bg-light-'.($planColors[$p->key] ?? 'secondary').' bg-opacity-25' : '' }}">
                                @if(! $p->feat_api_access)
                                    <i class="ki-solid ki-cross-circle text-danger opacity-40 fs-5"></i>
                                @elseif($p->feat_api_daily_limit === null)
                                    <span class="text-success fw-bold">∞</span>
                                @else
                                    {{ number_format($p->feat_api_daily_limit) }}
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        {{-- Boolean features --}}
                        @foreach($features as $f)
                        @if($f['key'] === 'feat_chatbot') @continue @endif
                        @if($f['key'] === 'feat_api_access') @continue @endif
                        <tr>
                            <td class="text-start ps-4 text-gray-700">{{ $f['label'] }}</td>
                            @foreach($plans as $p)
                            <td class="{{ $p->key === $currentKey ? 'bg-light-'.($planColors[$p->key] ?? 'secondary').' bg-opacity-25' : '' }}">
                                @if($p->{$f['key']})
                                    <i class="ki-solid ki-check-circle fs-5 text-success"></i>
                                @else
                                    <i class="ki-solid ki-cross-circle fs-5 text-danger opacity-40"></i>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>{{-- container-xxl --}}
</div>{{-- kt_content --}}
@endsection
