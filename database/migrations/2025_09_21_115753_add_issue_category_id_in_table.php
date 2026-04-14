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
        Schema::table('issues', function (Blueprint $table) {
            if (! Schema::hasColumn('issues', 'issue_category_id')) {
                $table->unsignedBigInteger('issue_category_id')->nullable()->index()->after('tenant_id');
                $table->foreign('issue_category_id')->references('id')->on('issue_categories')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            //
        });
    }
};
