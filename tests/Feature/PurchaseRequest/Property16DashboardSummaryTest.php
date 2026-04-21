<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\Dashboard\DashboardPurchaseRequestService;
use App\Services\PurchaseRequestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 16: Dashboard Summary Konsistensi
 *
 * For any set filter (tahun, bulan, divisi_id), nilai `total_pengajuan`,
 * `total_dalam_approval`, `total_dalam_pembelian`, `total_selesai`,
 * `total_biaya_estimasi`, dan `total_biaya_aktual` yang dikembalikan endpoint
 * summary harus konsisten dengan data aktual di database yang memenuhi filter tersebut.
 *
 * Validates: Requirements 13.7, 13.8, 13.9, 13.10, 13.11, 13.12
 */
class Property16DashboardSummaryTest extends TestCase
{
    use RefreshDatabase;

    private DashboardPurchaseRequestService $dashboardService;
    private PurchaseRequestService $prService;
    private User $privilegedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dashboardService = new DashboardPurchaseRequestService();
        $this->prService        = new PurchaseRequestService();

        $bendaharaRole        = Role::firstOrCreate(['name' => 'bendahara_umum', 'guard_name' => 'web']);
        $this->privilegedUser = User::factory()->create(['location_id' => 'test-location']);
        $this->privilegedUser->assignRole($bendaharaRole);
    }

    private function createRole(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = $this->createRole($roleName);
        $user = User::factory()->create(['location_id' => 'test-location']);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Create a PR directly in the DB with a specific created_at and status.
     */
    private function createPrWithDate(
        string $divisiRole,
        string $createdAt,
        PurchaseRequestStatus $status = PurchaseRequestStatus::DRAFT,
        float $biayaEstimasi = 10000,
        float $biayaAktual = 0
    ): PurchaseRequest {
        Carbon::setTestNow($createdAt);

        $user = $this->createUserWithRole($divisiRole);
        $pr   = $this->prService->store([
            'judul_pengajuan'    => 'Test Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Barang Test',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => $biayaEstimasi,
                ],
            ],
        ], $user);

        $pr->update([
            'status'              => $status,
            'total_biaya_aktual'  => $biayaAktual,
        ]);

        Carbon::setTestNow(); // reset

        return $pr->fresh();
    }

    // -------------------------------------------------------------------------
    // Test 1: Summary counts match actual DB counts
    // -------------------------------------------------------------------------

    /**
     * Validates: Requirements 13.7, 13.8, 13.9, 13.10
     */
    public function test_summary_counts_match_actual_database_counts(): void
    {
        $year = 2025;

        // DRAFT — not counted in any sub-category
        $this->createPrWithDate('divisi_it', "{$year}-03-10", PurchaseRequestStatus::DRAFT);

        // PENDING_BENDAHARA — dalam approval
        $this->createPrWithDate('divisi_it', "{$year}-03-11", PurchaseRequestStatus::PENDING_BENDAHARA);

        // PENDING_KETUA — dalam approval
        $this->createPrWithDate('divisi_finance', "{$year}-03-12", PurchaseRequestStatus::PENDING_KETUA);

        // PURCHASING — dalam pembelian
        $this->createPrWithDate('divisi_it', "{$year}-03-13", PurchaseRequestStatus::PURCHASING);

        // PARTIALLY_PURCHASED — dalam pembelian
        $this->createPrWithDate('divisi_finance', "{$year}-03-14", PurchaseRequestStatus::PARTIALLY_PURCHASED);

        // COMPLETED — selesai
        $this->createPrWithDate('divisi_it', "{$year}-03-15", PurchaseRequestStatus::COMPLETED);
        $this->createPrWithDate('divisi_finance', "{$year}-03-16", PurchaseRequestStatus::COMPLETED);

        // CANCELLED — counted in total but not in sub-categories
        $this->createPrWithDate('divisi_it', "{$year}-03-17", PurchaseRequestStatus::CANCELLED);

        $filters = ['tahun' => $year];
        $summary = $this->dashboardService->getSummary($filters, $this->privilegedUser);

        // total_pengajuan = all 8 PRs
        $this->assertEquals(8, $summary['total_pengajuan'], 'total_pengajuan should count all PRs');

        // total_dalam_approval = PENDING_BENDAHARA + PENDING_KETUA = 2
        $this->assertEquals(2, $summary['total_dalam_approval'], 'total_dalam_approval should count PENDING_BENDAHARA + PENDING_KETUA');

        // total_dalam_pembelian = PURCHASING + PARTIALLY_PURCHASED = 2
        $this->assertEquals(2, $summary['total_dalam_pembelian'], 'total_dalam_pembelian should count PURCHASING + PARTIALLY_PURCHASED');

        // total_selesai = COMPLETED = 2
        $this->assertEquals(2, $summary['total_selesai'], 'total_selesai should count COMPLETED PRs');

        // Verify counts match actual DB queries
        $actualTotal = PurchaseRequest::whereYear('created_at', $year)->count();
        $this->assertEquals($actualTotal, $summary['total_pengajuan']);

        $actualDalamApproval = PurchaseRequest::whereYear('created_at', $year)
            ->whereIn('status', [
                PurchaseRequestStatus::PENDING_BENDAHARA,
                PurchaseRequestStatus::PENDING_KETUA,
            ])->count();
        $this->assertEquals($actualDalamApproval, $summary['total_dalam_approval']);

        $actualDalamPembelian = PurchaseRequest::whereYear('created_at', $year)
            ->whereIn('status', [
                PurchaseRequestStatus::PURCHASING,
                PurchaseRequestStatus::PARTIALLY_PURCHASED,
            ])->count();
        $this->assertEquals($actualDalamPembelian, $summary['total_dalam_pembelian']);

        $actualSelesai = PurchaseRequest::whereYear('created_at', $year)
            ->where('status', PurchaseRequestStatus::COMPLETED)
            ->count();
        $this->assertEquals($actualSelesai, $summary['total_selesai']);
    }

    // -------------------------------------------------------------------------
    // Test 2: Summary biaya match actual DB sums
    // -------------------------------------------------------------------------

    /**
     * Validates: Requirements 13.11, 13.12
     */
    public function test_summary_biaya_match_actual_database_sums(): void
    {
        $year = 2025;

        $this->createPrWithDate('divisi_it', "{$year}-04-01", PurchaseRequestStatus::DRAFT, 100000, 0);
        $this->createPrWithDate('divisi_it', "{$year}-04-02", PurchaseRequestStatus::COMPLETED, 200000, 180000);
        $this->createPrWithDate('divisi_finance', "{$year}-04-03", PurchaseRequestStatus::PURCHASING, 150000, 0);
        $this->createPrWithDate('divisi_finance', "{$year}-04-04", PurchaseRequestStatus::COMPLETED, 300000, 290000);

        $filters = ['tahun' => $year];
        $summary = $this->dashboardService->getSummary($filters, $this->privilegedUser);

        // Verify against actual DB sums
        $actualBiayaEstimasi = (float) PurchaseRequest::whereYear('created_at', $year)
            ->sum('total_biaya_estimasi');
        $actualBiayaAktual = (float) PurchaseRequest::whereYear('created_at', $year)
            ->sum('total_biaya_aktual');

        $this->assertEquals(
            $actualBiayaEstimasi,
            $summary['total_biaya_estimasi'],
            'total_biaya_estimasi should match sum of all total_biaya_estimasi in DB'
        );

        $this->assertEquals(
            $actualBiayaAktual,
            $summary['total_biaya_aktual'],
            'total_biaya_aktual should match sum of all total_biaya_aktual in DB'
        );

        // Also verify the expected values directly
        $this->assertEquals(750000.0, $summary['total_biaya_estimasi']);
        $this->assertEquals(470000.0, $summary['total_biaya_aktual']);
    }

    // -------------------------------------------------------------------------
    // Test 3: Summary filters by year
    // -------------------------------------------------------------------------

    /**
     * Validates: Requirements 13.7, 13.8, 13.9, 13.10, 13.11, 13.12
     */
    public function test_summary_filters_by_year(): void
    {
        // PRs in 2024
        $this->createPrWithDate('divisi_it', '2024-06-01', PurchaseRequestStatus::COMPLETED, 50000, 45000);
        $this->createPrWithDate('divisi_it', '2024-06-02', PurchaseRequestStatus::COMPLETED, 60000, 55000);

        // PRs in 2025
        $this->createPrWithDate('divisi_it', '2025-06-01', PurchaseRequestStatus::DRAFT, 100000, 0);
        $this->createPrWithDate('divisi_it', '2025-06-02', PurchaseRequestStatus::PENDING_BENDAHARA, 200000, 0);
        $this->createPrWithDate('divisi_it', '2025-06-03', PurchaseRequestStatus::COMPLETED, 300000, 280000);

        // Query for 2025 only
        $summary2025 = $this->dashboardService->getSummary(['tahun' => 2025], $this->privilegedUser);

        $this->assertEquals(3, $summary2025['total_pengajuan'], '2025 filter should return 3 PRs');
        $this->assertEquals(1, $summary2025['total_dalam_approval'], '2025 filter: 1 in approval');
        $this->assertEquals(1, $summary2025['total_selesai'], '2025 filter: 1 completed');
        $this->assertEquals(600000.0, $summary2025['total_biaya_estimasi'], '2025 filter: sum estimasi');
        $this->assertEquals(280000.0, $summary2025['total_biaya_aktual'], '2025 filter: sum aktual');

        // Query for 2024 only
        $summary2024 = $this->dashboardService->getSummary(['tahun' => 2024], $this->privilegedUser);

        $this->assertEquals(2, $summary2024['total_pengajuan'], '2024 filter should return 2 PRs');
        $this->assertEquals(2, $summary2024['total_selesai'], '2024 filter: 2 completed');
        $this->assertEquals(110000.0, $summary2024['total_biaya_estimasi'], '2024 filter: sum estimasi');
        $this->assertEquals(100000.0, $summary2024['total_biaya_aktual'], '2024 filter: sum aktual');

        // Verify 2025 counts match actual DB
        $actual2025Total = PurchaseRequest::whereYear('created_at', 2025)->count();
        $this->assertEquals($actual2025Total, $summary2025['total_pengajuan']);
    }

    // -------------------------------------------------------------------------
    // Test 4: Summary filters by month
    // -------------------------------------------------------------------------

    /**
     * Validates: Requirements 13.7, 13.8, 13.9, 13.10, 13.11, 13.12
     */
    public function test_summary_filters_by_month(): void
    {
        $year = 2025;

        // PRs in January 2025
        $this->createPrWithDate('divisi_it', "{$year}-01-10", PurchaseRequestStatus::DRAFT, 100000, 0);
        $this->createPrWithDate('divisi_it', "{$year}-01-15", PurchaseRequestStatus::PENDING_BENDAHARA, 200000, 0);

        // PRs in March 2025
        $this->createPrWithDate('divisi_finance', "{$year}-03-05", PurchaseRequestStatus::COMPLETED, 300000, 250000);
        $this->createPrWithDate('divisi_finance', "{$year}-03-20", PurchaseRequestStatus::PURCHASING, 150000, 0);
        $this->createPrWithDate('divisi_it', "{$year}-03-25", PurchaseRequestStatus::COMPLETED, 400000, 380000);

        // Filter by January
        $summaryJan = $this->dashboardService->getSummary(
            ['tahun' => $year, 'bulan' => 1],
            $this->privilegedUser
        );

        $this->assertEquals(2, $summaryJan['total_pengajuan'], 'January filter: 2 PRs');
        $this->assertEquals(1, $summaryJan['total_dalam_approval'], 'January filter: 1 in approval');
        $this->assertEquals(0, $summaryJan['total_selesai'], 'January filter: 0 completed');
        $this->assertEquals(300000.0, $summaryJan['total_biaya_estimasi'], 'January filter: sum estimasi');
        $this->assertEquals(0.0, $summaryJan['total_biaya_aktual'], 'January filter: sum aktual');

        // Filter by March
        $summaryMar = $this->dashboardService->getSummary(
            ['tahun' => $year, 'bulan' => 3],
            $this->privilegedUser
        );

        $this->assertEquals(3, $summaryMar['total_pengajuan'], 'March filter: 3 PRs');
        $this->assertEquals(0, $summaryMar['total_dalam_approval'], 'March filter: 0 in approval');
        $this->assertEquals(1, $summaryMar['total_dalam_pembelian'], 'March filter: 1 in pembelian');
        $this->assertEquals(2, $summaryMar['total_selesai'], 'March filter: 2 completed');
        $this->assertEquals(850000.0, $summaryMar['total_biaya_estimasi'], 'March filter: sum estimasi');
        $this->assertEquals(630000.0, $summaryMar['total_biaya_aktual'], 'March filter: sum aktual');

        // Verify March counts match actual DB
        $actualMarTotal = PurchaseRequest::whereYear('created_at', $year)
            ->whereMonth('created_at', 3)
            ->count();
        $this->assertEquals($actualMarTotal, $summaryMar['total_pengajuan']);

        $actualMarEstimasi = (float) PurchaseRequest::whereYear('created_at', $year)
            ->whereMonth('created_at', 3)
            ->sum('total_biaya_estimasi');
        $this->assertEquals($actualMarEstimasi, $summaryMar['total_biaya_estimasi']);
    }
}
