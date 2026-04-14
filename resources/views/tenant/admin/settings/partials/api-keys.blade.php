@php
    $planAllowApi = \App\Services\PlanService::forCurrentTenant()->allows('api_access');
    $apiKeys      = \App\Models\TenantApiKey::where('tenant_id', tenant('id'))->orderByDesc('created_at')->get();
    $keyIds       = $apiKeys->pluck('id');
    $callsToday   = \DB::table('api_request_logs')
        ->selectRaw('api_key_id, COUNT(*) as total')
        ->whereIn('api_key_id', $keyIds)
        ->whereDate('created_at', today())
        ->groupBy('api_key_id')
        ->pluck('total', 'api_key_id');
    $calls30d     = \DB::table('api_request_logs')
        ->selectRaw('api_key_id, COUNT(*) as total')
        ->whereIn('api_key_id', $keyIds)
        ->where('created_at', '>=', now()->subDays(30))
        ->groupBy('api_key_id')
        ->pluck('total', 'api_key_id');
@endphp

@if(! $planAllowApi)
    {{-- Plan gate --}}
    <div class="card border-top-0 rounded-top-0 mb-5">
        <div class="card-body pt-7 text-center py-12">
            <i class="ki-duotone ki-lock-2 fs-3x text-warning mb-4"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            <h4 class="fw-bold text-gray-800 mb-2">API access requires Growth plan or higher</h4>
            <p class="text-muted fs-6">Upgrade your plan to generate API keys and integrate external systems with the Roster Contacts API.</p>
        </div>
    </div>
@else

    {{-- Flash: new key plaintext (shown only once) --}}
    @if(session('api_key_plaintext'))
    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-6 p-4 gap-4">
        <i class="ki-duotone ki-information-5 fs-2tx text-warning flex-shrink-0 mt-1">
            <span class="path1"></span><span class="path2"></span><span class="path3"></span>
        </i>
        <div>
            <div class="fw-bold text-gray-800 mb-2">
                API key "{{ session('api_key_name') }}" created — copy it now, it will never be shown again.
            </div>
            <code class="fs-7 text-break bg-white border rounded px-3 py-2 d-block font-monospace user-select-all">{{ session('api_key_plaintext') }}</code>
            <div class="text-muted fs-8 mt-1">Store this securely. Treat it like a password.</div>
        </div>
    </div>
    @endif

    {{-- Create new key form --}}
    <div class="card mb-6">
        <div class="card-header py-4 min-h-auto">
            <h3 class="card-title fw-bold fs-6">Create New API Key</h3>
        </div>
        <div class="card-body py-5">
            <form method="POST" action="{{ route('tenant.admin.settings.api_keys.store') }}">
                @csrf
                <div class="d-flex gap-3 align-items-end">
                    <div class="flex-grow-1">
                        <label class="form-label fw-semibold required">Key Name</label>
                        <input type="text" name="name" class="form-control form-control-solid @error('name') is-invalid @enderror"
                               placeholder="e.g. SIS Integration, PowerSchool Sync"
                               maxlength="100" required value="{{ old('name') }}">
                        @error('name')<div class="text-danger fs-7 mt-1">{{ $message }}</div>@enderror
                    </div>
                    <div class="flex-shrink-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-key fs-4"><span class="path1"></span><span class="path2"></span></i>
                            Generate Key
                        </button>
                    </div>
                </div>
                <div class="text-muted fs-7 mt-2">
                    The plaintext key is shown <strong>once</strong> immediately after creation. It cannot be retrieved afterwards.
                </div>
            </form>
        </div>
    </div>

    {{-- Existing keys table --}}
    <div class="card">
        <div class="card-header py-4 min-h-auto">
            <h3 class="card-title fw-bold fs-6">Active API Keys</h3>
            <div class="card-toolbar">
                <span class="text-muted fs-7">{{ $apiKeys->count() }} key(s)</span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($apiKeys->isEmpty())
                <div class="text-center py-10 text-muted fs-6">
                    No API keys yet. Create one above.
                </div>
            @else
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-3 px-6">
                    <thead>
                        <tr class="fw-semibold text-muted fs-7 text-uppercase">
                            <th class="ps-6">Name</th>
                            <th>Prefix</th>
                            <th>Status</th>
                            <th>Today</th>
                            <th>30 Days</th>
                            <th>Last Used</th>
                            <th>Created</th>
                            <th class="text-end pe-6">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($apiKeys as $key)
                        <tr>
                            <td class="ps-6 fw-semibold text-gray-800">{{ $key->name }}</td>
                            <td>
                                <code class="font-monospace fs-7 text-muted">{{ $key->key_prefix }}...</code>
                            </td>
                            <td>
                                @if($key->revoked_at)
                                    <span class="badge badge-light-danger">Revoked</span>
                                @elseif($key->expires_at && $key->expires_at->isPast())
                                    <span class="badge badge-light-warning">Expired</span>
                                @else
                                    <span class="badge badge-light-success">Active</span>
                                @endif
                            </td>
                            <td class="text-muted fs-7">
                                {{ $callsToday[$key->id] ?? 0 }}
                            </td>
                            <td class="text-muted fs-7">
                                {{ $calls30d[$key->id] ?? 0 }}
                            </td>
                            <td class="text-muted fs-7">
                                {{ $key->last_used_at ? $key->last_used_at->diffForHumans() : 'Never' }}
                            </td>
                            <td class="text-muted fs-7">
                                {{ $key->created_at->format('d M Y') }}
                            </td>
                            <td class="text-end pe-6">
                                @if(! $key->revoked_at)
                                <form method="POST"
                                      action="{{ route('tenant.admin.settings.api_keys.destroy', $key->id) }}"
                                      onsubmit="return confirm('Revoke key \"{{ addslashes($key->name) }}\"? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light-danger">
                                        <i class="ki-duotone ki-cross fs-5"><span class="path1"></span><span class="path2"></span></i>
                                        Revoke
                                    </button>
                                </form>
                                @else
                                    <span class="text-muted fs-7">—</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

@endif
