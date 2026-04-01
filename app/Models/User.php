<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location_id',
        'url_image',
        'role_id',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

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


    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function role(){
        return $this->belongsTo(Role::class, 'role_id');

    }

    public function scopeActive($query)
    {
        return $query->where('is_active',true);
    }
}
