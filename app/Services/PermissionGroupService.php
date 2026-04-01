<?php

namespace App\Services;

use App\Models\PermissionGroup;
use App\Services\MasterService;
use Illuminate\Support\Str;

class PermissionGroupService extends MasterService
{
    public function storePermissionGroup(array $data)
    {
        $permissionGroup = PermissionGroup::create([
            'name'=> $data['name'],
            'slug'=> Str::slug($data['name']),
            'is_active' => $data['is_active']
        ]);
        return $permissionGroup;
    }

    public function showPermissionGroup(int $id)
    {
        return PermissionGroup::where('id',$id)->first();
    }

    public function updatePermissionGroup($id, array $data)
    {
        $permissionGroup = PermissionGroup::where('id',$id)
        ->update([
            'name'=> $data['name'],
            'slug'=> Str::slug($data['name']),
            'is_active' => $data['is_active']
        ]);
        $permissionGroup->update($data);
        return $permissionGroup;
    }

    public function deletePermissionGroup($id)
    {
        $permissionGroup = PermissionGroup::findOrFail($id);
        if ($permissionGroup->delete()) {
        }
        return $permissionGroup;
    }

    public function getAllPermissionGroups()
    {
        return PermissionGroup::active()->orderBy('name','asc')->get();
    }

    public function getAllPermissionGroupWithPermissions()
    {
        return PermissionGroup::with(['permissions'])->active()->orderBy('name','asc')->get();
    }

}
