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
        Schema::create('issue_messages', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $t->enum('sender', ['parent', 'teacher', 'admin', 'system']);
            $t->text('message')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'issue_id']);
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issue_messages');
    }
};
