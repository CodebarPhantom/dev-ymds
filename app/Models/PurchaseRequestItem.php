<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequestItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'purchase_request_id',
        'nama_barang',
        'satuan',
        'jumlah',
        'biaya_estimasi',
        'catatan_item',
        'sudah_dibeli',
        'harga_aktual',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'sudah_dibeli' => 'boolean',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }
}
