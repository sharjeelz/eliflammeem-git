<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issue_activities', function (Blueprint $table) {
            // Add tenant_id after id; nullable so existing rows aren't rejected,
            // then backfill from the parent issue row.
            $table->string('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // Backfill tenant_id from the related issues row
        DB::statement('UPDATE issue_activities ia SET tenant_id = i.tenant_id FROM issues i WHERE i.id = ia.issue_id');
    }

    public function down(): void
    {
        Schema::table('issue_activities', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
