<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Services\PlanService;
use App\Services\TenantMailer;
use App\Services\TenantSms;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SchoolSettingsController extends Controller
{
    public function edit(): View
    {
        $school           = School::where('tenant_id', tenant('id'))->firstOrFail();
        $plan             = PlanService::forCurrentTenant();
        $planAllowChatbot = $plan->allows('chatbot');

        // Auto-generate an opaque webhook ID for this tenant if not already set.
        // This is used in the webhook URL instead of the raw tenant UUID to prevent
        // tenant enumeration via attacker-controlled query params (VULN-005).
        if (! $school->setting('whatsapp_webhook_id')) {
            $settings = $school->settings ?? [];
            $settings['whatsapp_webhook_id'] = Str::random(40);
            $school->update(['settings' => $settings]);
        }

        $whatsappWebhookUrl = url('/whatsapp/webhook/' . $school->setting('whatsapp_webhook_id'));

        return view('tenant.admin.settings.index', compact('school', 'planAllowChatbot', 'whatsappWebhookUrl'));
    }

    public function update(Request $request): RedirectResponse
    {
        $school = School::where('tenant_id', tenant('id'))->firstOrFail();
        $plan = PlanService::forCurrentTenant();

        $smtpEnabled = $request->boolean('smtp_enabled');
        $smsProvider = $request->input('sms_provider'); // 'twilio' | 'msegat' | null

        // Plan guards — silently block saving restricted settings
        if ($smtpEnabled && ! $plan->allows('custom_smtp')) {
            $smtpEnabled = false;
            $request->merge(['smtp_enabled' => false]);
        }
        if ($smsProvider && ! $plan->allows('broadcasting')) {
            $smsProvider = null;
            $request->merge(['sms_provider' => null]);
        }
        if ($request->boolean('whatsapp_enabled') && ! $plan->allows('whatsapp')) {
            $request->merge(['whatsapp_enabled' => false]);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'city' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,svg,webp', 'max:2048'],
            'address' => ['nullable', 'string', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'welcome_message' => ['nullable', 'string', 'max:300'],
            'thankyou_message' => ['nullable', 'string', 'max:300'],
            'primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sms_provider' => ['nullable', 'in:twilio,msegat'],

            // SMTP — required when enabled
            'smtp_host' => [$smtpEnabled ? 'required' : 'nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_username' => [$smtpEnabled ? 'required' : 'nullable', 'string', 'max:255'],
            'smtp_password' => [
                Rule::requiredIf($smtpEnabled && ! $school->setting('smtp_password')),
                'nullable', 'string', 'max:500',
            ],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,'],
            'smtp_from_address' => [$smtpEnabled ? 'required' : 'nullable', 'email', 'max:150'],
            'smtp_from_name' => ['nullable', 'string', 'max:100'],

            // Twilio — required when selected
            'twilio_sid' => [$smsProvider === 'twilio' ? 'required' : 'nullable', 'string', 'max:100'],
            'twilio_token' => [
                Rule::requiredIf($smsProvider === 'twilio' && ! $school->setting('twilio_token')),
                'nullable', 'string', 'max:255',
            ],
            'twilio_from' => [$smsProvider === 'twilio' ? 'required' : 'nullable', 'string', 'max:20'],

            // Msegat — required when selected
            'msegat_username' => [$smsProvider === 'msegat' ? 'required' : 'nullable', 'string', 'max:100'],
            'msegat_api_key' => [
                Rule::requiredIf($smsProvider === 'msegat' && ! $school->setting('msegat_api_key')),
                'nullable', 'string', 'max:255',
            ],
            'msegat_sender' => [$smsProvider === 'msegat' ? 'required' : 'nullable', 'string', 'max:50'],

            // WhatsApp
            'whatsapp_enabled' => ['sometimes', 'in:on,1,true'],
            'whatsapp_phone_number_id' => ['nullable', 'string', 'max:100'],
            'whatsapp_access_token' => ['nullable', 'string', 'max:500'],
            'whatsapp_webhook_verify_token' => ['nullable', 'string', 'max:100'],
        ]);

        // Preserve encrypted secrets — only re-encrypt when a new value is typed
        $smtpPassword = $school->setting('smtp_password');
        if ($request->filled('smtp_password')) {
            $smtpPassword = TenantMailer::encrypt($request->smtp_password);
        }

        $twilioToken = $school->setting('twilio_token');
        if ($request->filled('twilio_token')) {
            $twilioToken = TenantSms::encrypt($request->twilio_token);
        }

        $msegatApiKey = $school->setting('msegat_api_key');
        if ($request->filled('msegat_api_key')) {
            $msegatApiKey = TenantSms::encrypt($request->msegat_api_key);
        }

        $whatsappAccessToken = $school->setting('whatsapp_access_token');
        if ($request->filled('whatsapp_access_token')) {
            $whatsappAccessToken = TenantSms::encrypt($request->whatsapp_access_token);
        }

        $data = [
            'name' => $request->name,
            'city' => $request->city,
            'settings' => [
                'address' => $request->address,
                'website_url' => $request->website_url,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
                'welcome_message' => $request->welcome_message,
                'thankyou_message' => $request->thankyou_message,
                'primary_color' => $request->primary_color ?: null,
                'allow_new_issues' => $request->boolean('allow_new_issues'),
                'allow_anonymous_issues' => $request->boolean('allow_anonymous_issues'),
                'chatbot_enabled' => $request->boolean('chatbot_enabled'),
                // Email
                'smtp_enabled' => $smtpEnabled,
                'smtp_host' => $request->smtp_host ?: null,
                'smtp_port' => $request->smtp_port ? (int) $request->smtp_port : 587,
                'smtp_username' => $request->smtp_username ?: null,
                'smtp_password' => $smtpPassword,
                'smtp_encryption' => $request->smtp_encryption ?: 'tls',
                'smtp_from_address' => $request->smtp_from_address ?: null,
                'smtp_from_name' => $request->smtp_from_name ?: null,
                // SMS
                'sms_provider' => $smsProvider ?: null,
                'twilio_sid' => $request->twilio_sid ?: null,
                'twilio_token' => $twilioToken,
                'twilio_from' => $request->twilio_from ?: null,
                'msegat_username' => $request->msegat_username ?: null,
                'msegat_api_key' => $msegatApiKey,
                'msegat_sender' => $request->msegat_sender ?: null,
                // WhatsApp
                'whatsapp_enabled' => $request->boolean('whatsapp_enabled'),
                'whatsapp_phone_number_id' => $request->whatsapp_phone_number_id ?: null,
                'whatsapp_access_token' => $whatsappAccessToken,
                'whatsapp_webhook_verify_token' => $request->whatsapp_webhook_verify_token ?: null,
                // Preserve the opaque webhook ID — never accept it from the form
                'whatsapp_webhook_id' => $school->setting('whatsapp_webhook_id'),
            ],
        ];

        if ($request->hasFile('logo')) {
            if ($school->logo) {
                Storage::disk('logos')->delete($school->logo);
            }
            $path = $request->file('logo')->store("schools/{$school->id}", 'logos');
            $data['logo'] = $path;
        }

        if ($request->boolean('remove_logo') && $school->logo) {
            Storage::disk('logos')->delete($school->logo);
            $data['logo'] = null;
        }

        $school->update($data);

        return redirect()->route('tenant.admin.settings.edit')
            ->with('ok', 'School settings updated.');
    }
}
