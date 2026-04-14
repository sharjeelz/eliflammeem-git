<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use App\Models\AccessCode;
use App\Models\RosterContact;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParentAuthController extends Controller
{
    /**
     * POST /api/v1/parent/auth/login
     * Accepts an access code OR a student/school external_id.
     * Returns a Sanctum token — does NOT stamp used_at (that happens on issue submission).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:100']]);

        $input    = trim($request->code);
        $tenantId = tenant('id');

        // Step 1: Try direct access code match
        $access = AccessCode::where('tenant_id', $tenantId)
            ->where('code', $input)
            ->with('contact')
            ->first();

        $contact = $access?->contact;

        // Step 2: Try external_id fallback
        if (! $contact) {
            $contact = RosterContact::where('tenant_id', $tenantId)
                ->where('external_id', $input)
                ->first();
        }

        if (! $contact) {
            return response()->json([
                'error'   => 'invalid_code',
                'message' => 'Access code or ID is invalid.',
            ], 401);
        }

        if ($contact->deactivated_at || $contact->revoke_reason) {
            return response()->json([
                'error'   => 'account_inactive',
                'message' => 'Your account is no longer active. Please contact the school.',
            ], 403);
        }

        // Revoke any previous tokens for this contact on this device to avoid accumulation
        $contact->tokens()->where('name', 'parent-app')->delete();

        $token = $contact->createToken('parent-app')->plainTextToken;

        $school = School::where('tenant_id', $tenantId)->first();

        return response()->json([
            'data' => [
                'token' => $token,
                'contact' => [
                    'id'    => $contact->id,
                    'name'  => $contact->name,
                    'role'  => $contact->role,
                    'email' => $contact->email,
                ],
                'school' => [
                    'name'     => $school?->name,
                    'logo_url' => $school?->logo_url,
                    'color'    => $school?->setting('primary_color'),
                ],
            ],
            'message' => 'Login successful.',
        ]);
    }

    /**
     * POST /api/v1/parent/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * GET /api/v1/parent/me
     */
    public function me(Request $request): JsonResponse
    {
        $contact = $request->user();
        $school  = School::where('tenant_id', tenant('id'))->first();

        return response()->json([
            'data' => [
                'contact' => [
                    'id'    => $contact->id,
                    'name'  => $contact->name,
                    'role'  => $contact->role,
                    'email' => $contact->email,
                ],
                'school' => [
                    'name'     => $school?->name,
                    'logo_url' => $school?->logo_url,
                    'color'    => $school?->setting('primary_color'),
                ],
            ],
        ]);
    }
}
