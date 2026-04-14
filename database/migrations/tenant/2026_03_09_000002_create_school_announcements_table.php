<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('title', 200);
            $table->text('body');
            $table->unsignedBigInteger('issue_category_id')->nullable();
            $table->timestamp('published_at')->nullable(); // null = draft
            $table->unsignedBigInteger('created_by');     // User id — no FK (user may be deleted)
            $table->timestamps();

            $table->index(['tenant_id', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_announcements');
    }
};
