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
            $table->boolean('feat_api_access')->default(false)->after('feat_two_factor');
        });

        // Enable for growth, pro, enterprise
        DB::table('plans')->whereIn('key', ['growth', 'pro', 'enterprise'])->update(['feat_api_access' => true]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('feat_api_access');
        });
    }
};
