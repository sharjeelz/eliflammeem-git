<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Mail\LeadSubmittedMail;
use App\Models\Lead;
use App\Rules\ValidTurnstile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('central.contact');
    }

    public function submit(Request $request): RedirectResponse
    {
        $turnstileRule = app()->environment('local', 'testing') ? 'nullable' : 'required';

        $request->validate([
            'name'                   => ['required', 'string', 'max:150'],
            'email'                  => ['required', 'email', 'max:150'],
            'phone'                  => ['nullable', 'string', 'max:30', 'regex:/^[0-9\+\-\s\(\)]+$/'],
            'school_name'            => ['nullable', 'string', 'max:150'],
            'city'                   => ['nullable', 'string', 'max:100'],
            'package'                => ['required', 'in:starter,growth,pro,enterprise,custom'],
            'message'                => ['nullable', 'string', 'max:1000'],
            'cf-turnstile-response'  => [$turnstileRule, new ValidTurnstile],
            'website'                => ['present', 'max:0'],
        ], [
            'name.required'    => 'Your full name is required.',
            'email.required'   => 'A valid email address is required.',
            'email.email'      => 'Please enter a valid email address.',
            'package.required' => 'Please select a package you are interested in.',
            'package.in'       => 'Please select a valid package.',
            'phone.regex'      => 'Phone number may only contain digits, +, -, spaces, and parentheses.',
            'message.max'      => 'Message may not exceed 1,000 characters.',
        ]);

        $lead = Lead::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'school_name' => $request->school_name,
            'city'        => $request->city,
            'package'     => $request->package,
            'message'     => $request->message,
            'ip_address'  => $request->ip(),
        ]);

        Mail::to(config('mail.from.address'))->queue(new LeadSubmittedMail($lead));

        return redirect(url('/contact'))->with('ok', "Thank you! We'll be in touch soon.");
    }
}
