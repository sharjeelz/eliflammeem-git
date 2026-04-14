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
            $table->string('submission_type')->default('complaint')->after('is_anonymous');
        });

        // Backfill all existing rows
        DB::table('issues')->update(['submission_type' => 'complaint']);
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn('submission_type');
        });
    }
};
