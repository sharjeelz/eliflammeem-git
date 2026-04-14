<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_user', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->string('title')->nullable();
            $t->timestamps();

            $t->unique(['tenant_id', 'branch_id', 'user_id']);
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_user');
    }
};
