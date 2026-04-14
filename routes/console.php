<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule daily trend digest email to admins
Schedule::command('trends:send-digest')
    ->dailyAt('08:00')
    ->timezone('America/New_York')
    ->runInBackground();

// Check escalation rules every 15 minutes across all tenants
Schedule::command('escalations:check')
    ->everyFifteenMinutes()
    ->runInBackground();

// Nudge admins about resolved issues with no parent response after 7 days
Schedule::command('issues:nudge-resolved')
    ->dailyAt('09:00')
    ->runInBackground();

// Prune chatbot logs daily for GDPR compliance
// Deletes logs >90 days old; anonymizes IP addresses >30 days old
Schedule::command('chatbot:prune-logs')
    ->dailyAt('02:00')
    ->runInBackground();
