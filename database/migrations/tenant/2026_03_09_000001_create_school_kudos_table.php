<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_kudos', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('roster_contact_id')->nullable();
            $table->unsignedBigInteger('issue_category_id')->nullable();
            $table->text('message');
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_kudos');
    }
};
