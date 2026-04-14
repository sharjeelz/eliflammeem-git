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
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->string('name'); // Template name (e.g., "welcome_message")
            $table->string('language', 10)->default('en'); // Language code (en, ar, etc.)
            $table->enum('category', ['MARKETING', 'UTILITY', 'AUTHENTICATION'])->default('UTILITY');
            $table->enum('status', ['pending', 'approved', 'rejected', 'disabled'])->default('pending');
            $table->json('components'); // Header, body, footer, buttons as JSON
            $table->string('meta_template_id')->nullable(); // ID from Meta after approval
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->unique(['tenant_id', 'name', 'language']); // One template name per language per tenant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }
};
