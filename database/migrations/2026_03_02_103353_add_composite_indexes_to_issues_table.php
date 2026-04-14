<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->index(['tenant_id', 'status'], 'issues_tenant_status_idx');
            $table->index(['tenant_id', 'assigned_user_id'], 'issues_tenant_assigned_idx');
            $table->index(['tenant_id', 'roster_contact_id'], 'issues_tenant_contact_idx');
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex('issues_tenant_status_idx');
            $table->dropIndex('issues_tenant_assigned_idx');
            $table->dropIndex('issues_tenant_contact_idx');
        });
    }
};
