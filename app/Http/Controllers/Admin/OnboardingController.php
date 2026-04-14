<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    /** Show the current onboarding step. */
    public function show(): View
    {
        $school = School::where('tenant_id', tenant('id'))->firstOrFail();
        $step   = $this->currentStep();

        return view('tenant.admin.onboarding.wizard', compact('school', 'step'));
    }

    /** Step 1 — save school profile and advance. */
    public function saveProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'city'          => ['nullable', 'string', 'max:100'],
            'address'       => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'logo'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
        ]);

        $school = School::where('tenant_id', tenant('id'))->firstOrFail();

        // Merge submitted contact fields into the existing settings JSON
        $settings = is_array($school->settings) ? $school->settings : [];
        $settings['contact_email'] = $request->input('contact_email', '');
        $settings['contact_phone'] = $request->input('contact_phone', '');
        $settings['address']       = $request->input('address', '');

        $updates = [
            'name'     => $request->input('name'),
            'city'     => $request->input('city'),
            'settings' => $settings,
        ];

        if ($request->hasFile('logo')) {
            $updates['logo'] = $request->file('logo')->store('logos', 'logos');
        }

        $school->update($updates);

        // Advance registration_status from 'pending' → 'profile_complete'
        tenancy()->tenant->update(['registration_status' => 'profile_complete']);

        return redirect()->route('tenant.admin.onboarding')->with('success', 'School profile saved!');
    }

    /** Step 2 — accept Terms & Conditions and advance. */
    public function acceptTerms(Request $request): RedirectResponse
    {
        $request->validate([
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'You must accept the Terms & Conditions to continue.',
            'accept_terms.accepted' => 'You must accept the Terms & Conditions to continue.',
        ]);

        $tenant = tenancy()->tenant;
        $school = \App\Models\School::where('tenant_id', $tenant->id)->first();

        // Build the contract URL for this tenant domain
        $domain   = $tenant->domains()->first();
        $protocol = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
        $port     = parse_url(config('app.url'), PHP_URL_PORT);
        $contractUrl = $domain
            ? "{$protocol}://{$domain->domain}" . ($port ? ":{$port}" : '') . '/admin/contract'
            : url('/admin/contract');

        $tenant->update([
            'registration_status'    => 'terms_accepted',
            'terms_accepted_at'      => now(),
            'terms_accepted_ip'      => $request->ip(),
            'terms_accepted_version' => '1.0',
            'contract_file_url'      => $contractUrl,
        ]);

        return redirect()->route('tenant.admin.onboarding')->with('success', 'Terms accepted. Your contract has been recorded.');
    }

    /** Step 3 — final confirmation, mark onboarding complete. */
    public function complete(Request $request): RedirectResponse
    {
        tenancy()->tenant->update(['registration_status' => 'completed']);

        return redirect()->route('tenant.admin.dashboard')
            ->with('ok', 'Setup complete! Welcome to Schoolytics.');
    }

    /** Contract/agreement view — public (no auth required so Nova superadmin can open it directly). */
    public function contract(): \Illuminate\View\View
    {
        $tenant = tenancy()->tenant;
        $school = \App\Models\School::where('tenant_id', $tenant->id)->first();

        // Use the logged-in tenant user, or fall back to the first admin of this tenant
        // so the "Electronically accepted by" field is always populated.
        $admin = auth('web')->user()
            ?? \App\Models\User::where('tenant_id', $tenant->id)
                ->role('admin')
                ->orderBy('created_at')
                ->first();

        return view('tenant.admin.onboarding.contract', compact('tenant', 'school', 'admin'));
    }

    /**
     * Standalone T&C acceptance page for existing tenants who skipped onboarding.
     * Shown via the dashboard banner.
     */
    public function termsPage(): \Illuminate\View\View
    {
        $tenant = tenancy()->tenant;
        $school = \App\Models\School::where('tenant_id', $tenant->id)->first();
        $admin  = auth()->user();

        return view('tenant.admin.onboarding.terms_standalone', compact('tenant', 'school', 'admin'));
    }

    /**
     * POST handler for the standalone terms acceptance (existing tenants only).
     * Does NOT change registration_status — just stamps acceptance fields.
     */
    public function acceptTermsStandalone(Request $request): RedirectResponse
    {
        $request->validate([
            'accept_terms' => ['required', 'accepted'],
        ], [
            'accept_terms.required' => 'You must accept the Terms & Conditions to continue.',
            'accept_terms.accepted' => 'You must accept the Terms & Conditions to continue.',
        ]);

        $tenant = tenancy()->tenant;

        // Already accepted — nothing to do
        if ($tenant->terms_accepted_at) {
            return redirect()->route('tenant.admin.dashboard');
        }

        $domain      = $tenant->domains()->first();
        $protocol    = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https';
        $port        = parse_url(config('app.url'), PHP_URL_PORT);
        $contractUrl = $domain
            ? "{$protocol}://{$domain->domain}" . ($port ? ":{$port}" : '') . '/admin/contract'
            : url('/admin/contract');

        $tenant->update([
            'terms_accepted_at'      => now(),
            'terms_accepted_ip'      => $request->ip(),
            'terms_accepted_version' => '1.0',
            'contract_file_url'      => $contractUrl,
        ]);

        return redirect()->route('tenant.admin.dashboard')
            ->with('ok', 'Terms & Conditions accepted. Your contract has been recorded.');
    }

    // -------------------------------------------------------------------------

    private function currentStep(): int
    {
        return match (tenancy()->tenant->registration_status) {
            'profile_complete' => 2,
            'terms_accepted'   => 3,
            default            => 1,
        };
    }
}
