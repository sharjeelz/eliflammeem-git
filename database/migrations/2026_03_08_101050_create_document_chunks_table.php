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
        // Check if pgvector is available (PostgreSQL only)
        $hasPgvector = false;
        if (config('database.default') === 'pgsql') {
            try {
                // Test if vector extension exists
                $result = DB::select("SELECT * FROM pg_available_extensions WHERE name = 'vector'");
                if (!empty($result)) {
                    // Extension is available, try to enable it
                    DB::unprepared('CREATE EXTENSION IF NOT EXISTS vector');
                    $hasPgvector = true;
                } else {
                    echo "\n";
                    echo "⚠️  WARNING: pgvector extension not available on this PostgreSQL server.\n";
                    echo "   Phase 3 features (vector embeddings) will not work without it.\n";
                    echo "   \n";
                    echo "   To install pgvector:\n";
                    echo "   - Docker: docker run -d -p 5432:5432 pgvector/pgvector:pg16\n";
                    echo "   - Ubuntu: sudo apt install postgresql-16-pgvector\n";
                    echo "   - See: https://github.com/pgvector/pgvector#installation\n";
                    echo "\n";
                }
            } catch (\Exception $e) {
                // Silently handle - pgvector not available
            }
        }

        // Create the table without vector column first
        Schema::create('document_chunks', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id');
            $table->foreignId('document_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('chunk_index')->comment('Sequential chunk number within document');
            $table->text('content')->comment('Text content of this chunk (~500 words)');
            $table->json('metadata')->nullable()->comment('Additional metadata (word count, position, etc.)');
            $table->timestamps();

            // Indexes for tenant isolation and quick lookups
            $table->index('tenant_id');
            $table->index(['document_id', 'chunk_index']);
            $table->index('created_at');
        });

        // Add pgvector column in a separate transaction (if available)
        if ($hasPgvector) {
            try {
                DB::unprepared('ALTER TABLE document_chunks ADD COLUMN embedding vector(1536)');
                DB::unprepared('CREATE INDEX document_chunks_embedding_idx ON document_chunks USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
                echo "✅ Vector embedding column added successfully\n";
            } catch (\Exception $e) {
                echo "⚠️  Could not add vector column: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_chunks');
    }
};
