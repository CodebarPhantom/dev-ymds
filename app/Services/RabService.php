<?php

namespace App\Services;

use App\Enums\RabAksiLog;
use App\Enums\RabStatus;
use App\Models\Rab;
use App\Models\RabApprovalLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RabService extends MasterService
{
    /**
     * Generate nomor RAB unik berformat RAB/YYYYMM/NNN.
     * Harus dipanggil di dalam transaksi yang sudah ada (storeRab).
     */
    public function generateNomorRab(string $bulanPengajuan): string
    {
        $bulan = Carbon::parse($bulanPengajuan);
        $yyyymm = $bulan->format('Ym');
        $prefix = "RAB/{$yyyymm}/";

        $lastRow = DB::selectOne(
            "SELECT nomor_rab FROM rabs WHERE nomor_rab LIKE ? ORDER BY nomor_rab DESC LIMIT 1 FOR UPDATE",
            ["{$prefix}%"]
        );

        $sequence = 1;
        if ($lastRow && $lastRow->nomor_rab) {
            $parts = explode('/', $lastRow->nomor_rab);
            $sequence = (int) end($parts) + 1;
        }

        return $prefix . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Buat RAB baru beserta items-nya.
     */
    public function storeRab(array $data, User $user): Rab
    {
        return DB::transaction(function () use ($data, $user) {
            $nomorRab = $this->generateNomorRab($data['bulan_pengajuan']);

            $rab = Rab::create([
                'nomor_rab'       => $nomorRab,
                'divisi_id'       => $user->getRoleNames()->first(),
                'bulan_pengajuan' => Carbon::parse($data['bulan_pengajuan'])->startOfMonth(),
                'status'          => RabStatus::DRAFT,
                'dibuat_oleh'     => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $rab->items()->create([
                    'kegiatan'          => $item['kegiatan'],
                    'catatan_kegiatan'  => $item['catatan_kegiatan'] ?? null,
                    'biaya_anggaran'    => $item['biaya_anggaran'],
                    'waktu_pelaksanaan' => $item['waktu_pelaksanaan'],
                ]);
            }

            $this->updateComputedFields($rab);

            return $rab;
        });
    }

    /**
     * Update RAB header dan sync items.
     */
    public function updateRab(Rab $rab, array $data): Rab
    {
        return DB::transaction(function () use ($rab, $data) {
            $updateData = [];

            if (isset($data['bulan_pengajuan'])) {
                $updateData['bulan_pengajuan'] = Carbon::parse($data['bulan_pengajuan'])->startOfMonth();
            }

            // Update field lain yang diizinkan (kecuali divisi_id, dibuat_oleh, status, nomor_rab)
            $allowed = ['bulan_pengajuan'];
            foreach ($allowed as $field) {
                if (isset($data[$field]) && $field !== 'bulan_pengajuan') {
                    $updateData[$field] = $data[$field];
                }
            }

            if (!empty($updateData)) {
                $rab->update($updateData);
            }

            $rab->items()->delete();

            foreach ($data['items'] as $item) {
                $rab->items()->create([
                    'kegiatan'          => $item['kegiatan'],
                    'catatan_kegiatan'  => $item['catatan_kegiatan'] ?? null,
                    'biaya_anggaran'    => $item['biaya_anggaran'],
                    'waktu_pelaksanaan' => $item['waktu_pelaksanaan'],
                ]);
            }

            $this->updateComputedFields($rab);

            return $rab->refresh();
        });
    }

    /**
     * Hitung ulang total_kegiatan dan total_biaya_anggaran.
     */
    public function updateComputedFields(Rab $rab): void
    {
        $count = $rab->items()->count();
        $sum   = $rab->items()->sum('biaya_anggaran');

        $rab->update([
            'total_kegiatan'       => $count,
            'total_biaya_anggaran' => $sum,
        ]);
    }

    /**
     * Query builder untuk datatable dengan filter berbasis role.
     */
    public function getDatatableQuery(array $filters, User $user): Builder
    {
        $query = Rab::query();

        $roleName = $user->getRoleNames()->first();

        if ($roleName && str_starts_with($roleName, 'divisi_')) {
            $query->where('divisi_id', $roleName);
        } else {
            // bendahara_umum / ketua_yayasan — filter opsional
            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['divisi_id'])) {
                $query->where('divisi_id', $filters['divisi_id']);
            }

            if (!empty($filters['bulan'])) {
                $bulan = Carbon::createFromFormat('Y-m', $filters['bulan']);
                $query->where('bulan_pengajuan', '>=', $bulan->copy()->startOfMonth())
                      ->where('bulan_pengajuan', '<=', $bulan->copy()->endOfMonth());
            }
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q
                ->whereRaw('nomor_rab ILIKE ?', ["%{$search}%"])
                ->orWhereRaw('divisi_id ILIKE ?', ["%{$search}%"])
            );
        }

        $allowedSortFields = ['created_at', 'nomor_rab', 'divisi_id', 'bulan_pengajuan', 'status', 'total_kegiatan', 'total_biaya_anggaran'];
        $sortField = in_array($filters['sortField'] ?? null, $allowedSortFields) ? $filters['sortField'] : 'created_at';
        $sortOrder = in_array($filters['sortOrder'] ?? null, ['asc', 'desc']) ? $filters['sortOrder'] : 'desc';

        $query->orderBy($sortField, $sortOrder);

        return $query;
    }

    /**
     * Ajukan RAB ke Bendahara Umum.
     */
    public function submitRab(Rab $rab, User $user): Rab
    {
        $validStatuses = [
            RabStatus::DRAFT,
            RabStatus::REJECTED_BENDAHARA,
            RabStatus::REJECTED_KETUA,
        ];

        if (!in_array($rab->status, $validStatuses)) {
            abort(422, 'RAB tidak dapat diajukan karena statusnya tidak valid.');
        }

        $rab->update(['status' => RabStatus::PENDING_BENDAHARA]);

        RabApprovalLog::create([
            'rab_id'        => $rab->id,
            'aksi'          => RabAksiLog::SUBMITTED,
            'dilakukan_oleh' => $user->id,
            'catatan'       => null,
        ]);

        return $rab->refresh();
    }

    /**
     * Batalkan RAB.
     */
    public function cancelRab(Rab $rab, User $user): Rab
    {
        $invalidStatuses = [RabStatus::APPROVED, RabStatus::CANCELLED];

        if (in_array($rab->status, $invalidStatuses)) {
            abort(422, 'RAB tidak dapat dibatalkan karena statusnya tidak valid.');
        }

        $rab->update(['status' => RabStatus::CANCELLED]);

        RabApprovalLog::create([
            'rab_id'        => $rab->id,
            'aksi'          => RabAksiLog::CANCELLED,
            'dilakukan_oleh' => $user->id,
            'catatan'       => null,
        ]);

        return $rab->refresh();
    }

    /**
     * Setujui RAB (deteksi role otomatis).
     */
    public function approveRab(Rab $rab, User $user, ?string $catatan): Rab
    {
        if ($user->hasRole('bendahara_umum')) {
            if ($rab->status !== RabStatus::PENDING_BENDAHARA) {
                abort(422, 'RAB tidak dapat disetujui karena statusnya tidak valid.');
            }

            $rab->update(['status' => RabStatus::PENDING_KETUA]);

            RabApprovalLog::create([
                'rab_id'        => $rab->id,
                'aksi'          => RabAksiLog::APPROVED_BENDAHARA,
                'dilakukan_oleh' => $user->id,
                'catatan'       => $catatan,
            ]);
        } elseif ($user->hasRole('ketua_yayasan')) {
            if ($rab->status !== RabStatus::PENDING_KETUA) {
                abort(422, 'RAB tidak dapat disetujui karena statusnya tidak valid.');
            }

            $rab->update(['status' => RabStatus::APPROVED]);

            RabApprovalLog::create([
                'rab_id'        => $rab->id,
                'aksi'          => RabAksiLog::APPROVED_KETUA,
                'dilakukan_oleh' => $user->id,
                'catatan'       => $catatan,
            ]);
        } else {
            abort(403, 'Anda tidak berwenang untuk menyetujui RAB.');
        }

        return $rab->refresh();
    }

    /**
     * Tolak RAB (deteksi role otomatis).
     */
    public function rejectRab(Rab $rab, User $user, string $catatan): Rab
    {
        if (empty(trim($catatan))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'catatan' => ['Catatan penolakan wajib diisi.'],
            ]);
        }

        if ($user->hasRole('bendahara_umum')) {
            if ($rab->status !== RabStatus::PENDING_BENDAHARA) {
                abort(422, 'RAB tidak dapat ditolak karena statusnya tidak valid.');
            }

            $rab->update(['status' => RabStatus::REJECTED_BENDAHARA]);

            RabApprovalLog::create([
                'rab_id'        => $rab->id,
                'aksi'          => RabAksiLog::REJECTED_BENDAHARA,
                'dilakukan_oleh' => $user->id,
                'catatan'       => $catatan,
            ]);
        } elseif ($user->hasRole('ketua_yayasan')) {
            if ($rab->status !== RabStatus::PENDING_KETUA) {
                abort(422, 'RAB tidak dapat ditolak karena statusnya tidak valid.');
            }

            $rab->update(['status' => RabStatus::REJECTED_KETUA]);

            RabApprovalLog::create([
                'rab_id'        => $rab->id,
                'aksi'          => RabAksiLog::REJECTED_KETUA,
                'dilakukan_oleh' => $user->id,
                'catatan'       => $catatan,
            ]);
        } else {
            abort(403, 'Anda tidak berwenang untuk menolak RAB.');
        }

        return $rab->refresh();
    }
}
