<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionCategory extends Model
{
    public function form()
    {
        return $this->belongsTo(Form::class,'form_id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class,'category_id')->orderBy('order');
    }
}
