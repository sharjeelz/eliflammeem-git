<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('broadcast_recipients', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        // Backfill from the parent broadcast_batches row
        DB::statement(<<<'SQL'
            UPDATE broadcast_recipients r
            SET tenant_id = b.tenant_id
            FROM broadcast_batches b
            WHERE r.broadcast_batch_id = b.id
        SQL);

        // Make non-nullable now that all rows are filled
        Schema::table('broadcast_recipients', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('broadcast_recipients', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });
    }
};
