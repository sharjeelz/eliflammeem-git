@extends('layouts.tenant_admin')

@section('page_title', 'Terms & Conditions')

@section('content')
<div class="container-fluid py-8" style="max-width:860px;">

    {{-- Header --}}
    <div class="text-center mb-8">
        <h1 class="fs-2x fw-bold text-gray-900 mb-2">Terms &amp; Conditions</h1>
        <p class="text-muted fs-5">Please review and accept our Service Agreement to continue using {{ config('app.name') }}.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-6">{{ $errors->first() }}</div>
    @endif

    {{-- Contract summary box --}}
    <div class="card shadow-sm mb-6">
        <div class="card-header bg-light d-flex align-items-center justify-content-between">
            <span class="fw-bold text-gray-800">Service Agreement — {{ $school->name ?? 'Your School' }}</span>
            <a href="{{ route('tenant.admin.contract') }}" target="_blank" class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-file-down fs-5"><span class="path1"></span><span class="path2"></span></i>
                View Full Contract &amp; Save as PDF
            </a>
        </div>
        <div class="card-body p-0">
            <div style="height:420px; overflow-y:auto; padding:28px 32px; font-size:13.5px; line-height:1.75; color:#374151;">

                <p class="fw-bold mb-4">Summary of Key Terms</p>

                <p class="mb-3"><strong>1. Service.</strong> {{ config('app.name') }} provides a cloud-based school issue-tracking and communication platform. The school is granted a non-exclusive, non-transferable licence to use the platform for internal management purposes.</p>

                <p class="mb-3"><strong>2. Data Ownership.</strong> All data submitted by the school (issues, contacts, documents) remains the sole property of the school. {{ config('app.name') }} processes data solely to deliver the service and will not share it with third parties without consent, except as required by law.</p>

                <p class="mb-3"><strong>3. Privacy &amp; GDPR.</strong> The platform collects only data necessary to operate the service. Personal data of parents, teachers, and staff is handled in compliance with applicable data protection laws. IP addresses in chatbot logs are anonymised after 30 days and deleted after 90 days.</p>

                <p class="mb-3"><strong>4. Acceptable Use.</strong> The school agrees not to use the platform for unlawful purposes, to share access credentials, to attempt unauthorised access to other tenants' data, or to upload malicious content.</p>

                <p class="mb-3"><strong>5. Availability.</strong> We target 99.5% monthly uptime. Planned maintenance will be communicated 24 hours in advance. We are not liable for downtime caused by force majeure or third-party infrastructure failures.</p>

                <p class="mb-3"><strong>6. Subscription &amp; Payment.</strong> The service is billed according to the selected plan. Continued use after the renewal date constitutes acceptance of renewed terms. Non-payment may result in suspension after 14 days' notice.</p>

                <p class="mb-3"><strong>7. Liability.</strong> {{ config('app.name') }}'s total liability is limited to the fees paid in the preceding 3 months. We are not liable for indirect, consequential, or incidental damages.</p>

                <p class="mb-3"><strong>8. Termination.</strong> Either party may terminate with 30 days' written notice. Upon termination, the school may export its data within 14 days; after that, data will be securely deleted.</p>

                <p class="mb-3"><strong>9. Modifications.</strong> We may update these terms with 14 days' notice via email or in-app notification. Continued use after the notice period constitutes acceptance.</p>

                <p class="mb-3"><strong>10. Governing Law.</strong> These terms are governed by the laws of the jurisdiction in which {{ config('app.name') }} is registered. Disputes will be resolved through binding arbitration before resorting to litigation.</p>

                <p class="text-muted fs-7 mt-5">For the full legal document including all clauses, click "View Full Contract &amp; Save as PDF" above.</p>
            </div>
        </div>
    </div>

    {{-- Acceptance form --}}
    <div class="card shadow-sm">
        <div class="card-body p-8">
            <form method="POST" action="{{ route('tenant.admin.terms.accept') }}">
                @csrf

                <div class="form-check mb-6">
                    <input class="form-check-input @error('accept_terms') is-invalid @enderror"
                           type="checkbox" name="accept_terms" id="accept_terms" value="1"
                           {{ old('accept_terms') ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold fs-5 text-gray-800" for="accept_terms">
                        I have read and agree to the Terms &amp; Conditions on behalf of
                        <strong>{{ $school->name ?? 'my school' }}</strong>.
                        I understand this constitutes a legally binding agreement.
                    </label>
                    @error('accept_terms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex align-items-center gap-4">
                    <button type="submit" class="btn btn-primary btn-lg px-10">
                        <i class="ki-duotone ki-shield-tick fs-4 me-1"><span class="path1"></span><span class="path2"></span></i>
                        Accept &amp; Continue
                    </button>
                    <span class="text-muted fs-7">
                        Accepted by: <strong>{{ auth()->user()->name }}</strong> &bull; IP: {{ request()->ip() }} &bull; {{ now()->format('d M Y, H:i') }}
                    </span>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
