<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_contacts', function (Blueprint $table) {
            $table->timestamp('deactivated_at')->nullable()->after('spam_pardoned_at');
        });
    }

    public function down(): void
    {
        Schema::table('roster_contacts', function (Blueprint $table) {
            $table->dropColumn('deactivated_at');
        });
    }
};
