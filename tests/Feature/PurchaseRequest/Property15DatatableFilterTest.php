<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 15: Filter Datatable Konsistensi
 *
 * For any request datatable dengan filter `status`, `divisi_id`, atau `tanggal_dibutuhkan`,
 * semua item dalam response harus memenuhi semua kriteria filter yang diberikan.
 *
 * Validates: Requirements 9.2
 */
class Property15DatatableFilterTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $privilegedUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara_umum', 'guard_name' => 'web']);
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

    private function createPr(string $divisiRole, array $overrides = []): PurchaseRequest
    {
        $user = $this->createUserWithRole($divisiRole);
        $data = array_merge([
            'judul_pengajuan'    => 'Test Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Barang Test',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => 10000,
                ],
            ],
        ], $overrides);

        return $this->service->store($data, $user);
    }

    /**
     * Validates: Requirements 9.2
     */
    public function test_status_filter_returns_only_matching_status(): void
    {
        // Create PRs with different statuses
        $draftPr1 = $this->createPr('divisi_it');
        $draftPr2 = $this->createPr('divisi_finance');

        $pendingPr = $this->createPr('divisi_hrd');
        $pendingPr->update(['status' => PurchaseRequestStatus::PENDING_BENDAHARA]);

        $approvedPr = $this->createPr('divisi_it');
        $approvedPr->update(['status' => PurchaseRequestStatus::APPROVED]);

        $filters = ['status' => PurchaseRequestStatus::DRAFT->value];
        $results = $this->service->getDatatableQuery($filters, $this->privilegedUser)->get();

        $this->assertNotEmpty($results);

        foreach ($results as $pr) {
            $this->assertEquals(
                PurchaseRequestStatus::DRAFT,
                $pr->status,
                "All results should have status DRAFT, got '{$pr->status->value}'"
            );
        }

        $this->assertCount(2, $results, 'Should return exactly 2 DRAFT PRs');
    }

    /**
     * Validates: Requirements 9.2
     */
    public function test_divisi_id_filter_returns_only_matching_divisi(): void
    {
        // Create PRs for multiple divisi
        $this->createPr('divisi_it');
        $this->createPr('divisi_it');
        $this->createPr('divisi_finance');
        $this->createPr('divisi_hrd');

        $filters = ['divisi_id' => 'divisi_it'];
        $results = $this->service->getDatatableQuery($filters, $this->privilegedUser)->get();

        $this->assertNotEmpty($results);

        foreach ($results as $pr) {
            $this->assertEquals(
                'divisi_it',
                $pr->divisi_id,
                "All results should have divisi_id = 'divisi_it', got '{$pr->divisi_id}'"
            );
        }

        $this->assertCount(2, $results, 'Should return exactly 2 divisi_it PRs');
    }

    /**
     * Validates: Requirements 9.2
     */
    public function test_tanggal_dibutuhkan_filter_returns_only_matching_date(): void
    {
        $targetDate = '2025-06-15';
        $otherDate  = '2025-12-31';

        $this->createPr('divisi_it', ['tanggal_dibutuhkan' => $targetDate]);
        $this->createPr('divisi_finance', ['tanggal_dibutuhkan' => $targetDate]);
        $this->createPr('divisi_hrd', ['tanggal_dibutuhkan' => $otherDate]);

        $filters = ['tanggal_dibutuhkan' => $targetDate];
        $results = $this->service->getDatatableQuery($filters, $this->privilegedUser)->get();

        $this->assertNotEmpty($results);

        foreach ($results as $pr) {
            $this->assertEquals(
                $targetDate,
                $pr->tanggal_dibutuhkan->format('Y-m-d'),
                "All results should have tanggal_dibutuhkan = '{$targetDate}', got '{$pr->tanggal_dibutuhkan->format('Y-m-d')}'"
            );
        }

        $this->assertCount(2, $results, 'Should return exactly 2 PRs with the target date');
    }

    /**
     * Validates: Requirements 9.2
     */
    public function test_search_filter_returns_matching_nomor_or_judul(): void
    {
        $user = $this->createUserWithRole('divisi_it');

        // Create PRs with distinct judul
        $this->service->store([
            'judul_pengajuan'    => 'Pembelian Laptop Baru',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [['nama_barang' => 'Laptop', 'satuan' => 'unit', 'jumlah' => 1, 'biaya_estimasi' => 5000000]],
        ], $user);

        $this->service->store([
            'judul_pengajuan'    => 'Pembelian Printer Kantor',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [['nama_barang' => 'Printer', 'satuan' => 'unit', 'jumlah' => 1, 'biaya_estimasi' => 2000000]],
        ], $user);

        $this->service->store([
            'judul_pengajuan'    => 'Kebutuhan ATK Bulanan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [['nama_barang' => 'Pulpen', 'satuan' => 'lusin', 'jumlah' => 5, 'biaya_estimasi' => 50000]],
        ], $user);

        // Search by partial judul
        $filters = ['search' => 'Pembelian'];
        $results = $this->service->getDatatableQuery($filters, $this->privilegedUser)->get();

        $this->assertCount(2, $results, 'Should return 2 PRs matching "Pembelian"');

        foreach ($results as $pr) {
            $matchesNomor = stripos($pr->nomor_pengajuan, 'Pembelian') !== false;
            $matchesJudul = stripos($pr->judul_pengajuan, 'Pembelian') !== false;
            $this->assertTrue(
                $matchesNomor || $matchesJudul,
                "Result '{$pr->judul_pengajuan}' should match search term 'Pembelian'"
            );
        }

        // Search by partial nomor_pengajuan prefix
        $filters = ['search' => 'PB/'];
        $results = $this->service->getDatatableQuery($filters, $this->privilegedUser)->get();

        $this->assertCount(3, $results, 'All PRs should match nomor_pengajuan search "PB/"');
    }
}
