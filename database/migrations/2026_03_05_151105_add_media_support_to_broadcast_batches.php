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
            $table->enum('media_type', ['none', 'image', 'document', 'video', 'audio'])->default('none')->after('message');
            $table->string('media_path')->nullable()->after('media_type');
            $table->string('media_filename')->nullable()->after('media_path');
            $table->string('media_mime_type')->nullable()->after('media_filename');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('broadcast_batches', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_path', 'media_filename', 'media_mime_type']);
        });
    }
};
