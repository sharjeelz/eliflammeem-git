<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 50)->nullable();
            $table->string('school_name', 150)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('package', 50)->nullable(); // starter|growth|pro|enterprise|custom
            $table->text('message')->nullable();
            $table->string('status', 30)->default('new'); // new|contacted|approved|rejected
            $table->text('notes')->nullable(); // internal Nova notes
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
