<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentConfirmation extends Model
{
    protected $fillable = [
        'confirmation_code',
        'location_id',
        'location_category_id',
        'payer_name',
        'month',
        'year',
        'amount',
        'proof_file',
        'notes',
        'status'
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function locationCategory(): BelongsTo
    {
        return $this->belongsTo(LocationCategory::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'butuh_pengecekan' => 'Butuh Pengecekan',
            'sudah_dicek' => 'Sudah Dicek',
            default => $this->status
        };
    }
}
