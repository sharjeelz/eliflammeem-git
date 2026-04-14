<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roster_contacts', function (Blueprint $table) {
            $table->string('expo_push_token', 255)->nullable()->after('meta');
            $table->string('device_platform', 10)->nullable()->after('expo_push_token');
        });
    }

    public function down(): void
    {
        Schema::table('roster_contacts', function (Blueprint $table) {
            $table->dropColumn(['expo_push_token', 'device_platform']);
        });
    }
};
