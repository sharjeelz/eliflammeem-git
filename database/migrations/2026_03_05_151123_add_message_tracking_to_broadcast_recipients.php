<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('broadcast_recipients', function (Blueprint $table) {
            $table->string('message_id')->nullable()->after('contact_phone'); // WhatsApp/SMS provider message ID
            $table->enum('delivery_status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending')->after('status');
            $table->timestamp('delivered_at')->nullable()->after('sent_at');
            $table->timestamp('read_at')->nullable()->after('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_recipients', function (Blueprint $table) {
            $table->dropColumn(['message_id', 'delivery_status', 'delivered_at', 'read_at']);
        });
    }
};
