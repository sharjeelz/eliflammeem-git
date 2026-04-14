<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issue_messages', function (Blueprint $table) {
            $table->boolean('is_internal')->default(true)->after('message');
        });

        // Contact-authored messages are never internal
        DB::table('issue_messages')
            ->where('author_type', 'App\Models\RosterContact')
            ->update(['is_internal' => false]);
    }

    public function down(): void
    {
        Schema::table('issue_messages', function (Blueprint $table) {
            $table->dropColumn('is_internal');
        });
    }
};
