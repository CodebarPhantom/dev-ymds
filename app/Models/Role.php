<?php

namespace App\Models;

use App\Models\Workforce\EmployeeRoleRemunerationPackage;
use Spatie\Permission\Models\Role as SpatieRole;


class Role extends SpatieRole
{

    protected $fillable = ['name', 'guard_name', 'slug', 'is_active'];

    public function getIsActiveColorAttribute()
    {
        switch ($this->is_active) {
            case 1:
                return 'bg-success';
            case 0:
                return 'bg-danger';
            default:
                return 'bg-danger'; // Default color if status is unknown
        }
    }

    public function getIsActiveNameAttribute()
    {
        switch ($this->is_active) {
            case 1:
                return 'Active'; // Human-readable status name for active
            case 0:
                return 'Inactive'; // Human-readable status name for non-active
            default:
                return 'Unknown'; // Default for unknown status
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active',1);
    }

    public function remunerationPackages()
    {
        return $this->hasMany(EmployeeRoleRemunerationPackage::class,'role_id')->orderBy('value','desc');
    }
}
