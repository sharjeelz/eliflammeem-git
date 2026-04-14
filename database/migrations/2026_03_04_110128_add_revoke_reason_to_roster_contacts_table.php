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
        Schema::table('roster_contacts', function (Blueprint $table) {
            $table->text('revoke_reason')->nullable()->after('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('roster_contacts', function (Blueprint $table) {
            $table->dropColumn('revoke_reason');
        });
    }
};
