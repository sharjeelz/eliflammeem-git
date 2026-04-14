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
        Schema::create('issue_ai_analysis', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index(); // for multi-tenant support
            $table->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();

            $table->string('analysis_type'); // e.g., 'sentiment', 'predictive_sla', 'category'
            $table->json('result');          // JSON for storing output
            $table->float('confidence')->nullable(); // optional confidence
            $table->string('model_version')->nullable(); // e.g., 'gpt-4', 'finetuned-v1'
            $table->index(['tenant_id', 'analysis_type']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_ai_analysis');
    }
};
