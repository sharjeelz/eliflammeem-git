<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // Price in USD cents (e.g. 4900 = $49.00). null = free / custom pricing.
            $table->unsignedInteger('price_monthly_cents')->nullable()->after('label');
            // Optional short tagline shown on pricing page
            $table->string('tagline', 120)->nullable()->after('price_monthly_cents');
        });

        DB::table('plans')->where('key', 'starter')->update([
            'price_monthly_cents' => 0,
            'tagline'             => 'Perfect for getting started.',
        ]);
        DB::table('plans')->where('key', 'growth')->update([
            'price_monthly_cents' => null, // contact us
            'tagline'             => 'For schools ready to grow.',
        ]);
        DB::table('plans')->where('key', 'pro')->update([
            'price_monthly_cents' => null, // contact us
            'tagline'             => 'For schools with multiple branches.',
        ]);
        DB::table('plans')->where('key', 'enterprise')->update([
            'price_monthly_cents' => null,
            'tagline'             => 'For large districts and multi-school groups.',
        ]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['price_monthly_cents', 'tagline']);
        });
    }
};
