<?php

namespace Database\Seeders\Tenant;

use App\Models\IssueCategory;
use Illuminate\Database\Seeder;

class TenantIssueCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = tenant('id'); // must be called inside tenant context

        $defaults = [
            ['name' => 'Transport', 'slug' => 'transport', 'default_sla_hours' => 24],
            ['name' => 'Academics', 'slug' => 'academics', 'default_sla_hours' => 48],
            ['name' => 'Facilities', 'slug' => 'facilities', 'default_sla_hours' => 72],
            ['name' => 'Behavior', 'slug' => 'behavior', 'default_sla_hours' => 48],
            ['name' => 'Food & Dining', 'slug' => 'food-dining', 'default_sla_hours' => 72],
            ['name' => 'Communication', 'slug' => 'communication', 'default_sla_hours' => 48],
            ['name' => 'Health & Safety', 'slug' => 'health-safety', 'default_sla_hours' => 72],
            ['name' => 'Fees & Payments', 'slug' => 'fees-payments', 'default_sla_hours' => 48],
            ['name' => 'Technology Issues', 'slug' => 'technology-issues', 'default_sla_hours' => 24],
            ['name' => 'General Complaints', 'slug' => 'general-complaints', 'default_sla_hours' => 24],
        ];

        foreach ($defaults as $cat) {
            IssueCategory::firstOrCreate(
                ['tenant_id' => $tenantId, 'slug' => $cat['slug']],
                $cat
            );
        }
    }
}
