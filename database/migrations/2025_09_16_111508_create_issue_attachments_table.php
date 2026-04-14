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
        Schema::create('issue_attachments', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->foreignId('issue_id')->constrained('issues')->cascadeOnDelete();
            $t->foreignId('issue_message_id')->nullable()->constrained('issue_messages')->nullOnDelete();
            $t->string('disk')->default('public');
            $t->string('path');
            $t->string('mime')->nullable();
            $t->unsignedBigInteger('size')->nullable();
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
        Schema::dropIfExists('issue_attachments');
    }
};
