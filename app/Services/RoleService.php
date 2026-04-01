<?php

namespace App\Services;

use App\Models\Role;
use App\Services\MasterService;

class RoleService extends MasterService
{
    public function storeRole(array $data)
    {
        //dd($data['permissions']);
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
            'is_active'=> $data['is_active']
        ]);

        if (isset($data['permissions']) && !empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }


        return;

    }

    public function showRole(int $id)
    {

        return Role::where('id',$id)->with(['permissions'])->first();
    }

    public function updateRole($id, array $data)
    {
        $role = Role::where('id',$id)->first();
        $role->syncPermissions($data['permissions'] ?? []);
        $role->update([
            'name' => $data['name'],
            'guard_name' => 'web',
            'is_active'=> $data['is_active']
        ]);


        return;

    }

    public function getAllRole()
    {
        return Role::active()->orderBy('name','asc')->get();

    }

    // public function deletePermission($id)
    // {
    //     return Permission::destroy($id);
    // }

}
