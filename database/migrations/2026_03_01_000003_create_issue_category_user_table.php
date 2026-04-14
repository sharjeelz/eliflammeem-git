<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_category_user', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('issue_category_id')->constrained('issue_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'issue_category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_category_user');
    }
};
