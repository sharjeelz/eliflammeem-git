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
        Schema::create('issue_categories', function (Blueprint $table) {
            $table->id();
            $table->uuid('tenant_id')->index();
            $table->string('name');
            $table->string('slug');
            $table->unsignedSmallInteger('default_sla_hours')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'slug']); // unique per tenant
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key from issues table first to avoid dependency
        if (Schema::hasTable('issues')) {
            Schema::table('issues', function (Blueprint $table) {
                if (Schema::hasColumn('issues', 'issue_category_id')) {
                    $table->dropForeign(['issue_category_id']);
                }
            });
        }

        Schema::dropIfExists('issue_categories');
    }
};
