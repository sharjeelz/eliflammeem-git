<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\Branch;
use App\Models\RosterContact;
use App\Models\School;
use App\Models\SchoolKudo;
use Illuminate\Http\Request;

class KudoSubmitController extends Controller
{
    public function create()
    {
        $school = School::where('tenant_id', tenant('id'))->first();

        if ($school?->status === 'inactive') {
            return redirect('/')->with('error', 'The school portal is currently suspended.');
        }

        $categories = \App\Models\IssueCategory::where('tenant_id', tenant('id'))->get();

        return view('tenant.public.compliment', compact('school', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'website'               => ['present', 'max:0'],
            'cf-turnstile-response' => [app()->environment('local', 'testing') ? 'nullable' : 'required', new \App\Rules\ValidTurnstile],
            'code'                  => ['required', 'string', 'max:64'],
            'category_id'           => ['nullable', 'integer'],
            'message'               => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $school = School::where('tenant_id', tenant('id'))->first();

        if ($school?->status === 'inactive') {
            return back()->withErrors(['code' => 'The school portal is currently suspended.'])->withInput();
        }

        // Verify the access code belongs to this tenant.
        // For compliments we only check the code is valid and not expired —
        // used_at does NOT block it because the contact may have already submitted an issue.
        $code = AccessCode::where('tenant_id', tenant('id'))
            ->where('code', $data['code'])
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->first();

        if (! $code) {
            return back()->withErrors(['code' => 'Access code is invalid or expired.'])->withInput();
        }

        $contact = RosterContact::find($code->roster_contact_id);

        if ($contact?->deactivated_at) {
            return back()->withErrors(['code' => 'Your account is no longer active.'])->withInput();
        }

        $branchId = $code->branch_id
            ?: $contact?->branch_id
            ?: Branch::where('tenant_id', tenant('id'))
                ->where('school_id', $school->id)
                ->orderBy('id')
                ->value('id');

        // Validate category belongs to tenant if provided
        if (! empty($data['category_id'])) {
            $categoryExists = \App\Models\IssueCategory::where('tenant_id', tenant('id'))
                ->where('id', $data['category_id'])
                ->exists();
            if (! $categoryExists) {
                $data['category_id'] = null;
            }
        }

        SchoolKudo::create([
            'tenant_id'         => tenant('id'),
            'branch_id'         => $branchId,
            'roster_contact_id' => $code->roster_contact_id,
            'issue_category_id' => $data['category_id'] ?? null,
            'message'           => $data['message'],
        ]);

        // DO NOT stamp used_at on the access code — compliments don't consume it
        // The contact must still be able to submit issues

        return redirect()->route('tenant.public.home')
            ->with('ok', 'Thank you for sharing your kind words! The school team appreciates your feedback.');
    }
}
