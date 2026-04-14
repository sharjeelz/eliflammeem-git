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
        Schema::table('issue_messages', function (Blueprint $t) {
            // Polymorphic author (User | RosterContact | Bot | etc.)
            $t->string('author_type')->nullable()->after('issue_id');
            $t->unsignedBigInteger('author_id')->nullable()->after('author_type');

            // Helpful index for author lookups/analytics
            $t->index(['author_type', 'author_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issue_messages', function (Blueprint $t) {
            $t->dropIndex(['author_type', 'author_id']);
            $t->dropColumn(['author_type', 'author_id']);
        });
    }
};
