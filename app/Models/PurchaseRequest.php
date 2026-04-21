<?php

namespace App\Models;

use App\Enums\PurchaseRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nomor_pengajuan',
        'divisi_id',
        'judul_pengajuan',
        'keperluan',
        'tanggal_dibutuhkan',
        'status',
        'total_item',
        'total_biaya_estimasi',
        'total_biaya_aktual',
        'dibuat_oleh',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'status'            => PurchaseRequestStatus::class,
            'tanggal_dibutuhkan' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class, 'purchase_request_id');
    }

    public function logs()
    {
        return $this->hasMany(PurchaseRequestLog::class, 'purchase_request_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            PurchaseRequestStatus::DRAFT               => 'Draft',
            PurchaseRequestStatus::PENDING_BENDAHARA   => 'Menunggu Bendahara',
            PurchaseRequestStatus::REJECTED_BENDAHARA  => 'Ditolak Bendahara',
            PurchaseRequestStatus::PENDING_KETUA       => 'Menunggu Ketua',
            PurchaseRequestStatus::REJECTED_KETUA      => 'Ditolak Ketua',
            PurchaseRequestStatus::APPROVED            => 'Disetujui',
            PurchaseRequestStatus::CANCELLED           => 'Dibatalkan',
            PurchaseRequestStatus::PURCHASING          => 'Dalam Pembelian',
            PurchaseRequestStatus::PARTIALLY_PURCHASED => 'Sebagian Dibeli',
            PurchaseRequestStatus::COMPLETED           => 'Selesai',
            default                                    => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            PurchaseRequestStatus::DRAFT               => 'secondary',
            PurchaseRequestStatus::PENDING_BENDAHARA   => 'warning',
            PurchaseRequestStatus::REJECTED_BENDAHARA  => 'danger',
            PurchaseRequestStatus::PENDING_KETUA       => 'warning',
            PurchaseRequestStatus::REJECTED_KETUA      => 'danger',
            PurchaseRequestStatus::APPROVED            => 'success',
            PurchaseRequestStatus::CANCELLED           => 'dark',
            PurchaseRequestStatus::PURCHASING          => 'info',
            PurchaseRequestStatus::PARTIALLY_PURCHASED => 'primary',
            PurchaseRequestStatus::COMPLETED           => 'success',
            default                                    => 'secondary',
        };
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    /**
     * PR dapat diedit jika statusnya DRAFT, REJECTED_BENDAHARA, atau REJECTED_KETUA.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            PurchaseRequestStatus::DRAFT,
            PurchaseRequestStatus::REJECTED_BENDAHARA,
            PurchaseRequestStatus::REJECTED_KETUA,
        ]);
    }

    /**
     * PR dapat disubmit jika kondisinya sama dengan isEditable.
     */
    public function isSubmittable(): bool
    {
        return $this->isEditable();
    }

    /**
     * PR dapat dibatalkan jika statusnya bukan APPROVED, CANCELLED, atau COMPLETED.
     */
    public function isCancellable(): bool
    {
        return !in_array($this->status, [
            PurchaseRequestStatus::APPROVED,
            PurchaseRequestStatus::CANCELLED,
            PurchaseRequestStatus::COMPLETED,
        ]);
    }

    /**
     * PR dapat dimulai proses pembelian jika statusnya APPROVED.
     */
    public function isPurchasable(): bool
    {
        return $this->status === PurchaseRequestStatus::APPROVED;
    }

    /**
     * PR sedang dalam proses pembelian jika statusnya PURCHASING atau PARTIALLY_PURCHASED.
     */
    public function isInPurchasing(): bool
    {
        return in_array($this->status, [
            PurchaseRequestStatus::PURCHASING,
            PurchaseRequestStatus::PARTIALLY_PURCHASED,
        ]);
    }
}
