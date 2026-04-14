<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_escalations', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->unsignedBigInteger('issue_id');
            $table->unsignedBigInteger('escalation_rule_id');
            $table->timestamp('fired_at');
            $table->json('action_taken')->nullable();

            // Prevents double-firing the same rule on the same issue
            $table->unique(['issue_id', 'escalation_rule_id']);
            $table->index(['tenant_id', 'fired_at']);

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('issue_id')->references('id')->on('issues')->cascadeOnDelete();
            $table->foreign('escalation_rule_id')->references('id')->on('escalation_rules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_escalations');
    }
};
