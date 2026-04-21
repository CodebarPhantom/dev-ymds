<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 17: Export Data Completeness dan Role Isolation
 *
 * For any user dengan role `divisi_*`, file export yang dihasilkan hanya boleh
 * berisi PurchaseRequest dengan `divisi_id` sama dengan role user tersebut.
 * Untuk Privileged_User dengan filter `divisi_id` tertentu, file export hanya
 * boleh berisi data dari divisi tersebut.
 *
 * Validates: Requirements 13.36, 13.37, 13.45, 13.46
 */
class Property17ExportTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();
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

    private function createPrForDivisi(string $divisiRole): PurchaseRequest
    {
        $user = $this->createUserWithRole($divisiRole);
        return $this->service->store([
            'judul_pengajuan'    => "Pengajuan dari {$divisiRole}",
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Barang Test',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => 10000,
                ],
            ],
        ], $user);
    }

    /**
     * Validates: Requirements 13.36, 13.45
     */
    public function test_divisi_user_export_only_contains_own_divisi_data(): void
    {
        // Create PRs for two different divisi
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_finance');
        $this->createPrForDivisi('divisi_finance');

        $divisiItUser = $this->createUserWithRole('divisi_it');

        // The export uses the same getDatatableQuery as the datatable
        $exportQuery = $this->service->getDatatableQuery([], $divisiItUser);
        $results = $exportQuery->get();

        $this->assertNotEmpty($results, 'Export should contain data for divisi_it user');

        // All exported rows must belong to divisi_it only
        foreach ($results as $pr) {
            $this->assertEquals(
                'divisi_it',
                $pr->divisi_id,
                "Export for divisi_it user must only contain PRs with divisi_id = 'divisi_it', got '{$pr->divisi_id}'"
            );
        }

        // divisi_finance PRs must not appear in the export
        $financeCount = $results->where('divisi_id', 'divisi_finance')->count();
        $this->assertEquals(0, $financeCount, 'divisi_it user export must not contain divisi_finance PRs');

        // Both divisi_it PRs must be present
        $itCount = $results->where('divisi_id', 'divisi_it')->count();
        $this->assertEquals(2, $itCount, 'divisi_it user export must contain all 2 divisi_it PRs');
    }

    /**
     * Validates: Requirements 13.37, 13.46
     */
    public function test_privileged_user_export_with_divisi_filter_contains_only_that_divisi(): void
    {
        // Create PRs for multiple divisi
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_finance');
        $this->createPrForDivisi('divisi_hrd');

        $bendaharaUser = $this->createUserWithRole('bendahara_umum');

        // Export with divisi_id filter
        $exportQuery = $this->service->getDatatableQuery(['divisi_id' => 'divisi_it'], $bendaharaUser);
        $results = $exportQuery->get();

        $this->assertNotEmpty($results, 'Export should contain data when divisi_id filter is applied');

        // All exported rows must belong to the filtered divisi only
        foreach ($results as $pr) {
            $this->assertEquals(
                'divisi_it',
                $pr->divisi_id,
                "Privileged user export with divisi_id=divisi_it filter must only contain PRs with divisi_id = 'divisi_it', got '{$pr->divisi_id}'"
            );
        }

        // Exactly 2 divisi_it PRs should be in the export
        $this->assertCount(2, $results, 'Export with divisi_it filter should contain exactly 2 PRs');

        // Other divisi must not appear
        $nonItCount = $results->where('divisi_id', '!=', 'divisi_it')->count();
        $this->assertEquals(0, $nonItCount, 'Export with divisi_it filter must not contain PRs from other divisi');
    }

    /**
     * Validates: Requirements 13.37, 13.45
     */
    public function test_privileged_user_export_without_filter_contains_all_divisi_data(): void
    {
        // Create PRs for multiple divisi
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_finance');
        $this->createPrForDivisi('divisi_hrd');

        $ketuaUser = $this->createUserWithRole('ketua_yayasan');

        // Export without any divisi filter
        $exportQuery = $this->service->getDatatableQuery([], $ketuaUser);
        $results = $exportQuery->get();

        $this->assertCount(3, $results, 'Privileged user export without filter should contain all 3 PRs');

        $divisiIds = $results->pluck('divisi_id')->unique()->sort()->values()->toArray();
        $this->assertContains('divisi_it', $divisiIds, 'Export should include divisi_it data');
        $this->assertContains('divisi_finance', $divisiIds, 'Export should include divisi_finance data');
        $this->assertContains('divisi_hrd', $divisiIds, 'Export should include divisi_hrd data');
    }
}
