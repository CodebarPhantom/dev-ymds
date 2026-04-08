<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rab_id',
        'kegiatan',
        'catatan_kegiatan',
        'biaya_anggaran',
        'waktu_pelaksanaan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'biaya_anggaran' => 'decimal:2',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }
}
