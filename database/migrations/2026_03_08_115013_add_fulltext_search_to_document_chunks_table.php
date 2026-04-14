<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add tsvector column for PostgreSQL full-text search
        DB::statement('ALTER TABLE document_chunks ADD COLUMN content_tsv tsvector');
        
        // Create GIN index for full-text search performance
        DB::statement('CREATE INDEX document_chunks_content_tsv_idx ON document_chunks USING GIN (content_tsv)');
        
        // Create trigger to automatically update tsvector when content changes
        DB::statement("
            CREATE OR REPLACE FUNCTION document_chunks_content_tsv_trigger() RETURNS trigger AS $$
            BEGIN
                NEW.content_tsv := to_tsvector('english', COALESCE(NEW.content, ''));
                RETURN NEW;
            END
            $$ LANGUAGE plpgsql;
        ");
        
        DB::statement('
            CREATE TRIGGER document_chunks_content_tsv_update 
            BEFORE INSERT OR UPDATE ON document_chunks 
            FOR EACH ROW 
            EXECUTE FUNCTION document_chunks_content_tsv_trigger();
        ');
        
        // Populate tsvector for existing rows
        DB::statement("UPDATE document_chunks SET content_tsv = to_tsvector('english', COALESCE(content, ''))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS document_chunks_content_tsv_update ON document_chunks');
        DB::statement('DROP FUNCTION IF EXISTS document_chunks_content_tsv_trigger()');
        DB::statement('DROP INDEX IF EXISTS document_chunks_content_tsv_idx');
        
        Schema::table('document_chunks', function (Blueprint $table) {
            $table->dropColumn('content_tsv');
        });
    }
};
