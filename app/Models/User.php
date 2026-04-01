<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Models\BaseNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Auth;

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

    public function latestNotificationAlls()
    {
        //$employeeView = Employee::where('id', $user->employee_id)->first();

        return $this->hasMany(BaseNotification::class)
            ->where(function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->orWhere('for_all_users', true)
                    ->orWhere('location_id', Auth::user()->location_id); // Ensure to join with employee relation
            })
            ->latest()
            ->take(10);
    }

    public function latestNotificationForMe()
    {
        return $this->hasMany(BaseNotification::class)
            ->where(function ($query) {
                $query->where('user_id', Auth::user()->id)
                    ->where('location_id', null)
                    ->orWhere('for_all_users', true);
            })
            ->latest()
            ->take(10);
    }

    public function latestNotificationDepartment()
    {
        return $this->hasMany(BaseNotification::class)
            ->where('location_id', Auth::user()->location_id) // Ensure to join with employee relation
            ->latest()
            ->take(10);
    }

    public function notificationAlls()
    {
        return $this->hasMany(BaseNotification::class)->where('user_id', Auth::user()->id);
    }
}
