<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            // Branch-level report queries: WHERE tenant_id = ? AND branch_id = ? AND created_at BETWEEN ?
            $table->index(['tenant_id', 'branch_id', 'created_at'], 'issues_tenant_branch_created_idx');

            // Staff-level report queries: WHERE tenant_id = ? AND assigned_user_id = ? AND created_at BETWEEN ?
            $table->index(['tenant_id', 'assigned_user_id', 'created_at'], 'issues_tenant_assignee_created_idx');

            // Category-level report queries
            $table->index(['tenant_id', 'issue_category_id', 'created_at'], 'issues_tenant_category_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex('issues_tenant_branch_created_idx');
            $table->dropIndex('issues_tenant_assignee_created_idx');
            $table->dropIndex('issues_tenant_category_created_idx');
        });
    }
};
