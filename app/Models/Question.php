<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['form_id','category_id','name','order'];

    public function category()
    {
        return $this->belongsTo(QuestionCategory::class,'category_id');
    }
}
