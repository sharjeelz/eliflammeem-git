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
        Schema::table('broadcast_batches', function (Blueprint $table) {
            $table->foreignId('whatsapp_template_id')->nullable()->after('media_mime_type')->constrained('whatsapp_templates')->nullOnDelete();
            $table->json('template_parameters')->nullable()->after('whatsapp_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_batches', function (Blueprint $table) {
            $table->dropForeign(['whatsapp_template_id']);
            $table->dropColumn(['whatsapp_template_id', 'template_parameters']);
        });
    }
};
