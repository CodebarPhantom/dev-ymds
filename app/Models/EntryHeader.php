<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EntryHeader extends Model
{
    protected $fillable = ['entry_code', 'form_id', 'user_id', 'surah_id', 'approver_id', 'page', 'verse_start', 'verse_end', 'entry_date', 'notes', 'metadata'];
    protected $casts = [
        'metadata' => 'array',
    ];
    protected $appends = [
        'formatted_entry_date'
    ];

    public function surah()
    {
        return $this->belongsTo(Surah::class, 'surah_id');
    }

    public function form()
    {
        return $this->belongsTo(Form::class, 'form_id');
    }

    public function details()
    {
        return $this->hasMany(EntryDetail::class, 'entry_header_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFormattedEntryDateAttribute(): string
    {
        return Carbon::parse($this->entry_date)->format('d M y');
    }

    public function getTotalErrorsAttribute()
    {
        return $this->details->sum(function ($detail) {
            return (int) $detail->string_value;
        });
    }
}
