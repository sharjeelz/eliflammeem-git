<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->timestamp('status_entered_at')->nullable()->after('last_activity_at');
        });

        // Backfill: use last_activity_at if available, otherwise created_at
        DB::statement('UPDATE issues SET status_entered_at = COALESCE(last_activity_at, created_at)');
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('status_entered_at');
        });
    }
};
