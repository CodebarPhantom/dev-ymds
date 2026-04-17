<?php

namespace App\Services\Dashboard;

use App\Enums\RabStatus;
use App\Models\Rab;
use App\Models\User;
use App\Services\MasterService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class DashboardRabService extends MasterService
{
    private const PRIVILEGED_ROLES = ['bendahara_umum', 'ketua_yayasan', 'super_admin'];

    private const MONTH_LABELS = [
        1  => 'Jan', 2  => 'Feb', 3  => 'Mar', 4  => 'Apr',
        5  => 'Mei', 6  => 'Jun', 7  => 'Jul', 8  => 'Agu',
        9  => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
    ];

    /**
     * Cek apakah user adalah Privileged_User.
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
        $query = Rab::query();

        $roleName = $user->getRoleNames()->first();

        if ($roleName && str_starts_with($roleName, 'divisi_')) {
            // Divisi user: selalu filter by divisi sendiri, abaikan divisi_id dari filters
            $query->where('divisi_id', $roleName);
        } elseif ($this->isPrivileged($user)) {
            // Privileged_User: filter divisi_id opsional dari params
            if (!empty($filters['divisi_id'])) {
                $query->where('divisi_id', $filters['divisi_id']);
            }
        }

        // Filter tahun — REQUIRED, selalu diterapkan
        $query->whereYear('bulan_pengajuan', $filters['tahun']);

        // Filter bulan — opsional
        if (!empty($filters['bulan'])) {
            $query->whereMonth('bulan_pengajuan', $filters['bulan']);
        }

        return $query;
    }

    /**
     * Aggregate summary data untuk Summary Cards.
     */
    public function getSummary(array $filters, User $user): array
    {
        $query = $this->buildBaseQuery($filters, $user);

        $totalRab      = (clone $query)->count();
        $totalKegiatan = (clone $query)->sum('total_kegiatan');
        $totalAnggaran = (clone $query)->sum('total_biaya_anggaran');
        $totalApproved = (clone $query)->where('status', RabStatus::APPROVED)->count();
        $totalPending  = (clone $query)->whereIn('status', [
            RabStatus::PENDING_BENDAHARA,
            RabStatus::PENDING_KETUA,
        ])->count();

        return [
            'total_rab'               => $totalRab,
            'total_kegiatan'          => (int) $totalKegiatan,
            'total_anggaran'          => (float) $totalAnggaran,
            'total_anggaran_formatted' => number_format((float) $totalAnggaran, 0, ',', '.'),
            'total_approved'          => $totalApproved,
            'total_pending'           => $totalPending,
        ];
    }

    /**
     * Data untuk semua chart di dashboard.
     */
    public function getChartData(array $filters, User $user): array
    {
        // Status distribution — gunakan buildBaseQuery (dengan filter bulan jika ada)
        $statusDistribution = $this->getStatusDistribution($filters, $user);

        // Monthly trend — selalu query full year (abaikan filter bulan)
        $monthlyTrend = $this->getMonthlyTrend($filters, $user);

        // Divisi comparison — hanya untuk Privileged_User
        $divisiComparison = $this->isPrivileged($user)
            ? $this->getDivisiComparison($filters, $user)
            : [];

        return [
            'status_distribution' => $statusDistribution,
            'monthly_trend'       => $monthlyTrend,
            'divisi_comparison'   => $divisiComparison,
        ];
    }

    /**
     * Distribusi jumlah RAB per status.
     */
    private function getStatusDistribution(array $filters, User $user): array
    {
        $query = $this->buildBaseQuery($filters, $user);

        $rows = (clone $query)
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        $statusLabels = [
            RabStatus::DRAFT->value              => 'Draft',
            RabStatus::PENDING_BENDAHARA->value  => 'Menunggu Bendahara',
            RabStatus::REJECTED_BENDAHARA->value => 'Ditolak Bendahara',
            RabStatus::PENDING_KETUA->value      => 'Menunggu Ketua',
            RabStatus::REJECTED_KETUA->value     => 'Ditolak Ketua',
            RabStatus::APPROVED->value           => 'Disetujui',
            RabStatus::CANCELLED->value          => 'Dibatalkan',
        ];

        return $rows->map(function ($row) use ($statusLabels) {
            $statusValue = $row->status instanceof RabStatus
                ? $row->status->value
                : $row->status;

            return [
                'status' => $statusLabels[$statusValue] ?? $statusValue,
                'count'  => (int) $row->count,
            ];
        })->values()->toArray();
    }

    /**
     * Tren total anggaran per bulan dalam tahun terpilih (selalu 12 bulan).
     */
    private function getMonthlyTrend(array $filters, User $user): array
    {
        // Buat query tanpa filter bulan — hanya filter tahun + role
        $filtersWithoutBulan = array_merge($filters, ['bulan' => null]);
        $query = $this->buildBaseQuery($filtersWithoutBulan, $user);

        $rows = (clone $query)
            ->select(
                DB::raw('EXTRACT(MONTH FROM bulan_pengajuan) as month'),
                DB::raw('SUM(total_biaya_anggaran) as total')
            )
            ->groupBy(DB::raw('EXTRACT(MONTH FROM bulan_pengajuan)'))
            ->get()
            ->keyBy(fn ($row) => (int) $row->month);

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[] = [
                'month' => $m,
                'label' => self::MONTH_LABELS[$m],
                'total' => isset($rows[$m]) ? (float) $rows[$m]->total : 0.0,
            ];
        }

        return $result;
    }

    /**
     * Perbandingan total anggaran per divisi (hanya Privileged_User).
     */
    private function getDivisiComparison(array $filters, User $user): array
    {
        $query = $this->buildBaseQuery($filters, $user);

        $rows = (clone $query)
            ->select('divisi_id', DB::raw('SUM(total_biaya_anggaran) as total'))
            ->groupBy('divisi_id')
            ->get();

        return $rows->map(fn ($row) => [
            'divisi_id' => $row->divisi_id,
            'total'     => (float) $row->total,
        ])->values()->toArray();
    }

    /**
     * Ambil distinct tahun dari kolom bulan_pengajuan, tambahkan tahun berjalan jika belum ada.
     */
    public function getAvailableYears(): array
    {
        $years = Rab::query()
            ->selectRaw('EXTRACT(YEAR FROM bulan_pengajuan)::integer as year')
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
     * Ambil distinct divisi_id dari tabel rabs untuk dropdown filter.
     */
    public function getDivisiList(): array
    {
        return Rab::query()
            ->select('divisi_id')
            ->distinct()
            ->orderBy('divisi_id')
            ->pluck('divisi_id')
            ->map(fn ($d) => (string) $d)
            ->toArray();
    }
}
