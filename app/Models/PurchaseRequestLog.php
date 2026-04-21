<?php

namespace App\Models;

use App\Enums\PurchaseRequestAksiLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_request_id',
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
            'aksi' => PurchaseRequestAksiLog::class,
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function dilakukanOleh()
    {
        return $this->belongsTo(User::class, 'dilakukan_oleh');
    }
}
