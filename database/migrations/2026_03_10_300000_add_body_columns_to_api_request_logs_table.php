<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->jsonb('request_body')->nullable()->after('endpoint');
            $table->jsonb('response_body')->nullable()->after('request_body');
        });
    }

    public function down(): void
    {
        Schema::table('api_request_logs', function (Blueprint $table) {
            $table->dropColumn(['request_body', 'response_body']);
        });
    }
};
