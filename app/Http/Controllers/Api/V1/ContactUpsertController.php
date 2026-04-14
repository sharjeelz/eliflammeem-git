<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ContactUpsertRequest;
use App\Models\AccessCode;
use App\Models\Branch;
use App\Models\RosterContact;
use App\Models\School;
use App\Services\PlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class ContactUpsertController extends Controller
{
    public function __invoke(ContactUpsertRequest $request): JsonResponse
    {
        $data = $request->validated();

        // 1. Resolve branch — validate if provided, else auto-assign if school has only one
        $branches = Branch::where('tenant_id', tenant('id'))->get(['id']);

        if (! empty($data['branch_id'])) {
            if (! $branches->contains('id', $data['branch_id'])) {
                return response()->json([
                    'error'   => 'Validation failed.',
                    'details' => ['branch_id' => ['The specified branch does not exist or does not belong to this tenant.']],
                ], 422);
            }
            $resolvedBranchId = $data['branch_id'];
        } else {
            // Auto-assign when the school has exactly one branch (same as CSV import)
            $resolvedBranchId = $branches->count() === 1 ? $branches->first()->id : null;
        }

        // 2. Upsert logic — find existing contact
        $existing = null;

        if (! empty($data['external_id'])) {
            $existing = RosterContact::withoutGlobalScopes()
                ->where('tenant_id', tenant('id'))
                ->where('external_id', $data['external_id'])
                ->first();
        }

        if (! $existing && ! empty($data['email'])) {
            $existing = RosterContact::withoutGlobalScopes()
                ->where('tenant_id', tenant('id'))
                ->where('email', $data['email'])
                ->first();
        }

        if (! $existing && ! empty($data['phone']) && empty($data['email'])) {
            $existing = RosterContact::withoutGlobalScopes()
                ->where('tenant_id', tenant('id'))
                ->where('phone', $data['phone'])
                ->first();
        }

        // 3. Update or create
        if ($existing) {
            $updatePayload = [
                'name'      => $data['name'],
                'role'      => $data['role'],
                'email'     => $data['email'] ?? $existing->email,
                'phone'     => $data['phone'] ?? $existing->phone,
                'branch_id' => $resolvedBranchId ?? $existing->branch_id,
            ];

            if (! empty($data['external_id'])) {
                $updatePayload['external_id'] = $data['external_id'];
            }

            // Reactivate if is_active=true and currently deactivated
            $isActive = $data['is_active'] ?? true;
            if ($isActive && $existing->deactivated_at !== null) {
                $updatePayload['deactivated_at'] = null;
                $updatePayload['revoke_reason']  = null;
            }

            $existing->update($updatePayload);

            return response()->json([
                'status'  => 'updated',
                'contact' => $existing->fresh()->toArray(),
            ], 200);
        }

        // 4. New contact — check plan contact limit first
        $plan         = PlanService::forCurrentTenant();
        $contactCount = RosterContact::where('tenant_id', tenant('id'))->whereNull('deactivated_at')->count();

        if (! $plan->withinLimit('max_contacts', $contactCount)) {
            return response()->json([
                'error' => "Plan limit reached. Your plan ({$plan->planName()}) allows a maximum of {$plan->limitLabel('max_contacts')} active contacts.",
            ], 422);
        }

        $schoolId = School::where('tenant_id', tenant('id'))->value('id');

        $contact = RosterContact::create([
            'tenant_id'   => tenant('id'),
            'school_id'   => $schoolId,
            'branch_id'   => $resolvedBranchId,
            'role'        => $data['role'],
            'name'        => $data['name'],
            'email'       => $data['email'] ?? null,
            'phone'       => $data['phone'] ?? null,
            'external_id' => $data['external_id'] ?? null,
        ]);

        // Auto-generate an access code so the contact can use the portal immediately
        $code = AccessCode::create([
            'tenant_id'         => tenant('id'),
            'roster_contact_id' => $contact->id,
            'branch_id'         => $resolvedBranchId,
            'code'              => $this->uniqueCode(),
            'channel'           => 'api',
            'expires_at'        => now()->addDays(7),
        ]);

        return response()->json([
            'status'      => 'created',
            'contact'     => $contact->toArray(),
            'access_code' => $code->code,
        ], 201);
    }

    private function uniqueCode(int $len = 10): string
    {
        do {
            $code = strtoupper(Str::random($len));
        } while (AccessCode::where('tenant_id', tenant('id'))->where('code', $code)->exists());

        return $code;
    }
}
