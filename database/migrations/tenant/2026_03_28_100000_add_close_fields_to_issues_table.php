<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            // Admin's resolution note — how the issue was actually resolved
            $table->text('close_note')->nullable()->after('resolved_at');
            // Contact's reason for self-closing — fixed enum
            $table->string('close_reason', 50)->nullable()->after('close_note');
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn(['close_note', 'close_reason']);
        });
    }
};
