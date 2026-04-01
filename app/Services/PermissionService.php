<?php

namespace App\Services;

use App\Models\Permission;
use App\Services\MasterService;
use Illuminate\Support\Str;

class PermissionService extends MasterService
{
    public function storePermission(array $data)
    {
        $permission = Permission::create([
            'permission_group_id'=> $data['permission_group_id'],
            'name'=> $data['name'],
            'slug'=> Str::slug($data['name']),
            'guard_name' => 'web'
        ]);
        return $permission;
    }

    public function showPermission(int $id)
    {
        return Permission::where('id',$id)->first();
    }

    public function updatePermission($id, array $data)
    {
        // $permission = Permission::where('id',$id)
        // ->update([
        //     'permission_group_id'=> $data['permission_group_id'],
        //     'name'=> $data['name'],
        //     'slug'=> Str::slug($data['name'])
        // ]);
        $permission = Permission::find($id);
        $permission->update([
            'permission_group_id'=> $data['permission_group_id'],
            'name'=> $data['name'],
            'slug'=> Str::slug($data['name'])
        ]);
        return $permission;
    }

    // public function deletePermission($id)
    // {
    //     return Permission::destroy($id);
    // }

}
