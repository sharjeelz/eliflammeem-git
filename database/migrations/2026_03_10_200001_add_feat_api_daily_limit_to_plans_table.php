<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // null = unlimited, 0 = no access, positive = daily cap
            $table->unsignedInteger('feat_api_daily_limit')->nullable()->default(0)->after('feat_api_access');
        });

        // Set per-plan values
        DB::table('plans')->where('key', 'starter')->update(['feat_api_daily_limit' => 0]);
        DB::table('plans')->where('key', 'growth')->update(['feat_api_daily_limit' => 500]);
        DB::table('plans')->where('key', 'pro')->update(['feat_api_daily_limit' => 5000]);
        DB::table('plans')->where('key', 'enterprise')->update(['feat_api_daily_limit' => null]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('feat_api_daily_limit');
        });
    }
};
