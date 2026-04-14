<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_api_keys', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tenant_id', 36)->index(); // UUID — no FK constraint, tenants table may be separate
            $table->string('name', 100);
            $table->string('key_prefix', 16);            // first 16 chars of plaintext, for display
            $table->string('key_hash', 64)->unique();    // sha256(plaintext) — never store plaintext
            $table->unsignedBigInteger('created_by')->nullable(); // user id
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'revoked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_api_keys');
    }
};
