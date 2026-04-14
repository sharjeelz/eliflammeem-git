<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAnnouncementViaMail;
use App\Jobs\SendAnnouncementViaSms;
use App\Jobs\SendAnnouncementViaWhatsApp;
use App\Models\Branch;
use App\Models\BroadcastBatch;
use App\Models\BroadcastRecipient;
use App\Models\RosterContact;
use App\Models\School;
use App\Models\WhatsAppTemplate;
use App\Services\TenantSms;
use App\Services\TenantWhatsApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactBroadcastController extends Controller
{
    public function create(Request $request): View
    {
        $branches = Branch::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $smsEnabled = TenantSms::isConfigured(tenant('id'));
        $whatsappEnabled = TenantWhatsApp::isConfigured(tenant('id'));

        $whatsappTemplates = WhatsAppTemplate::where('tenant_id', tenant('id'))
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'meta_template_name', 'parameters']);

        // Load selected contacts from ?ids= query param
        $preselectedIds = collect(explode(',', $request->query('ids', '')))->map('intval')->filter()->values();
        $selectedContacts = $preselectedIds->isNotEmpty()
            ? RosterContact::where('tenant_id', tenant('id'))
                ->whereIn('id', $preselectedIds)
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'email', 'phone'])
            : collect();

        return view('tenant.admin.contacts.broadcast', compact(
            'branches', 'smsEnabled', 'whatsappEnabled', 'whatsappTemplates', 'selectedContacts'
        ));
    }

    /**
     * AJAX endpoint — returns recipient counts based on current filter + channel.
     */
    public function count(Request $request): JsonResponse
    {
        $channel = $request->input('channel', 'email');
        $base = $this->buildAudienceQuery($request);

        $emailCount = (clone $base)->whereNotNull('email')->count();
        $smsCount = (clone $base)->whereNotNull('phone')->count();
        $whatsappCount = (clone $base)->whereNotNull('phone')->count();
        $total = (clone $base)->count();

        $channelCount = match ($channel) {
            'email' => $emailCount,
            'sms' => $smsCount,
            'whatsapp' => $whatsappCount,
            default => 0,
        };

        return response()->json([
            'total' => $total,
            'channel_count' => $channelCount,
            'email_count' => $emailCount,
            'sms_count' => $smsCount,
            'whatsapp_count' => $whatsappCount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $smsEnabled = TenantSms::isConfigured(tenant('id'));
        $whatsappEnabled = TenantWhatsApp::isConfigured(tenant('id'));
        $channel = $request->input('channel');

        $request->validate([
            'audience' => ['required', 'in:all,filter,specific'],
            'contact_ids' => ['required_if:audience,specific', 'nullable', 'array', 'min:1'],
            'contact_ids.*' => ['integer'],
            'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('tenant_id', tenant('id'))],
            'role' => ['nullable', 'in:parent,teacher'],
            'channel' => ['required', 'in:email,sms,whatsapp'],
            'subject' => [
                $channel === 'email' ? 'required' : 'nullable',
                'string',
                'max:150',
            ],
            'message' => ['required', 'string', 'max:1600'],
            'media' => ['nullable', 'file', 'max:16384'], // Max 16MB
            'media_type' => ['nullable', 'in:image,document,video,audio'],
            'whatsapp_template_id' => ['nullable', 'integer', Rule::exists('whatsapp_templates', 'id')->where('tenant_id', tenant('id'))],
            'template_parameters' => ['nullable', 'array'],
            'template_parameters.*' => ['nullable', 'string', 'max:500'],
        ]);

        if ($channel === 'sms' && ! $smsEnabled) {
            return back()
                ->withErrors(['channel' => 'SMS is not configured. Set up your SMS provider in School Settings.'])
                ->withInput();
        }

        if ($channel === 'whatsapp' && ! $whatsappEnabled) {
            return back()
                ->withErrors(['channel' => 'WhatsApp is not configured. Set up your WhatsApp Business API in School Settings.'])
                ->withInput();
        }

        $school = School::where('tenant_id', tenant('id'))->first();
        $contacts = $this->buildAudienceQuery($request)->get();
        $subject = $request->subject ?: ($school->name.' — Announcement');

        // Handle media upload
        $mediaType = 'none';
        $mediaPath = null;
        $mediaFilename = null;
        $mediaMimeType = null;

        if ($request->hasFile('media') && $request->input('media_type')) {
            $file = $request->file('media');
            $mediaType = $request->input('media_type');
            $mediaFilename = $file->getClientOriginalName();
            $mediaMimeType = $file->getMimeType();

            // Store on private disk; uploaded to Meta's Media API before sending
            $mediaPath = $file->store('broadcast_media/'.tenant('id'), 'local');
        }

        $audienceFilter = null;
        if ($request->input('audience') === 'filter') {
            $audienceFilter = [
                'branch_id' => $request->integer('branch_id') ?: null,
                'role' => $request->input('role'),
            ];
        }

        $batch = BroadcastBatch::create([
            'tenant_id' => tenant('id'),
            'subject' => $subject,
            'message' => $request->message,
            'channel' => $channel,
            'audience_type' => $request->input('audience'),
            'audience_filter' => $audienceFilter,
            'media_type' => $mediaType,
            'media_path' => $mediaPath,
            'media_filename' => $mediaFilename,
            'media_mime_type' => $mediaMimeType,
            'whatsapp_template_id' => $request->input('whatsapp_template_id'),
            'template_parameters' => $request->input('template_parameters'),
            'total_count' => 0,
            'sent_count' => 0,
            'failed_count' => 0,
        ]);

        // Load WhatsApp template once outside the loop to avoid N+1 queries
        $whatsappTemplateName = null;
        $whatsappTemplateParameters = null;
        $whatsappTemplateLanguage = 'en';
        if ($channel === 'whatsapp' && $request->input('whatsapp_template_id')) {
            $whatsappTemplate = WhatsAppTemplate::find($request->input('whatsapp_template_id'));
            if ($whatsappTemplate) {
                $whatsappTemplateName = $whatsappTemplate->meta_template_name;
                $whatsappTemplateParameters = $request->input('template_parameters', []);
                $whatsappTemplateLanguage = $whatsappTemplate->language ?? 'en';
            }
        }

        $emailCount = 0;
        $smsCount = 0;
        $whatsappCount = 0;
        $skipped = 0;

        foreach ($contacts as $contact) {
            $recipient = BroadcastRecipient::create([
                'tenant_id' => tenant('id'),
                'broadcast_batch_id' => $batch->id,
                'contact_id' => $contact->id,
                'contact_name' => $contact->name,
                'contact_email' => $contact->email,
                'contact_phone' => $contact->phone,
                'status' => 'pending',
            ]);

            $dispatched = false;

            if ($channel === 'email' && $contact->email) {
                SendAnnouncementViaMail::dispatch(
                    tenant('id'),
                    $contact->email,
                    $contact->name,
                    $subject,
                    $request->message,
                    $school->name,
                    $recipient->id,
                );
                $emailCount++;
                $dispatched = true;
            }

            if ($channel === 'sms' && $contact->phone && $smsEnabled) {
                SendAnnouncementViaSms::dispatch(
                    tenant('id'),
                    $contact->phone,
                    $request->message,
                    $recipient->id,
                );
                $smsCount++;
                $dispatched = true;
            }

            if ($channel === 'whatsapp' && $contact->phone && $whatsappEnabled) {
                $templateName = null;
                $templateParameters = null;

                // If a template is selected, use it
                $templateLanguage = 'en';
                if ($request->input('whatsapp_template_id')) {
                    $template = WhatsAppTemplate::find($request->input('whatsapp_template_id'));
                    if ($template) {
                        $templateName = $template->meta_template_name;
                        $templateParameters = $request->input('template_parameters', []);
                        $templateLanguage = $template->language ?? 'en';
                    }
                }

                SendAnnouncementViaWhatsApp::dispatch(
                    tenant('id'),
                    $contact->phone,
                    $request->message,
                    $recipient->id,
                    $mediaType !== 'none' ? $mediaType : null,
                    $mediaPath,
                    $templateName,
                    $templateParameters,
                    $templateLanguage,
                );
                $whatsappCount++;
                $dispatched = true;
            }

            if (! $dispatched) {
                $recipient->delete();
                $skipped++;
            }
        }

        $batch->update(['total_count' => $emailCount + $smsCount + $whatsappCount]);

        $parts = [];
        if ($emailCount) {
            $parts[] = "{$emailCount} email(s) queued";
        }
        if ($smsCount) {
            $parts[] = "{$smsCount} SMS queued";
        }
        if ($whatsappCount) {
            $parts[] = "{$whatsappCount} WhatsApp message(s) queued";
        }
        if ($skipped) {
            $parts[] = "{$skipped} skipped (no matching contact info)";
        }

        return redirect()->route('tenant.admin.contacts.index')
            ->with('ok', implode(', ', $parts ?: ['No recipients matched the selected filters']).'.');
    }

    private function buildAudienceQuery(Request $request)
    {
        $q = RosterContact::where('tenant_id', tenant('id'))->whereNull('deactivated_at');

        if ($request->input('audience') === 'specific') {
            $ids = collect($request->input('contact_ids', []))->map('intval')->filter()->all();
            $q->whereIn('id', $ids);
        } elseif ($request->input('audience') === 'filter') {
            if ($branchId = $request->integer('branch_id') ?: null) {
                $q->where('branch_id', $branchId);
            }
            if ($role = $request->input('role')) {
                $q->where('role', $role);
            }
        }

        return $q;
    }

    public function logs(Request $request): View
    {
        $batches = BroadcastBatch::forTenant(tenant('id'))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $branchNames = Branch::where('tenant_id', tenant('id'))->pluck('name', 'id');

        return view('tenant.admin.contacts.broadcast-logs', compact('batches', 'branchNames'));
    }

    public function logDetail(BroadcastBatch $batch): View
    {
        abort_if($batch->tenant_id !== tenant('id'), 403);

        $recipients = $batch->recipients()
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('tenant.admin.contacts.broadcast-log-detail', compact('batch', 'recipients'));
    }

    public function retryRecipient(BroadcastRecipient $recipient): RedirectResponse
    {
        abort_if($recipient->batch->tenant_id !== tenant('id'), 403);

        $batch = $recipient->batch;
        $recipient->resetForRetry();

        if ($batch->channel === 'email' && $recipient->contact_email) {
            $school = School::where('tenant_id', tenant('id'))->first();
            SendAnnouncementViaMail::dispatch(
                tenant('id'),
                $recipient->contact_email,
                $recipient->contact_name,
                $batch->subject,
                $batch->message,
                $school?->name,
                $recipient->id,
            );
        } elseif ($batch->channel === 'sms' && $recipient->contact_phone) {
            SendAnnouncementViaSms::dispatch(
                tenant('id'),
                $recipient->contact_phone,
                $batch->message,
                $recipient->id,
            );
        } elseif ($batch->channel === 'whatsapp' && $recipient->contact_phone) {
            $templateName = null;
            $templateParameters = null;

            $templateLanguage = 'en';
            if ($batch->whatsapp_template_id) {
                $template = WhatsAppTemplate::find($batch->whatsapp_template_id);
                if ($template) {
                    $templateName = $template->meta_template_name;
                    $templateParameters = $batch->template_parameters;
                    $templateLanguage = $template->language ?? 'en';
                }
            }

            SendAnnouncementViaWhatsApp::dispatch(
                tenant('id'),
                $recipient->contact_phone,
                $batch->message,
                $recipient->id,
                $batch->media_type !== 'none' ? $batch->media_type : null,
                $batch->media_path,
                $templateName,
                $templateParameters,
                $templateLanguage,
            );
        }

        return back()->with('ok', 'Retry queued for '.$recipient->contact_name);
    }
}
