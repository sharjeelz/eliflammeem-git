<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CsatResponse;
use App\Models\School;

class CsatController extends Controller
{
    public function store(string $token, int $rating)
    {
        abort_unless(in_array($rating, [1, 2, 3, 4, 5], true), 404);

        // BelongsToTenant global scope already filters by tenant
        $csat = CsatResponse::where('token', $token)->firstOrFail();

        $alreadySubmitted = (bool) $csat->submitted_at;

        if (! $alreadySubmitted) {
            $csat->update([
                'rating'       => $rating,
                'submitted_at' => now(),
            ]);
        }

        $school = School::where('tenant_id', tenant('id'))->first();

        return view('tenant.public.csat_thankyou', [
            'rating'  => $alreadySubmitted ? $csat->rating : $rating,
            'already' => $alreadySubmitted,
            'school'  => $school,
        ]);
    }
}
