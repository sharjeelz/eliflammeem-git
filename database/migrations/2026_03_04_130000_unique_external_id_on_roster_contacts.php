<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Partial unique index: (tenant_id, external_id) where external_id IS NOT NULL
        // This allows multiple NULLs (unmanaged contacts) while preventing duplicates within a school.
        DB::statement('
            CREATE UNIQUE INDEX roster_contacts_tenant_external_id_unique
            ON roster_contacts (tenant_id, external_id)
            WHERE external_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS roster_contacts_tenant_external_id_unique');
    }
};
