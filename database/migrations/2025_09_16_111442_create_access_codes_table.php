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
        Schema::create('access_codes', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->foreignId('roster_contact_id')->nullable()->constrained('roster_contacts')->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $t->string('code', 64);
            $t->enum('channel', ['sms', 'email', 'manual'])->default('manual');
            $t->timestamp('expires_at')->nullable();
            $t->timestamp('used_at')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'code']);
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_codes');
    }
};
