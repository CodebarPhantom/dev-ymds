<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class BaseNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'location_id', 'for_all_users', 'title', 'description', 'is_read'
    ];

    protected $dates = ['created_at', 'updated_at'];

    // public function scopeByUserId($query)
    // {
    //     return $query->where('user_id',Auth::user()->id);
    // }


    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

}
