<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\AccessCodeMail;
use App\Models\AccessCode;
use App\Models\RosterContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class AccessCodeController extends Controller
{
    public function create()
    {
        return view('tenant.public.resend');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'                 => ['nullable', 'email'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'external_id'           => ['nullable', 'string', 'max:100'],
            'cf-turnstile-response' => [app()->environment('local', 'testing') ? 'nullable' : 'required', new \App\Rules\ValidTurnstile],
        ]);

        if (! $data['email'] && ! $data['phone'] && ! $data['external_id']) {
            return back()->withErrors(['email' => 'Provide your email, phone, or school ID.'])->withInput();
        }

        $genericResponse = redirect('/')->with('ok', 'If your details are on file, an access code will be sent to you shortly.');

        // Lookup contact by email, phone, or external_id within current tenant
        $contact = RosterContact::where('tenant_id', tenant('id'))
            ->where(function ($q) use ($data) {
                if ($data['email']) {
                    $q->orWhere('email', $data['email']);
                }
                if ($data['phone']) {
                    $q->orWhere('phone', $data['phone']);
                }
                if ($data['external_id']) {
                    $q->orWhere('external_id', $data['external_id']);
                }
            })
            ->first();

        // Return the same generic response regardless of whether the contact exists —
        // distinct error messages would allow enumeration of registered contacts.
        if (! $contact || $contact->deactivated_at || $contact->revoke_reason) {
            return $genericResponse;
        }

        // Check for an existing active (unused + not expired) code
        $activeCode = AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->active()
            ->first();

        if ($activeCode) {
            // Re-send the existing code silently rather than revealing its existence
            if ($contact->email) {
                Mail::to($contact->email)->queue(new AccessCodeMail($activeCode->code, tenant('data.name'), $contact->name, tenant('id')));
            }

            return $genericResponse;
        }

        // No active code — either first time, expired, or used. Renew or create.
        $code = AccessCode::where('tenant_id', tenant('id'))
            ->where('roster_contact_id', $contact->id)
            ->first();

        if ($code) {
            // Renew the existing record (reset expiry and clear used_at)
            $code->update([
                'expires_at' => now()->addDays(7),
                'used_at'    => null,
            ]);
        } else {
            $code = AccessCode::create([
                'tenant_id'         => tenant('id'),
                'roster_contact_id' => $contact->id,
                'branch_id'         => $contact->branch_id,
                'code'              => $this->generateUniqueCode(),
                'channel'           => ! empty($data['email']) ? 'email' : 'manual',
                'expires_at'        => now()->addDays(7),
            ]);
        }

        if ($contact->email) {
            Mail::to($contact->email)->queue(new AccessCodeMail($code->code, tenant('data.name'), $contact->name, tenant('id')));
        }

        // TODO (later): integrate SMS provider if $contact->phone is present

        return $genericResponse;
    }

    private function generateUniqueCode(int $len = 10): string
    {
        do {
            $c = strtoupper(\Illuminate\Support\Str::random($len));
        } while (\App\Models\AccessCode::where('tenant_id', tenant('id'))->where('code', $c)->exists());

        return $c;
    }
}
