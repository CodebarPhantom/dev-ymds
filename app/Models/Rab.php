<?php

namespace App\Models;

use App\Enums\RabStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rab extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nomor_rab',
        'divisi_id',
        'bulan_pengajuan',
        'status',
        'total_kegiatan',
        'total_biaya_anggaran',
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
            'status'          => RabStatus::class,
            'bulan_pengajuan' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relations
    // -------------------------------------------------------------------------

    public function items()
    {
        return $this->hasMany(RabItem::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(RabApprovalLog::class)->latest();
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
            RabStatus::DRAFT               => 'Draft',
            RabStatus::PENDING_BENDAHARA   => 'Menunggu Bendahara',
            RabStatus::REJECTED_BENDAHARA  => 'Ditolak Bendahara',
            RabStatus::PENDING_KETUA       => 'Menunggu Ketua',
            RabStatus::REJECTED_KETUA      => 'Ditolak Ketua',
            RabStatus::APPROVED            => 'Disetujui',
            RabStatus::CANCELLED           => 'Dibatalkan',
            default                        => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            RabStatus::DRAFT               => 'secondary',
            RabStatus::PENDING_BENDAHARA   => 'warning',
            RabStatus::REJECTED_BENDAHARA  => 'danger',
            RabStatus::PENDING_KETUA       => 'info',
            RabStatus::REJECTED_KETUA      => 'danger',
            RabStatus::APPROVED            => 'success',
            RabStatus::CANCELLED           => 'dark',
            default                        => 'secondary',
        };
    }

    // -------------------------------------------------------------------------
    // Helper Methods
    // -------------------------------------------------------------------------

    /**
     * RAB dapat diedit jika statusnya DRAFT, REJECTED_BENDAHARA, atau REJECTED_KETUA.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [
            RabStatus::DRAFT,
            RabStatus::REJECTED_BENDAHARA,
            RabStatus::REJECTED_KETUA,
        ]);
    }

    /**
     * RAB dapat disubmit jika kondisinya sama dengan isEditable.
     */
    public function isSubmittable(): bool
    {
        return $this->isEditable();
    }

    /**
     * RAB dapat dibatalkan jika statusnya bukan APPROVED dan bukan CANCELLED.
     */
    public function isCancellable(): bool
    {
        return !in_array($this->status, [
            RabStatus::APPROVED,
            RabStatus::CANCELLED,
        ]);
    }
}
