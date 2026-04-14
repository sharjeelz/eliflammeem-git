<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old constraint and replace with expanded set
        DB::statement('ALTER TABLE access_codes DROP CONSTRAINT IF EXISTS access_codes_channel_check');
        DB::statement("ALTER TABLE access_codes ADD CONSTRAINT access_codes_channel_check CHECK (channel IN ('sms','email','manual','csv','api'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE access_codes DROP CONSTRAINT IF EXISTS access_codes_channel_check');
        DB::statement("ALTER TABLE access_codes ADD CONSTRAINT access_codes_channel_check CHECK (channel IN ('sms','email','manual'))");
    }
};
