<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->float('confidence')->default(0);
            $table->integer('chunks_found')->default(0);
            $table->boolean('used_fallback')->default(false);
            $table->json('metadata_filters')->nullable();
            $table->json('sources')->nullable();
            $table->integer('response_ms')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_logs');
    }
};
