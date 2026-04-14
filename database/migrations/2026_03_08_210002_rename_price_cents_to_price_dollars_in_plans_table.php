<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Rename from cents to dollars — value entered directly as whole dollars (e.g. 49 = $49/month)
            $table->renameColumn('price_monthly_cents', 'price_monthly');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->renameColumn('price_monthly', 'price_monthly_cents');
        });
    }
};
