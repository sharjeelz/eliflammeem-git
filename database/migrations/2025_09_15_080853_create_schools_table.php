<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->string('name');
            $t->string('code')->unique();        // each tenant is one school, but keep row for settings/branding
            $t->string('city')->nullable();
            $t->enum('status', ['active', 'inactive'])->default('active');
            $t->json('settings')->nullable();
            $t->timestamps();
            $t->unique(['tenant_id', 'code']);
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
