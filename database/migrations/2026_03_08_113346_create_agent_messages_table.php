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
        // Add tenant_id to agent_conversations table
        Schema::table('agent_conversations', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // Add tenant_id to agent_conversation_messages table
        Schema::table('agent_conversation_messages', function (Blueprint $table) {
            $table->uuid('tenant_id')->nullable()->after('id');
            $table->index('tenant_id');
        });

        // Add document_chunk references to agent_conversation_messages
        Schema::table('agent_conversation_messages', function (Blueprint $table) {
            $table->json('source_chunk_ids')->nullable()->after('meta');
            $table->decimal('confidence_score', 3, 2)->nullable()->after('source_chunk_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_conversations', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        Schema::table('agent_conversation_messages', function (Blueprint $table) {
            $table->dropIndex(['tenant_id']);
            $table->dropColumn(['tenant_id', 'source_chunk_ids', 'confidence_score']);
        });
    }
};
