<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\AccessCodeMail;
use App\Models\AccessCode;
use App\Models\Branch;
use App\Models\RosterContact;
use App\Models\School;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendAccessCodeViaMail;
use App\Jobs\SendAccessCodeViaSms;
use App\Services\TenantSms;
use App\Models\Issue;
use App\Models\IssueActivity;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class RosterContactController extends Controller
{
    public function index(Request $request)
    {
        $q = RosterContact::where('tenant_id', tenant('id'))
            ->with(['branch:id,name', 'accessCodes'])
            ->orderByDesc('created_at');

        if ($search = $request->get('search')) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('phone', 'ilike', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $q->where('role', $role);
        }

        if ($branch = $request->get('branch_id')) {
            $q->where('branch_id', $branch);
        }

        $codeStatus = $request->get('code_status');

        if ($codeStatus === 'deactivated') {
            $q->whereNotNull('deactivated_at');
        } else {
            // All non-deactivated views hide deactivated contacts
            $q->whereNull('deactivated_at');

            switch ($codeStatus) {
                case 'active':
                    $q->whereHas('accessCodes', fn ($a) => $a->whereNull('used_at')
                        ->where(fn ($w) => $w->whereNull('expires_at')->orWhere('expires_at', '>', now())));
                    break;
                case 'expired':
                    $q->whereHas('accessCodes', fn ($a) => $a->whereNull('used_at')
                        ->whereNotNull('expires_at')->where('expires_at', '<=', now()));
                    break;
                case 'used':
                    $q->whereHas('accessCodes', fn ($a) => $a->whereNotNull('used_at'));
                    break;
                case 'not_sent':
                    $q->whereHas('accessCodes', fn ($a) => $a->whereNull('sent_at'));
                    break;
                case 'none':
                    $q->whereDoesntHave('accessCodes');
                    break;
            }
        }

        $contacts              = $q->paginate(50)->withQueryString();
        $branches              = Branch::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $smsEnabled            = TenantSms::isConfigured(tenant('id'));
        $planAllowBroadcasting = PlanService::forCurrentTenant()->allows('broadcasting');

        return view('tenant.admin.contacts.index', compact('contacts', 'branches', 'smsEnabled', 'planAllowBroadcasting'));
    }

    public function create()
    {
        $branches = Branch::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);

        return view('tenant.admin.contacts.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'role' => ['required', 'in:parent,teacher'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:191'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:30'],
            'external_id' => ['nullable', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('roster_contacts', 'external_id')
                    ->where('tenant_id', tenant('id'))],
            'branch_id' => ['nullable', 'integer',
                \Illuminate\Validation\Rule::exists('branches', 'id')
                    ->where('tenant_id', tenant('id'))],
        ], [
            'email.required_without' => 'At least one of email or phone is required.',
            'phone.required_without' => 'At least one of email or phone is required.',
            'external_id.unique'     => 'This External ID is already used by another contact in this school.',
        ]);

        // Plan limit check
        $plan = PlanService::forCurrentTenant();
        $contactCount = RosterContact::where('tenant_id', tenant('id'))->whereNull('deactivated_at')->count();
        if (! $plan->withinLimit('max_contacts', $contactCount)) {
            return back()->with('error', "Your plan ({$plan->planName()}) allows a maximum of {$plan->limitLabel('max_contacts')} active contacts. Please upgrade to add more.")->withInput();
        }

        $school = \App\Models\School::where('tenant_id', tenant('id'))->value('id');

        RosterContact::create([
            'tenant_id' => tenant('id'),
            'school_id' => $school,
            'branch_id' => $data['branch_id'] ?? null,
            'role' => $data['role'],
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'external_id' => $data['external_id'] ?? null,
        ]);

        return redirect()->route('tenant.admin.contacts.index')
            ->with('ok', 'Contact created successfully.');
    }

    public function issues(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        $issues = \App\Models\Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->with(['branch:id,name', 'assignedTo:id,name', 'roasterContact:id,name,role'])
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.admin.issues.index-issues', [
            'issues'      => $issues,
            'contact'     => $contact,
            'hideContact' => true,
        ]);
    }

    public function edit(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        $branches = Branch::where('tenant_id', tenant('id'))->orderBy('name')->get(['id', 'name']);
        $code = AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->first();

        $issueCount = \App\Models\Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->count();

        $spamCount = \App\Models\Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->where('is_spam', true)
            ->count();

        $smsEnabled = TenantSms::isConfigured(tenant('id'));

        return view('tenant.admin.contacts.edit', compact('contact', 'branches', 'code', 'issueCount', 'spamCount', 'smsEnabled'));
    }

    public function update(Request $request, RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'role' => ['required', 'in:parent,teacher'],
            'email' => ['nullable', 'required_without:phone', 'email', 'max:191'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:30'],
            'external_id' => ['nullable', 'string', 'max:100',
                \Illuminate\Validation\Rule::unique('roster_contacts', 'external_id')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($contact->id)],
            'branch_id' => ['nullable', 'integer',
                \Illuminate\Validation\Rule::exists('branches', 'id')
                    ->where('tenant_id', tenant('id'))],
        ], [
            'email.required_without' => 'At least one of email or phone is required.',
            'phone.required_without' => 'At least one of email or phone is required.',
            'external_id.unique'     => 'This External ID is already used by another contact in this school.',
        ]);

        $oldBranchId = $contact->branch_id;
        $newBranchId = $data['branch_id'] ? (int) $data['branch_id'] : null;
        $branchChanged = $newBranchId !== null && (int) $oldBranchId !== $newBranchId;

        $contact->update([
            'branch_id'   => $newBranchId,
            'role'        => $data['role'],
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'external_id' => $data['external_id'] ?? null,
        ]);

        if ($branchChanged) {
            $oldBranch = Branch::where('tenant_id', tenant('id'))->find($oldBranchId);
            $newBranch = Branch::where('tenant_id', tenant('id'))->find($newBranchId);
            $this->handleBranchChange($contact, $newBranch, $request->user(), $oldBranch);
        }

        return redirect()->route('tenant.admin.contacts.edit', $contact)
            ->with('ok', 'Contact updated.' . ($branchChanged ? ' Branch changed — code revoked and open issues unassigned.' : ''));
    }

    public function destroy(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        // Revoke access codes so they can't be reused
        AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->delete();

        // Preserve issue history but detach the contact
        Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->update(['roster_contact_id' => null]);

        $contact->delete();

        return redirect()->route('tenant.admin.contacts.index')
            ->with('ok', 'Contact deleted.');
    }

    public function reactivate(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        $contact->update([
            'deactivated_at' => null,
            'revoke_reason'  => null,
        ]);

        // Delete any stale codes and issue a fresh one
        AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->delete();

        $code = AccessCode::create([
            'tenant_id'         => tenant('id'),
            'roster_contact_id' => $contact->id,
            'branch_id'         => $contact->branch_id,
            'code'              => $this->uniqueCode(),
            'channel'           => 'manual',
            'expires_at'        => now()->addDays(7),
        ]);

        return back()->with('ok', "{$contact->name} reactivated. New access code: {$code->code}");
    }

    public function generateCode(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        $existing = AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->first();

        if ($existing) {
            return back()->with('ok', "Code already exists: {$existing->code}");
        }

        $code = AccessCode::create([
            'tenant_id'         => tenant('id'),
            'roster_contact_id' => $contact->id,
            'branch_id'         => $contact->branch_id,
            'code'              => $this->uniqueCode(),
            'channel'           => 'manual',
            'expires_at'        => now()->addDays(7),
        ]);

        $contact->update([
            'revoke_reason'    => null,
            'spam_pardoned_at' => $contact->revoke_reason ? now() : $contact->spam_pardoned_at,
        ]);

        return back()->with('ok', "Access code generated: {$code->code}");
    }

    public function renewCode(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        // Delete old code and issue a fresh one with a new 7-day window
        AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->delete();

        $channel = $contact->email ? 'email' : ($contact->phone ? 'sms' : 'manual');

        $code = AccessCode::create([
            'tenant_id'         => tenant('id'),
            'roster_contact_id' => $contact->id,
            'branch_id'         => $contact->branch_id,
            'code'              => $this->uniqueCode(),
            'channel'           => $channel,
            'expires_at'        => now()->addDays(7),
        ]);

        $schoolName = \App\Models\School::where('tenant_id', tenant('id'))->value('name');
        $sent = '';

        if ($contact->email) {
            Mail::to($contact->email)->queue(new AccessCodeMail($code->code, $schoolName, $contact->name, tenant('id')));
            $code->update(['sent_at' => now()]);
            $sent = "sent to {$contact->email}";
        } elseif ($contact->phone) {
            if (TenantSms::isConfigured(tenant('id'))) {
                try {
                    TenantSms::send(
                        $contact->phone,
                        "Your access code for {$schoolName} is: {$code->code}. Visit the school portal to submit your issue.",
                        tenant('id'),
                    );
                    $code->update(['sent_at' => now()]);
                    $sent = "sent to {$contact->phone} via SMS";
                } catch (\Throwable $e) {
                    $sent = "SMS failed: {$e->getMessage()}";
                }
            }
        }

        $contact->update([
            'revoke_reason'    => null,
            'spam_pardoned_at' => $contact->revoke_reason ? now() : $contact->spam_pardoned_at,
        ]);

        $msg = "Access code renewed: {$code->code} (expires in 7 days)";
        if ($sent) {
            $msg .= " — {$sent}.";
        } else {
            $msg .= '. No contact method available to send automatically.';
        }

        return back()->with('ok', $msg);
    }

    public function revokeCode(RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->delete();

        $actor = request()->user();
        $contact->update(['revoke_reason' => 'Manually revoked by ' . $actor->name]);

        return back()->with('ok', 'Access code revoked.');
    }

    public function sendCode(Request $request, RosterContact $contact)
    {
        abort_unless($contact->tenant_id === tenant('id'), 404);

        $request->validate([
            'channel' => ['required', 'in:email,sms'],
        ]);

        $code = AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->first();

        if (! $code) {
            return back()->withErrors(['code' => 'This contact has no access code. Generate one first.']);
        }

        $schoolName = \App\Models\School::where('tenant_id', tenant('id'))->value('name');

        if ($request->channel === 'email') {
            if (! $contact->email) {
                return back()->withErrors(['channel' => 'This contact has no email address.']);
            }
            Mail::to($contact->email)->queue(new AccessCodeMail($code->code, $schoolName, $contact->name, tenant('id')));
            $code->update(['sent_at' => now(), 'send_error' => null]);

            return back()->with('ok', "Access code sent to {$contact->email} via email.");
        }

        // SMS
        if (! $contact->phone) {
            return back()->withErrors(['channel' => 'This contact has no phone number.']);
        }

        if (! TenantSms::isConfigured(tenant('id'))) {
            return back()->withErrors(['channel' => 'SMS is not configured. Configure a provider in School Settings → SMS tab.']);
        }

        try {
            TenantSms::send(
                $contact->phone,
                "Your access code for {$schoolName} is: {$code->code}. Visit the school portal to submit your issue.",
                tenant('id'),
            );
            $code->update(['sent_at' => now(), 'send_error' => null]);
        } catch (\Throwable $e) {
            $code->update(['send_error' => $e->getMessage()]);
            return back()->withErrors(['channel' => 'SMS failed: ' . $e->getMessage()]);
        }

        return back()->with('ok', "Access code sent to {$contact->phone} via SMS.");
    }

    public function importForm()
    {
        $plan          = \App\Services\PlanService::forCurrentTenant();
        $planAllowApi  = $plan->allows('api_access');
        $activeApiKeys = $planAllowApi
            ? \App\Models\TenantApiKey::where('tenant_id', tenant('id'))
                ->whereNull('revoked_at')
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->get(['id', 'name', 'key_prefix'])
            : collect();
        $tenantDomain  = request()->getHost();

        return view('tenant.admin.contacts.import', compact('planAllowApi', 'activeApiKeys', 'tenantDomain'));
    }

    public function import(Request $request)
    {
        $uploadedFile = $request->file('file');
        $ext = $uploadedFile ? strtolower($uploadedFile->getClientOriginalExtension()) : '';

        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        if (! in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            return back()->withErrors(['file' => 'Please upload an Excel (.xlsx, .xls) or CSV (.csv) file.'])->withInput();
        }

        $tenantId = tenant('id');
        $schoolId = School::where('tenant_id', $tenantId)->value('id');

        // Build branch lookup map: lowercase(name/code) => id
        $branches = Branch::where('tenant_id', $tenantId)->orderBy('id')->get(['id', 'name', 'code']);
        $branchMap = $branches
            ->flatMap(fn ($b) => [
                strtolower($b->name) => $b->id,
                strtolower($b->code) => $b->id,
            ])
            ->all();

        // If school has exactly one branch, auto-assign when none is specified in the file
        $defaultBranchId = $branches->count() === 1 ? $branches->first()->id : null;

        $validRoles = ['parent', 'teacher'];
        $imported    = 0;
        $updated     = 0;
        $deactivated = 0;
        $skipped     = [];
        $rowNumber   = 1;
        $seenExternalIds = [];

        // FastExcel detects file format by extension. Laravel temp files have .tmp extension
        // which makes it default to the XLSX reader and fail on CSVs. Copy to a proper path.
        $tmpPath = tempnam(sys_get_temp_dir(), 'import_') . '.' . $ext;
        copy($uploadedFile->getRealPath(), $tmpPath);

        try {
        (new FastExcel)->import($tmpPath, function ($row) use (
            $tenantId, $schoolId, $branchMap, $defaultBranchId, $validRoles,
            &$imported, &$updated, &$deactivated, &$skipped, &$rowNumber, &$seenExternalIds
        ) {
            $rowNumber++;

            // Normalise keys: trim + lowercase
            $row = collect($row)->mapWithKeys(fn ($v, $k) => [strtolower(trim($k)) => trim((string) $v)])->all();

            $name       = $row['name'] ?? '';
            $email      = $row['email'] ?: null;
            // Excel sometimes stores phone numbers as scientific notation (e.g. 9.665E+11).
            // Convert to a plain integer string when that happens.
            $rawPhone = $row['phone'] ?? '';
            if ($rawPhone !== '' && preg_match('/^[0-9.]+[eE][+\-]?[0-9]+$/', $rawPhone)) {
                $rawPhone = number_format((float) $rawPhone, 0, '.', '');
            }
            $phone = $rawPhone ?: null;
            $externalId = $row['external_id'] ?? null ?: null;

            // is_active_in_sis: blank/missing = active; falsy strings = inactive
            $sisActiveRaw = strtolower(trim($row['is_active_in_sis'] ?? ''));
            $isActive = ! in_array($sisActiveRaw, ['0', 'no', 'false', 'n'], true);

            if ($name === '') {
                $skipped[] = "Row {$rowNumber}: name is required.";

                return null;
            }

            if (! $email && ! $phone) {
                $skipped[] = "Row {$rowNumber} ({$name}): at least one of email or phone is required — skipped.";

                return null;
            }

            // Guard against duplicate external_id within this file
            if ($externalId) {
                if (in_array($externalId, $seenExternalIds, true)) {
                    $skipped[] = "Row {$rowNumber} ({$name}): external_id '{$externalId}' appears more than once in this file — skipped.";

                    return null;
                }
                $seenExternalIds[] = $externalId;
            }

            // --- Find existing contact (same lookup order regardless of active flag) ---
            $existing = null;

            if ($externalId) {
                $existing = RosterContact::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where('external_id', $externalId)
                    ->first();
            }

            if (! $existing && $email) {
                $existing = RosterContact::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where('email', $email)
                    ->first();
            }

            if (! $existing && $phone && ! $email) {
                $existing = RosterContact::withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where('phone', $phone)
                    ->first();
            }

            // --- Inactive: deactivate if found, skip if not ---
            if (! $isActive) {
                if ($existing && ! $existing->deactivated_at) {
                    $existing->update([
                        'deactivated_at' => now(),
                        'revoke_reason'  => 'Deactivated via SIS import (is_active_in_sis = false)',
                    ]);
                    AccessCode::where('tenant_id', $tenantId)
                        ->where('roster_contact_id', $existing->id)
                        ->delete();
                    $deactivated++;
                }

                return null;
            }

            // --- Active: upsert ---
            $role      = strtolower($row['role'] ?? '');
            $branchKey = strtolower($row['branch'] ?? '');
            if ($branchKey !== '' && ! isset($branchMap[$branchKey])) {
                $skipped[] = "Row {$rowNumber} ({$name}): branch '{$row['branch']}' not recognised — contact saved without a branch.";
            }
            $branchId = $branchKey !== '' ? ($branchMap[$branchKey] ?? null) : $defaultBranchId;

            if (! in_array($role, $validRoles, true)) {
                $skipped[] = "Row {$rowNumber} ({$name}): role '{$role}' invalid — must be parent or teacher.";

                return null;
            }

            if ($existing) {
                $wasDeactivated = $existing->deactivated_at !== null;

                $existing->update([
                    'name'           => $name,
                    'email'          => $email,
                    'phone'          => $phone,
                    'role'           => $role,
                    'branch_id'      => $branchId,
                    'external_id'    => $externalId ?? $existing->external_id,
                    'deactivated_at' => null,
                    'revoke_reason'  => null,
                ]);

                // Regenerate access code if contact was reactivated
                if ($wasDeactivated) {
                    AccessCode::where('tenant_id', $tenantId)
                        ->where('roster_contact_id', $existing->id)
                        ->delete();

                    AccessCode::create([
                        'tenant_id'         => $tenantId,
                        'roster_contact_id' => $existing->id,
                        'branch_id'         => $branchId ?? $existing->branch_id,
                        'code'              => $this->uniqueCode(),
                        'channel'           => 'csv',
                        'expires_at'        => now()->addDays(7),
                    ]);
                }

                $updated++;

                return null;
            }

            // --- Create new ---
            RosterContact::create([
                'tenant_id'   => $tenantId,
                'school_id'   => $schoolId,
                'branch_id'   => $branchId,
                'role'        => $role,
                'name'        => $name,
                'email'       => $email,
                'phone'       => $phone,
                'external_id' => $externalId,
            ]);

            $imported++;

            return null;
        });
        } finally {
            @unlink($tmpPath);
        }

        $parts = [];
        if ($imported) {
            $parts[] = "{$imported} created";
        }
        if ($updated) {
            $parts[] = "{$updated} updated";
        }
        if ($deactivated) {
            $parts[] = "{$deactivated} deactivated";
        }

        $message = implode(', ', $parts ?: ['0 contacts processed']) . '.';

        if (count($skipped)) {
            return redirect()->route('tenant.admin.contacts.index')
                ->with('ok', $message)
                ->with('import_warnings', $skipped);
        }

        return redirect()->route('tenant.admin.contacts.index')->with('ok', $message);
    }

    public function template()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="contacts_template.csv"',
        ];

        $rows = [
            ['name', 'role', 'email', 'phone', 'branch', 'external_id', 'is_active_in_sis'],
            ['Jane Smith', 'parent',  'jane@example.com', '+1 555 0101', 'Main', 'STU-001', '1'],
            ['Mr. Ali',    'teacher', 'ali@school.edu',   '+1 555 0202', 'Main', 'TCH-005', '1'],
            ['Old Student','parent',  'old@example.com',  '',            'Main', 'STU-002', '0'],
        ];

        $csv = implode("\n", array_map(fn ($r) => implode(',', $r), $rows));

        return response($csv, 200, $headers);
    }

    public function bulkGenerateCode(Request $request)
    {
        $data = $request->validate([
            'contact_ids'   => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer'],
        ]);

        $contacts = RosterContact::where('tenant_id', tenant('id'))
            ->whereIn('id', $data['contact_ids'])
            ->with('accessCodes')
            ->get();

        $generated = 0;
        $skipped   = 0;

        foreach ($contacts as $contact) {
            if ($contact->accessCodes->isNotEmpty()) {
                $skipped++;
                continue;
            }

            AccessCode::create([
                'tenant_id'         => tenant('id'),
                'roster_contact_id' => $contact->id,
                'branch_id'         => $contact->branch_id,
                'code'              => $this->uniqueCode(),
                'channel'           => 'manual',
                'expires_at'        => now()->addDays(7),
            ]);

            $generated++;
        }

        $msg = "{$generated} access code(s) generated.";
        if ($skipped) {
            $msg .= " {$skipped} skipped (already have a code).";
        }

        return back()->with('ok', $msg);
    }

    public function bulkRevokeCode(Request $request)
    {
        $data = $request->validate([
            'contact_ids'   => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer'],
        ]);

        $revoked = AccessCode::where('tenant_id', tenant('id'))
            ->whereIn('roster_contact_id', $data['contact_ids'])
            ->delete();

        return back()->with('ok', "{$revoked} access code(s) revoked.");
    }

    public function bulkChangeBranch(Request $request)
    {
        $data = $request->validate([
            'contact_ids'   => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer'],
            'branch_id'     => ['required', 'integer', 'exists:branches,id'],
        ]);

        $actor    = $request->user();
        $branch   = Branch::where('tenant_id', tenant('id'))->findOrFail($data['branch_id']);
        $contacts = RosterContact::where('tenant_id', tenant('id'))
            ->whereIn('id', $data['contact_ids'])
            ->get();

        $totalUnassigned = 0;
        $moved   = 0;
        $skipped = 0;
        foreach ($contacts as $contact) {
            if ($contact->branch_id == $branch->id) {
                $skipped++;
                continue; // already in this branch
            }
            $oldBranch = Branch::where('tenant_id', tenant('id'))->find($contact->branch_id);
            $contact->update(['branch_id' => $branch->id]);
            $totalUnassigned += $this->handleBranchChange($contact, $branch, $actor, $oldBranch);
            $moved++;
        }

        if ($moved === 0) {
            return back()->with('info', "No contacts were moved — all {$skipped} selected contact(s) are already in \"{$branch->name}\".");
        }

        $msg = "{$moved} contact(s) moved to \"{$branch->name}\". Codes revoked, {$totalUnassigned} open issue(s) auto-closed.";
        if ($skipped > 0) {
            $msg .= " {$skipped} skipped (already in this branch).";
        }
        return back()->with('ok', $msg);
    }

    /**
     * Shared logic when a contact's branch changes:
     * revoke their access code, unassign + flag their open issues, log activity.
     * Returns the count of issues unassigned.
     */
    private function handleBranchChange(RosterContact $contact, Branch $newBranch, $actor, ?Branch $oldBranch = null): int
    {
        // Revoke access code — needs a fresh one for the new branch
        AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->delete();

        $fromLabel = $oldBranch ? " from \"{$oldBranch->name}\"" : '';
        $contact->update([
            'revoke_reason' => "Moved{$fromLabel} to branch \"{$newBranch->name}\" by {$actor->name} — new access code required.",
        ]);

        // Unassign, flag, and auto-close all open issues
        $openIssues = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->whereIn('status', ['new', 'in_progress'])
            ->get(['id', 'assigned_user_id', 'status', 'meta']);

        foreach ($openIssues as $issue) {
            $fromStatus = $issue->status;
            $meta = $issue->meta ?? [];
            $meta['unassigned_reason'] = 'contact_branch_changed';

            $issue->update([
                'assigned_user_id'  => null,
                'status'            => 'closed',
                'status_entered_at' => now(),
                'last_activity_at'  => now(),
                'meta'              => $meta,
            ]);

            // Internal note for admin audit trail
            \App\Models\IssueMessage::create([
                'tenant_id'   => tenant('id'),
                'issue_id'    => $issue->id,
                'sender'      => 'admin',
                'message'     => "Auto-closed: contact \"{$contact->name}\" was moved{$fromLabel} to branch \"{$newBranch->name}\" by {$actor->name}.",
                'is_internal' => true,
                'author_type' => \App\Models\User::class,
                'author_id'   => $actor->id,
                'meta'        => ['system' => true],
            ]);

            // Public-facing message — visible to the parent/teacher on the portal
            \App\Models\IssueMessage::create([
                'tenant_id'   => tenant('id'),
                'issue_id'    => $issue->id,
                'sender'      => 'admin',
                'message'     => "This issue has been closed by the school as your record has been updated. Please contact the school to receive a new access code if you have a new concern.",
                'is_internal' => false,
                'author_type' => \App\Models\User::class,
                'author_id'   => $actor->id,
                'meta'        => ['system' => true],
            ]);

            IssueActivity::create([
                'tenant_id' => tenant('id'),
                'issue_id'  => $issue->id,
                'actor_id'  => $actor->id,
                'type'      => 'status_changed',
                'data'      => [
                    'from' => $fromStatus,
                    'to'   => 'closed',
                    'note' => "Auto-closed — contact \"{$contact->name}\" moved{$fromLabel} to branch \"{$newBranch->name}\".",
                ],
            ]);
        }

        // Log branch change on remaining issues (already closed/resolved)
        // so activity log always has a record of the move
        $remainingIssues = Issue::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->whereNotIn('id', $openIssues->pluck('id'))
            ->get(['id']);

        foreach ($remainingIssues as $issue) {
            IssueActivity::create([
                'tenant_id' => tenant('id'),
                'issue_id'  => $issue->id,
                'actor_id'  => $actor->id,
                'type'      => 'contact_moved',
                'data'      => [
                    'contact_name' => $contact->name,
                    'from_branch'  => $oldBranch?->name,
                    'to_branch'    => $newBranch->name,
                    'note'         => "Contact \"{$contact->name}\" moved{$fromLabel} to branch \"{$newBranch->name}\" by {$actor->name}.",
                ],
            ]);
        }

        return $openIssues->count();
    }

    public function bulkSendCode(Request $request)
    {
        $data = $request->validate([
            'contact_ids'   => ['required', 'array', 'min:1'],
            'contact_ids.*' => ['integer'],
            'channel'       => ['required', 'in:email,sms'],
        ]);

        $channel    = $data['channel'];
        $schoolName = \App\Models\School::where('tenant_id', tenant('id'))->value('name');

        // Check SMS config once up-front
        if ($channel === 'sms' && ! TenantSms::isConfigured(tenant('id'))) {
            return back()->withErrors(['bulk' => 'SMS is not configured. Add Twilio credentials in School Settings or via the global .env file.']);
        }

        $contacts = RosterContact::where('tenant_id', tenant('id'))
            ->whereIn('id', $data['contact_ids'])
            ->with('accessCodes')
            ->get();

        $sent    = 0;
        $skipped = 0;

        foreach ($contacts as $contact) {
            $code = $contact->accessCodes->first();

            if (! $code) {
                $skipped++;
                continue;
            }

            if ($channel === 'email') {
                if (! $contact->email) {
                    $skipped++;
                    continue;
                }
                SendAccessCodeViaMail::dispatch(
                    tenant('id'),
                    $code->id,
                    $contact->email,
                    $contact->name,
                    $code->code,
                    $schoolName,
                );
                $sent++;
            } else {
                // SMS — dispatch a queued job so large batches don't block the HTTP response
                if (! $contact->phone) {
                    $skipped++;
                    continue;
                }
                SendAccessCodeViaSms::dispatch(
                    tenant('id'),
                    $code->id,
                    $contact->phone,
                    "Your access code for {$schoolName} is: {$code->code}. Visit the school portal to submit your issue.",
                );
                $sent++;
            }
        }

        $channelLabel = $channel === 'sms' ? 'SMS messages queued for sending in the background' : 'emails queued';
        $msg = "{$sent} access code(s) — {$channelLabel}.";
        if ($skipped) {
            $msg .= " {$skipped} skipped (no access code or missing contact info).";
        }

        return back()->with('ok', $msg);
    }

    private function uniqueCode(int $len = 10): string
    {
        do {
            $code = strtoupper(Str::random($len));
        } while (AccessCode::where('tenant_id', tenant('id'))->where('code', $code)->exists());

        return $code;
    }
}
