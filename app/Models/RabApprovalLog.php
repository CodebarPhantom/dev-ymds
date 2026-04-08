<?php

namespace App\Models;

use App\Enums\RabAksiLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RabApprovalLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'rab_id',
        'aksi',
        'dilakukan_oleh',
        'catatan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'aksi' => RabAksiLog::class,
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function rab()
    {
        return $this->belongsTo(Rab::class);
    }

    public function dilakukanOleh()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
