<?php

use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains') as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', function () {
            $plans = \App\Models\Plan::orderByRaw("CASE key WHEN 'starter' THEN 1 WHEN 'growth' THEN 2 WHEN 'pro' THEN 3 WHEN 'enterprise' THEN 4 ELSE 5 END")->get();
            return view('welcome', compact('plans'));
        });

        Route::get('/terms', fn () => view('central.terms'))->name('central.terms');
        Route::get('/privacy', fn () => view('central.privacy'))->name('central.privacy');
        Route::get('/contact', [\App\Http\Controllers\Central\ContactController::class, 'show'])->name('central.contact');
        Route::post('/contact', [\App\Http\Controllers\Central\ContactController::class, 'submit'])->name('central.contact.submit')->middleware('throttle:5,1');
        Route::get('/pitch', fn () => view('central.pitch'))->name('central.pitch');
    });
}

// WhatsApp webhook - must be publicly accessible
// Meta will call: GET /whatsapp/webhook/{webhookId} for verification
// Meta will call: POST /whatsapp/webhook/{webhookId} for status updates
// {webhookId} is an opaque per-tenant secret stored in school settings (not the tenant UUID)
Route::get('/whatsapp/webhook/{webhookId}', [WhatsAppWebhookController::class, 'verify'])->name('whatsapp.webhook.verify');
Route::post('/whatsapp/webhook/{webhookId}', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook.handle');
