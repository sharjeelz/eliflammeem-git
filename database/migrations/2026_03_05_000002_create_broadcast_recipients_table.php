<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_recipients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('broadcast_batch_id')->constrained('broadcast_batches')->cascadeOnDelete();
            $t->foreignId('contact_id')->constrained('roster_contacts')->cascadeOnDelete();
            $t->string('contact_name');
            $t->string('contact_email')->nullable();
            $t->string('contact_phone')->nullable();
            $t->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $t->text('error_message')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();

            $t->index(['broadcast_batch_id', 'status']);
            $t->index(['broadcast_batch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_recipients');
    }
};
