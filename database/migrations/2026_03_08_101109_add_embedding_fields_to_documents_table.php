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
            $table->string('embedding_status', 20)->default('pending')->after('text_extraction_attempts')
                ->comment('Status: pending, processing, completed, failed');
            $table->text('embedding_error')->nullable()->after('embedding_status')
                ->comment('Error message if embedding generation failed');
            $table->timestamp('embeddings_generated_at')->nullable()->after('embedding_error')
                ->comment('When embeddings were successfully generated');
            $table->unsignedInteger('chunk_count')->default(0)->after('embeddings_generated_at')
                ->comment('Number of chunks created for this document');
            $table->unsignedTinyInteger('embedding_attempts')->default(0)->after('chunk_count')
                ->comment('Number of times embedding generation was attempted');

            $table->index('embedding_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['embedding_status']);
            $table->dropColumn([
                'embedding_status',
                'embedding_error',
                'embeddings_generated_at',
                'chunk_count',
                'embedding_attempts',
            ]);
        });
    }
};
