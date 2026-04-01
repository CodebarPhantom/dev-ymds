<?php
namespace Database\Seeders\Permission;

use App\Models\PermissionGroup;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Helper function to create permission group and permissions if they don't exist
        $this->createPermissionGroupWithPermissions('Lokasi', [
            'location-read',
            'location-create',
            'location-update',
            'location-delete'
        ]);


        $this->createPermissionGroupWithPermissions('Roles & Permission - Roles', [
            'role-read',
            'role-create',
            'role-update',
            'role-delete'
        ]);

        $this->createPermissionGroupWithPermissions('Roles & Permission - Grup Hak Akses', [
            'permission-group-read',
            'permission-group-create',
            'permission-group-update',
            'permission-group-delete'
        ]);

        $this->createPermissionGroupWithPermissions('Roles & Permission - Hak Akses', [
            'permission-read',
            'permission-create',
            'permission-update',
            'permission-delete'
        ]);

        $this->createPermissionGroupWithPermissions('User', [
            'user-read',
            'user-create',
            'user-update',
            'user-delete'
        ]);

    }

    /**
     * Create a permission group with associated permissions.
     * If the permission group or permissions already exist, they will not be created again.
     */
    private function createPermissionGroupWithPermissions($groupName, array $permissions)
    {
        // Use firstOrCreate to check if the group already exists
        $permissionGroup = PermissionGroup::firstOrCreate(['name' => $groupName,'slug'=>Str::slug($groupName)]);

        // Loop through permissions and use firstOrCreate to prevent duplicates
        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
                'permission_group_id' => $permissionGroup->id
            ]);
        }
    }
}

