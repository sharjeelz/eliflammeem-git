<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantRolesSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = tenant()->id;

        // Forget cached permissions before creating
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Define permissions
        $permissions = [
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

        // Create permissions for this tenant
        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
                'tenant_id' => $tenantId,
            ]);
        }

        // Define roles
        $roles = [
            'admin' => $permissions, // admin gets all
            'branch_manager' => [
                'issues.view.branch',
                'issues.assign',
                'issues.update.status',
                'issues.comment',
                'reports.view',
            ],
            'staff' => [
                'issues.view.assigned',
                'issues.update.status',
                'issues.comment',
            ],
        ];

        foreach ($roles as $roleName => $rolePerms) {
            // Create role
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'tenant_id' => $tenantId,
            ]);

            // Attach permissions with tenant_id in pivot
            $permissionIds = Permission::where('tenant_id', $tenantId)
                ->whereIn('name', $rolePerms)
                ->pluck('id')
                ->toArray();

            $role->permissions()->syncWithPivotValues($permissionIds, ['tenant_id' => $tenantId]);
        }

        // Forget cache again
        app(PermissionRegistrar::class)->forgetCachedPermissions();

    }
}
