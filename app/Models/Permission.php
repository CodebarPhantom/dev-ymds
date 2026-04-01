<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;


class Permission extends SpatiePermission
{

    protected $fillable = ['name', 'slug', 'guard_name', 'permission_group_id'];

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class,'permission_group_id');
    }
}
