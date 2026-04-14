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
        Schema::table('documents', function (Blueprint $table) {
            // Text extraction processing status
            $table->enum('text_extraction_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->after('searchable_content');
            
            $table->text('text_extraction_error')->nullable()->after('text_extraction_status');
            $table->timestamp('text_extracted_at')->nullable()->after('text_extraction_error');
            $table->integer('text_extraction_attempts')->default(0)->after('text_extracted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'text_extraction_status',
                'text_extraction_error',
                'text_extracted_at',
                'text_extraction_attempts',
            ]);
        });
    }
};
