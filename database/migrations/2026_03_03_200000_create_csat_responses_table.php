<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('csat_responses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->unsignedTinyInteger('rating')->nullable(); // 1–5, null until submitted
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('email_sent_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['issue_id', 'tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('csat_responses');
    }
};
