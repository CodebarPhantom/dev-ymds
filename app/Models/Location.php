<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'address', 'phone', 'status', 'latitude','longitude','radius', 'location_category_id'];

    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 1:
                return 'bg-success';
            case 0:
                return 'bg-danger';
            default:
                return 'bg-danger'; // Default color if status is unknown
        }
    }

    public function getStatusNameAttribute()
    {
        switch ($this->status) {
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
        return $query->where('status',1);
    }

    public function locationCategory()
    {
        return $this->belongsTo(LocationCategory::class);
    }
}
