<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->renameColumn('plan_started_at', 'subscription_starts_at');
            $table->date('subscription_ends_at')->nullable()->after('plan_started_at');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['contract_started_at', 'contract_duration_months']);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->renameColumn('subscription_starts_at', 'plan_started_at');
            $table->dropColumn('subscription_ends_at');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->date('contract_started_at')->nullable();
            $table->unsignedSmallInteger('contract_duration_months')->nullable();
        });
    }
};
