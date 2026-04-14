<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tenant_id')->index();
            $table->unsignedBigInteger('api_key_id')->nullable()->index();
            $table->string('endpoint', 100);
            $table->unsignedSmallInteger('status_code');
            $table->string('ip', 45);
            $table->unsignedInteger('duration_ms')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Composite indexes for common query patterns
            $table->index(['tenant_id', 'created_at']);
            $table->index(['api_key_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
    }
};
