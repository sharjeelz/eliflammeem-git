<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = tenant('id');

        // Tell Spatie that we are in a team/tenant context
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenantId);

        $perms = [
            'issues.view.any',
            'issues.view.branch',
            'issues.view.assigned',
            'issues.assign',
            'issues.update.status',
            'issues.comment',
            'branches.manage',
            'staff.manage',
            'reports.view',
            'analytics.view',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate([
                'name' => $p,
                'guard_name' => 'web',
                'tenant_id' => $tenantId, // explicitly set team id
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
            'tenant_id' => $tenantId,
        ]);

        $bm = Role::firstOrCreate([
            'name' => 'branch_manager',
            'guard_name' => 'web',
            'tenant_id' => $tenantId,
        ]);

        $staff = Role::firstOrCreate([
            'name' => 'staff',
            'guard_name' => 'web',
            'tenant_id' => $tenantId,
        ]);

        // Now assign permissions — Spatie will include tenant_id automatically
        $admin->givePermissionTo($perms);
        $bm->syncPermissions([
            'issues.view.branch',
            'issues.assign',
            'issues.update.status',
            'issues.comment',
            'reports.view',
        ]);
        $staff->syncPermissions(['issues.view.assigned', 'issues.update.status', 'issues.comment']);
    }
}
