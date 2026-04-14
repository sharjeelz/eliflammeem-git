<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $t->string('name');
            $t->string('code');
            $t->string('city')->nullable();
            $t->string('address')->nullable();
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->json('settings')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'school_id', 'code']);
            $t->index(['tenant_id', 'school_id']);
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
