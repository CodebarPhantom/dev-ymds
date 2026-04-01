<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Form extends Model
{
    protected $fillable = ['form_code','name'];

    public function categories()
    {
        return $this->hasMany(QuestionCategory::class, 'form_id')
                    ->orderBy('order');
    }

    public function lastEntryHeader()
    {
        return $this->hasOne(EntryHeader::class, 'form_id')->where('user_id',Auth::id())->orderBy('created_at','desc');
    }
}
