<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentPushTokenController extends Controller
{
    /**
     * POST /api/v1/parent/push-token
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => ['required', 'string', 'max:255', 'starts_with:ExponentPushToken['],
            'platform' => ['required', 'string', 'in:ios,android'],
        ]);

        $request->user()->update([
            'expo_push_token' => $request->token,
            'device_platform' => $request->platform,
        ]);

        return response()->json(['message' => 'Push token registered.']);
    }

    /**
     * DELETE /api/v1/parent/push-token
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->update([
            'expo_push_token' => null,
            'device_platform' => null,
        ]);

        return response()->json(['message' => 'Push token removed.']);
    }
}
