<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roster_contacts', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();                    // stores UUID string
            $t->foreignId('school_id')->nullable()->constrained('schools')->cascadeOnDelete();
            $t->foreignId('branch_id')->nullable()->constrained('branches')->cascadeOnDelete();
            $t->enum('role', ['parent', 'teacher', 'admin']);
            $t->string('external_id')->nullable();              // e.g. student_id / staff_id from SIS
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();

            $t->index(['tenant_id', 'branch_id']);
            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();     // scoped uniqueness
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roster_contacts');
    }
};
