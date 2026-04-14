<?php

use App\Http\Controllers\Api\V1\ContactUpsertController;
use App\Http\Controllers\Api\V1\Parent\ParentAuthController;
use App\Http\Controllers\Api\V1\Parent\ParentIssueController;
use App\Http\Controllers\Api\V1\Parent\ParentPushTokenController;
use App\Http\Middleware\AuthenticateTenantApiKey;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

// ── External integration API (API key auth) ──────────────────────────────────
Route::middleware([
    AuthenticateTenantApiKey::class,
    'throttle:api_keys',
])->prefix('api/v1')->group(function () {
    Route::post('/contacts', ContactUpsertController::class)->name('api.v1.contacts.upsert');
});

// ── Parent mobile app API (Sanctum token auth) ───────────────────────────────
Route::middleware([
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/v1/parent')->group(function () {

    Route::post('/auth/login', [ParentAuthController::class, 'login'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
        Route::post('/auth/logout', [ParentAuthController::class, 'logout']);
        Route::get('/me',           [ParentAuthController::class, 'me']);

        Route::get('/categories',   [ParentIssueController::class, 'categories']);
        Route::get('/issues',       [ParentIssueController::class, 'index']);
        Route::post('/issues',      [ParentIssueController::class, 'store']);
        Route::get('/issues/{public_id}',        [ParentIssueController::class, 'show']);
        Route::post('/issues/{public_id}/reply',  [ParentIssueController::class, 'reply']);
        Route::post('/issues/{public_id}/close',  [ParentIssueController::class, 'close']);
        Route::post('/issues/{public_id}/reopen', [ParentIssueController::class, 'reopen']);

        Route::post('/push-token',   [ParentPushTokenController::class, 'store']);
        Route::delete('/push-token', [ParentPushTokenController::class, 'destroy']);
    });
});
