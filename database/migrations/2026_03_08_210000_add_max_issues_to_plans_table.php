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
            $table->unsignedInteger('max_issues_per_month')->nullable()->after('max_contacts');
        });

        DB::table('plans')->where('key', 'starter')->update(['max_issues_per_month' => 100]);
        DB::table('plans')->where('key', 'growth')->update(['max_issues_per_month' => 500]);
        DB::table('plans')->where('key', 'pro')->update(['max_issues_per_month' => 2000]);
        DB::table('plans')->where('key', 'enterprise')->update(['max_issues_per_month' => null]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('max_issues_per_month');
        });
    }
};
