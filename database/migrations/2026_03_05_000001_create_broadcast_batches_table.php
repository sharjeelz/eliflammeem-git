<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcast_batches', function (Blueprint $t) {
            $t->id();
            $t->uuid('tenant_id')->index();
            $t->string('subject')->nullable();
            $t->text('message');
            $t->enum('channel', ['email', 'sms', 'both']);
            $t->string('audience_type'); // all, filter, specific
            $t->json('audience_filter')->nullable(); // branch_id, role when filter
            $t->unsignedInteger('total_count')->default(0);
            $t->unsignedInteger('sent_count')->default(0);
            $t->unsignedInteger('failed_count')->default(0);
            $t->timestamps();

            $t->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $t->index(['tenant_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcast_batches');
    }
};
