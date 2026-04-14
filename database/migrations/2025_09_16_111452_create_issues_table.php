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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('public_id', 20);
            $table->enum('source_role', ['parent', 'teacher', 'admin'])->nullable();
            $table->unsignedBigInteger('roster_contact_id')->nullable();
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable();
            $table->string('subcategory')->nullable();
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new')->index();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('low')->index();
            $table->json('ai_summary')->nullable();
            $table->json('meta')->nullable();

            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->timestamp('first_response_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->unsignedSmallInteger('sla_hours')->nullable();
            $table->dateTime('sla_due_at')->nullable()->index();
            // $table->foreignId('category_id')->nullable()->constrained('issue_categories')->nullOnDelete()->index();
            $table->timestamp('last_activity_at')->nullable()->index();

            $table->unique(['tenant_id', 'public_id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
