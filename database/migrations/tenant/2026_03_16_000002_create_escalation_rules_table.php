<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('escalation_rules', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->string('name', 150);
            $table->boolean('is_active')->default(true);
            $table->string('trigger_status', 20); // new | in_progress | resolved
            $table->unsignedSmallInteger('hours_threshold');
            $table->string('priority_filter', 20)->nullable(); // null = any
            $table->string('action_notify_role', 30)->nullable(); // admin | branch_manager | both
            $table->boolean('action_bump_priority')->default(false);
            $table->string('scope_type', 20)->default('global'); // global | branch | category
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['tenant_id', 'is_active']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('escalation_rules');
    }
};
