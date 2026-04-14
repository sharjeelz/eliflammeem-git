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
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            // Add fields for imported Meta templates
            $table->string('meta_template_name')->nullable()->after('name'); // The actual name in Meta
            $table->text('description')->nullable()->after('category'); // Description for admins
            $table->json('parameters')->nullable()->after('components'); // Parameter names/types
            $table->boolean('is_active')->default(true)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_templates', function (Blueprint $table) {
            $table->dropColumn(['meta_template_name', 'description', 'parameters', 'is_active']);
        });
    }
};
