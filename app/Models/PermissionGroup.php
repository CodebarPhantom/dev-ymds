<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug' ,'is_active'];

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

    public function permissions()
    {
        return $this->hasMany(Permission::class,'permission_group_id')->orderBy('name', 'asc');
    }
}
