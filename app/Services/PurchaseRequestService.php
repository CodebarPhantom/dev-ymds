<?php

namespace App\Services;

use App\Enums\PurchaseRequestAksiLog;
use App\Enums\PurchaseRequestStatus;
use App\Exceptions\CustomException;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseRequestLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequestService extends MasterService
{
    /**
     * Generate nomor pengajuan unik berformat PB/YYYYMM/NNN.
     * Harus dipanggil di dalam transaksi yang sudah ada (store).
     */
    public function generateNomorPengajuan(): string
    {
        $yyyymm = now()->format('Ym');
        $prefix = "PB/{$yyyymm}/";

        $lastRow = DB::selectOne(
            "SELECT nomor_pengajuan FROM purchase_requests WHERE nomor_pengajuan LIKE ? ORDER BY nomor_pengajuan DESC LIMIT 1 FOR UPDATE",
            ["{$prefix}%"]
        );

        $sequence = 1;
        if ($lastRow && $lastRow->nomor_pengajuan) {
            $parts = explode('/', $lastRow->nomor_pengajuan);
            $sequence = (int) end($parts) + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Buat PurchaseRequest baru beserta items-nya.
     */
    public function store(array $data, User $user): PurchaseRequest
    {
        return DB::transaction(function () use ($data, $user) {
            $nomorPengajuan = $this->generateNomorPengajuan();

            $pr = PurchaseRequest::create([
                'nomor_pengajuan'   => $nomorPengajuan,
                'divisi_id'         => $user->getRoleNames()->first(),
                'judul_pengajuan'   => $data['judul_pengajuan'],
                'keperluan'         => $data['keperluan'] ?? null,
                'tanggal_dibutuhkan' => $data['tanggal_dibutuhkan'],
                'status'            => PurchaseRequestStatus::DRAFT,
                'dibuat_oleh'       => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $pr->items()->create([
                    'nama_barang'   => $item['nama_barang'],
                    'satuan'        => $item['satuan'],
                    'jumlah'        => $item['jumlah'],
                    'biaya_estimasi' => $item['biaya_estimasi'],
                    'catatan_item'  => $item['catatan_item'] ?? null,
                ]);
            }

            $this->updateComputedFields($pr);

            return $pr;
        });
    }

    /**
     * Update PurchaseRequest header dan sync items.
     */
    public function update(PurchaseRequest $pr, array $data): PurchaseRequest
    {
        return DB::transaction(function () use ($pr, $data) {
            $pr->update([
                'judul_pengajuan'    => $data['judul_pengajuan'],
                'keperluan'          => $data['keperluan'] ?? null,
                'tanggal_dibutuhkan' => $data['tanggal_dibutuhkan'],
            ]);

            $pr->items()->delete();

            foreach ($data['items'] as $item) {
                $pr->items()->create([
                    'nama_barang'   => $item['nama_barang'],
                    'satuan'        => $item['satuan'],
                    'jumlah'        => $item['jumlah'],
                    'biaya_estimasi' => $item['biaya_estimasi'],
                    'catatan_item'  => $item['catatan_item'] ?? null,
                ]);
            }

            $this->updateComputedFields($pr);

            return $pr->refresh();
        });
    }

    /**
     * Hitung ulang total_item dan total_biaya_estimasi.
     */
    public function updateComputedFields(PurchaseRequest $pr): void
    {
        $count = $pr->items()->count();
        $sum   = $pr->items()->selectRaw('SUM(biaya_estimasi * jumlah) as total')->value('total') ?? 0;

        $pr->update([
            'total_item'           => $count,
            'total_biaya_estimasi' => $sum,
        ]);
    }

    /**
     * Ajukan PurchaseRequest ke Bendahara Umum.
     */
    public function submit(PurchaseRequest $pr, User $user): PurchaseRequest
    {
        if (!$pr->isSubmittable()) {
            throw new CustomException('Pengajuan tidak dapat diajukan karena statusnya tidak valid.', 422);
        }

        $pr->update(['status' => PurchaseRequestStatus::PENDING_BENDAHARA]);

        $this->logAction($pr, PurchaseRequestAksiLog::SUBMITTED, $user->id);

        return $pr->refresh();
    }

    /**
     * Batalkan PurchaseRequest.
     */
    public function cancel(PurchaseRequest $pr, User $user): PurchaseRequest
    {
        if (!$pr->isCancellable()) {
            throw new CustomException('Pengajuan tidak dapat dibatalkan karena statusnya tidak valid.', 422);
        }

        $pr->update(['status' => PurchaseRequestStatus::CANCELLED]);

        $this->logAction($pr, PurchaseRequestAksiLog::CANCELLED, $user->id);

        return $pr->refresh();
    }

    /**
     * Setujui PurchaseRequest (deteksi role otomatis).
     */
    public function approve(PurchaseRequest $pr, User $user, ?string $catatan): PurchaseRequest
    {
        if ($user->hasRole('bendahara_umum')) {
            if ($pr->status !== PurchaseRequestStatus::PENDING_BENDAHARA) {
                throw new CustomException('Pengajuan tidak dapat disetujui karena statusnya tidak valid.', 422);
            }

            $pr->update(['status' => PurchaseRequestStatus::PENDING_KETUA]);

            $this->logAction($pr, PurchaseRequestAksiLog::APPROVED_BENDAHARA, $user->id, $catatan);
        } elseif ($user->hasRole('ketua_yayasan')) {
            if ($pr->status !== PurchaseRequestStatus::PENDING_KETUA) {
                throw new CustomException('Pengajuan tidak dapat disetujui karena statusnya tidak valid.', 422);
            }

            $pr->update(['status' => PurchaseRequestStatus::APPROVED]);

            $this->logAction($pr, PurchaseRequestAksiLog::APPROVED_KETUA, $user->id, $catatan);
        } else {
            throw new CustomException('Anda tidak berwenang untuk menyetujui pengajuan ini.', 403);
        }

        return $pr->refresh();
    }

    /**
     * Tolak PurchaseRequest (deteksi role otomatis).
     */
    public function reject(PurchaseRequest $pr, User $user, string $catatan): PurchaseRequest
    {
        if (empty(trim($catatan))) {
            throw ValidationException::withMessages([
                'catatan' => ['Catatan penolakan wajib diisi.'],
            ]);
        }

        if ($user->hasRole('bendahara_umum')) {
            if ($pr->status !== PurchaseRequestStatus::PENDING_BENDAHARA) {
                throw new CustomException('Pengajuan tidak dapat ditolak karena statusnya tidak valid.', 422);
            }

            $pr->update(['status' => PurchaseRequestStatus::REJECTED_BENDAHARA]);

            $this->logAction($pr, PurchaseRequestAksiLog::REJECTED_BENDAHARA, $user->id, $catatan);
        } elseif ($user->hasRole('ketua_yayasan')) {
            if ($pr->status !== PurchaseRequestStatus::PENDING_KETUA) {
                throw new CustomException('Pengajuan tidak dapat ditolak karena statusnya tidak valid.', 422);
            }

            $pr->update(['status' => PurchaseRequestStatus::REJECTED_KETUA]);

            $this->logAction($pr, PurchaseRequestAksiLog::REJECTED_KETUA, $user->id, $catatan);
        } else {
            throw new CustomException('Anda tidak berwenang untuk menolak pengajuan ini.', 403);
        }

        return $pr->refresh();
    }

    /**
     * Mulai proses pembelian (APPROVED → PURCHASING).
     */
    public function startPurchasing(PurchaseRequest $pr, User $user): PurchaseRequest
    {
        if ($pr->status !== PurchaseRequestStatus::APPROVED) {
            throw new CustomException('Pengajuan tidak dapat dimulai proses pembeliannya karena statusnya tidak valid.', 422);
        }

        $pr->update(['status' => PurchaseRequestStatus::PURCHASING]);

        $this->logAction($pr, PurchaseRequestAksiLog::PURCHASING_STARTED, $user->id);

        return $pr->refresh();
    }

    /**
     * Tandai item sudah dibeli dan update status PR.
     */
    public function markItemPurchased(PurchaseRequest $pr, PurchaseRequestItem $item, array $data, User $user): PurchaseRequest
    {
        if ($data['harga_aktual'] < 0) {
            throw ValidationException::withMessages([
                'harga_aktual' => ['Harga aktual tidak boleh negatif.'],
            ]);
        }

        if (!$pr->isInPurchasing()) {
            throw new CustomException('Pengajuan tidak dalam status pembelian yang valid.', 422);
        }

        $item->update([
            'sudah_dibeli' => true,
            'harga_aktual' => $data['harga_aktual'],
        ]);

        $this->updateActualFields($pr);

        $pr->refresh();

        $allPurchased = $pr->items()->where('sudah_dibeli', false)->doesntExist();

        if ($allPurchased) {
            $pr->update(['status' => PurchaseRequestStatus::COMPLETED]);
            $this->logAction($pr, PurchaseRequestAksiLog::COMPLETED, $user->id);
        } else {
            $pr->update(['status' => PurchaseRequestStatus::PARTIALLY_PURCHASED]);
        }

        $this->logAction($pr, PurchaseRequestAksiLog::ITEM_PURCHASED, $user->id, $item->nama_barang);

        return $pr->refresh();
    }

    /**
     * Hitung ulang total_biaya_aktual dari item yang sudah dibeli.
     */
    public function updateActualFields(PurchaseRequest $pr): void
    {
        $total = $pr->items()
            ->where('sudah_dibeli', true)
            ->sum('harga_aktual');

        $pr->update(['total_biaya_aktual' => $total ?? 0]);
    }

    /**
     * Query builder untuk datatable dengan filter berbasis role.
     */
    public function getDatatableQuery(array $filters, User $user): Builder
    {
        $query = PurchaseRequest::query();

        $privilegedRoles = ['bendahara_umum', 'ketua_yayasan', 'divisi_sarpras'];
        $roleName = $user->getRoleNames()->first();
        $isPrivileged = in_array($roleName, $privilegedRoles);

        if (!$isPrivileged) {
            // divisi_* user hanya lihat data divisi sendiri
            $query->where('divisi_id', $roleName);
        } else {
            // privileged user: filter divisi_id opsional
            if (!empty($filters['divisi_id'])) {
                $query->where('divisi_id', $filters['divisi_id']);
            }
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['tanggal_dibutuhkan'])) {
            $query->whereDate('tanggal_dibutuhkan', $filters['tanggal_dibutuhkan']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn (Builder $q) => $q
                ->whereRaw('nomor_pengajuan ILIKE ?', ["%{$search}%"])
                ->orWhereRaw('judul_pengajuan ILIKE ?', ["%{$search}%"])
            );
        }

        $allowedSortFields = ['created_at', 'nomor_pengajuan', 'divisi_id', 'judul_pengajuan', 'tanggal_dibutuhkan', 'status', 'total_item', 'total_biaya_estimasi', 'total_biaya_aktual'];
        $sortField = in_array($filters['sortField'] ?? null, $allowedSortFields) ? $filters['sortField'] : 'created_at';
        $sortOrder = in_array($filters['sortOrder'] ?? null, ['asc', 'desc']) ? $filters['sortOrder'] : 'desc';

        $query->orderBy($sortField, $sortOrder);

        return $query;
    }

    /**
     * Catat aksi ke purchase_request_logs.
     */
    private function logAction(PurchaseRequest $pr, PurchaseRequestAksiLog $aksi, int $userId, ?string $catatan = null): void
    {
        PurchaseRequestLog::create([
            'purchase_request_id' => $pr->id,
            'aksi'                => $aksi,
            'dilakukan_oleh'      => $userId,
            'catatan'             => $catatan,
        ]);
    }
}
