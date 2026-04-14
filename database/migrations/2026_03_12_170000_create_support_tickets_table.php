<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id', 36)->index(); // UUID — not a FK, central DB reference
            $table->string('tenant_name', 150)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name', 150)->nullable();
            $table->string('user_email', 150)->nullable();
            $table->string('subject', 150);
            $table->text('message');
            $table->string('type', 30)->default('question');    // bug|question|billing|feature_request|other
            $table->string('priority', 20)->default('medium');  // low|medium|high|urgent
            $table->string('status', 20)->default('open');      // open|in_progress|resolved
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
