<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EntryDetail extends Model
{
    protected $fillable = ['entry_header_id','entry_code','question_id','string_value'];

    public function entryHeader()
    {
        return $this->belongsTo(EntryHeader::class, 'entry_header_id');
    }
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
