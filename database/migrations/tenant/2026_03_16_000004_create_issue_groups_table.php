<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('label', 200);          // e.g. "Facilities · Block C · water supply"
            $table->string('theme', 100);           // AI theme key
            $table->unsignedBigInteger('issue_category_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('confidence', 10);       // high | medium | low
            $table->unsignedSmallInteger('issue_count')->default(0);
            $table->string('status', 20)->default('open'); // open | resolved | dismissed
            $table->text('resolved_message')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });

        Schema::create('issue_group_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('issue_group_id');
            $table->unsignedBigInteger('issue_id');
            $table->timestamp('removed_at')->nullable(); // null = in group, set = removed by admin

            $table->unique(['issue_group_id', 'issue_id']);
            $table->index(['tenant_id']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('issue_group_id')->references('id')->on('issue_groups')->cascadeOnDelete();
            $table->foreign('issue_id')->references('id')->on('issues')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_group_items');
        Schema::dropIfExists('issue_groups');
    }
};
