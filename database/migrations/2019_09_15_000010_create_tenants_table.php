<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name')->nullable()->after('id');
            $table->string('phone', 30)->nullable()->after('name');
            $table->string('email')->nullable()->after('phone');

            // Contract
            $table->enum('contract_type', ['yearly', 'monthly'])->nullable()->after('email');
            $table->unsignedSmallInteger('contract_duration_months')->nullable()->after('contract_type');
            $table->string('contract_file_url')->nullable()->after('contract_duration_months');

            $table->timestamps();
            $table->json('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
