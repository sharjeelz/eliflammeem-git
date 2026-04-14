<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TenantRolesSeeder::class,
            // AdminUserSeeder::class,
        ]);
    }
}
