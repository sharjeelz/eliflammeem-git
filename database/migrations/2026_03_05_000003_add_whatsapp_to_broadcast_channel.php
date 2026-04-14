<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE broadcast_batches DROP CONSTRAINT broadcast_batches_channel_check');
        DB::statement("ALTER TABLE broadcast_batches ADD CONSTRAINT broadcast_batches_channel_check CHECK (channel IN ('email', 'sms', 'whatsapp', 'both'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE broadcast_batches DROP CONSTRAINT broadcast_batches_channel_check');
        DB::statement("ALTER TABLE broadcast_batches ADD CONSTRAINT broadcast_batches_channel_check CHECK (channel IN ('email', 'sms', 'both'))");
    }
};
