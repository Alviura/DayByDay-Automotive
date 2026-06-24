<?php

namespace Database\Seeders;

use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Permissions and core role defaults are defined in config/permissions.php.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $registered = PermissionRegistry::allNames();

        foreach ($registered as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $registered)
            ->each(function (Permission $permission): void {
                $permission->roles()->detach();
                $permission->delete();
            });

        $admin = Role::firstOrCreate(['name' => 'Administrator', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        foreach (config('permissions.role_defaults', []) as $roleName => $defaults) {
            if ($roleName === 'Administrator') {
                continue;
            }

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $names = PermissionRegistry::defaultsForRole($roleName);
            $role->syncPermissions(
                Permission::whereIn('name', $names)->where('guard_name', 'web')->get()
            );
        }
    }
}
