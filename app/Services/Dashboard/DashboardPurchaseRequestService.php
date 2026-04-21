<?php

namespace App\Services\Dashboard;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\MasterService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardPurchaseRequestService extends MasterService
{
    private const PRIVILEGED_ROLES = ['bendahara_umum', 'ketua_yayasan', 'divisi_sarpras'];

    private const MONTH_LABELS = [
        1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
        5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Agu',
        9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Cek apakah user adalah privileged user.
     */
    private function isPrivileged(User $user): bool
    {
        return in_array($user->getRoleNames()->first(), self::PRIVILEGED_ROLES);
    }

    /**
     * Build base query dengan filter role dan periode.
     */
    public function buildBaseQuery(array $filters, User $user): Builder
    {
        $query = PurchaseRequest::query();

        $roleName = $user->getRoleNames()->first();

        if ($roleName && str_starts_with($roleName, 'divisi_') && !$this->isPrivileged($user)) {
            // Non-privileged divisi user: selalu filter by divisi sendiri
            $query->where('divisi_id', $roleName);
        } elseif ($this->isPrivileged($user)) {
            // Privileged user: filter divisi_id opsional dari params
            if (!empty($filters['divisi_id'])) {
                $query->where('divisi_id', $filters['divisi_id']);
            }
        }

        // Filter tahun — REQUIRED, selalu diterapkan
        $query->whereYear('created_at', $filters['tahun']);

        // Filter bulan — opsional
        if (!empty($filters['bulan'])) {
            $query->whereMonth('created_at', $filters['bulan']);
        }

        return $query;
    }

    /**
     * Aggregate summary data untuk Summary Cards.
     */
    public function getSummary(array $filters, User $user): array
    {
        $query = $this->buildBaseQuery($filters, $user);

        $totalPengajuan = (clone $query)->count();

        $totalDalamApproval = (clone $query)->whereIn('status', [
            PurchaseRequestStatus::PENDING_BENDAHARA,
            PurchaseRequestStatus::PENDING_KETUA,
        ])->count();

        $totalDalamPembelian = (clone $query)->whereIn('status', [
            PurchaseRequestStatus::PURCHASING,
            PurchaseRequestStatus::PARTIALLY_PURCHASED,
        ])->count();

        $totalSelesai = (clone $query)->where('status', PurchaseRequestStatus::COMPLETED)->count();

        $totalBiayaEstimasi = (float) (clone $query)->sum('total_biaya_estimasi');
        $totalBiayaAktual   = (float) (clone $query)->sum('total_biaya_aktual');

        return [
            'total_pengajuan'                => $totalPengajuan,
            'total_dalam_approval'           => $totalDalamApproval,
            'total_dalam_pembelian'          => $totalDalamPembelian,
            'total_selesai'                  => $totalSelesai,
            'total_biaya_estimasi'           => $totalBiayaEstimasi,
            'total_biaya_estimasi_formatted' => number_format($totalBiayaEstimasi, 0, ',', '.'),
            'total_biaya_aktual'             => $totalBiayaAktual,
            'total_biaya_aktual_formatted'   => number_format($totalBiayaAktual, 0, ',', '.'),
        ];
    }

    /**
     * Data distribusi status per bulan untuk chart.
     */
    public function getChartData(array $filters, User $user): array
    {
        // Selalu query full year (abaikan filter bulan)
        $filtersWithoutBulan = array_merge($filters, ['bulan' => null]);
        $query = $this->buildBaseQuery($filtersWithoutBulan, $user);

        $rows = (clone $query)
            ->select(
                DB::raw('EXTRACT(MONTH FROM created_at)::integer as month'),
                'status',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy(DB::raw('EXTRACT(MONTH FROM created_at)::integer'), 'status')
            ->get();

        // Kelompokkan per bulan
        $byMonth = [];
        foreach ($rows as $row) {
            $month = (int) $row->month;
            $statusValue = $row->status instanceof PurchaseRequestStatus
                ? $row->status->value
                : $row->status;

            if (!isset($byMonth[$month])) {
                $byMonth[$month] = [];
            }
            $byMonth[$month][$statusValue] = (int) $row->count;
        }

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'month'  => $m,
                'label'  => self::MONTH_LABELS[$m],
                'data'   => $byMonth[$m] ?? [],
            ];
        }

        return $result;
    }

    /**
     * Ambil distinct tahun dari kolom created_at, tambahkan tahun berjalan jika belum ada.
     */
    public function getAvailableYears(): array
    {
        $years = PurchaseRequest::query()
            ->selectRaw('EXTRACT(YEAR FROM created_at)::integer as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($y) => (int) $y)
            ->toArray();

        $currentYear = (int) Carbon::now()->year;

        if (!in_array($currentYear, $years)) {
            array_unshift($years, $currentYear);
        }

        rsort($years);

        return $years;
    }

    /**
     * Ambil distinct divisi_id dari tabel purchase_requests untuk dropdown filter.
     */
    public function getDivisiList(): array
    {
        return PurchaseRequest::query()
            ->select('divisi_id')
            ->distinct()
            ->orderBy('divisi_id')
            ->pluck('divisi_id')
            ->map(fn ($d) => (string) $d)
            ->toArray();
    }
}
