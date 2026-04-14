<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantApiKey;
use App\Services\PlanService;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    /**
     * List all API keys for the current tenant.
     * (Redirects to settings page — index handled there.)
     */
    public function index()
    {
        $apiKeys = TenantApiKey::where('tenant_id', tenant('id'))
            ->orderByDesc('created_at')
            ->get();

        return view('tenant.admin.settings.partials.api-keys', compact('apiKeys'));
    }

    /**
     * Create a new API key for the current tenant.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        // Plan gate
        $plan = PlanService::forCurrentTenant();
        if (! $plan->allows('api_access')) {
            return redirect()
                ->route('tenant.admin.settings.edit')
                ->with('_settings_tab', 'api')
                ->with('error', "API access requires a Growth plan or higher. Your current plan is {$plan->planName()}.");
        }

        $result = TenantApiKey::generate(
            tenantId: tenant('id'),
            name: $request->input('name'),
            createdBy: $request->user()?->id,
        );

        return redirect()
            ->route('tenant.admin.settings.edit')
            ->with('_settings_tab', 'api')
            ->with('api_key_plaintext', $result['plaintext'])
            ->with('api_key_name', $result['model']->name);
    }

    /**
     * Revoke an API key.
     */
    public function destroy(int $id)
    {
        $apiKey = TenantApiKey::findOrFail($id);

        abort_unless($apiKey->tenant_id === tenant('id'), 404);

        $apiKey->update(['revoked_at' => now()]);

        return redirect()
            ->route('tenant.admin.settings.edit')
            ->with('_settings_tab', 'api')
            ->with('ok', "API key \"{$apiKey->name}\" has been revoked.");
    }
}
