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
        Schema::table('chatbot_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('faqs_matched')->default(0)->after('chunks_found');
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_logs', function (Blueprint $table) {
            $table->dropColumn('faqs_matched');
        });
    }
};
