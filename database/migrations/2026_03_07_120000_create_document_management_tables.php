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
        // Document categories (hierarchical structure)
        Schema::create('document_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->foreignId('parent_id')->nullable()->constrained('document_categories')->onDelete('cascade');
            $table->string('name', 150);
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable(); // ki-duotone icon class
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'parent_id']);
        });

        // Documents (files: PDFs, DOCX, TXT)
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->onDelete('set null');
            $table->string('title', 200);
            $table->string('slug');
            $table->text('description')->nullable();

            // File storage (matches IssueAttachment pattern)
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('mime');
            $table->bigInteger('size')->unsigned();

            // Document metadata
            $table->enum('type', ['policy', 'schedule', 'event', 'news', 'handbook', 'form', 'other']);
            $table->boolean('is_public')->default(false);

            // For Phase 2/3: text extraction and vector search
            $table->text('searchable_content')->nullable();
            $table->json('meta')->nullable();

            $table->integer('display_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'category_id']);
            $table->index(['tenant_id', 'type', 'is_public']);
            $table->unique(['tenant_id', 'slug']);
        });

        // FAQs (knowledge base questions & answers)
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->foreignId('category_id')->nullable()->constrained('document_categories')->onDelete('set null');
            $table->text('question');
            $table->text('answer');

            // Analytics for Phase 4 (chatbot feedback)
            $table->integer('view_count')->default(0);
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);

            $table->integer('display_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->json('related_document_ids')->nullable();

            $table->timestamps();
            $table->index(['tenant_id', 'category_id', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('document_categories');
    }
};
