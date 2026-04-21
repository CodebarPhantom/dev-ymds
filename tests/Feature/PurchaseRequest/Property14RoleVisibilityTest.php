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
 * Property 14: Role-Based Data Visibility
 *
 * For any user dengan role `divisi_*` (kecuali `divisi_sarpras`), semua data yang
 * dikembalikan harus memiliki `divisi_id` sama dengan `role_name` user tersebut.
 * User dengan role `bendahara_umum`, `ketua_yayasan`, atau `divisi_sarpras` harus
 * mendapatkan semua PurchaseRequest dari semua divisi.
 *
 * Validates: Requirements 8.1, 9.1, 12.1, 12.2
 */
class Property14RoleVisibilityTest extends TestCase
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
     * Validates: Requirements 8.1, 12.1
     */
    public function test_divisi_user_only_sees_own_divisi_data(): void
    {
        // Create PRs for two different divisi
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_finance');
        $this->createPrForDivisi('divisi_finance');

        $divisiItUser = $this->createUserWithRole('divisi_it');

        $results = $this->service->getDatatableQuery([], $divisiItUser)->get();

        $this->assertNotEmpty($results);

        foreach ($results as $pr) {
            $this->assertEquals(
                'divisi_it',
                $pr->divisi_id,
                "divisi_it user should only see PRs with divisi_id = 'divisi_it', got '{$pr->divisi_id}'"
            );
        }

        // Ensure divisi_finance PRs are not visible
        $financeCount = $results->where('divisi_id', 'divisi_finance')->count();
        $this->assertEquals(0, $financeCount, 'divisi_it user should not see divisi_finance PRs');

        // Ensure divisi_it PRs are visible
        $itCount = $results->where('divisi_id', 'divisi_it')->count();
        $this->assertEquals(2, $itCount, 'divisi_it user should see all 2 divisi_it PRs');
    }

    /**
     * Validates: Requirements 9.1, 12.2
     */
    public function test_privileged_user_sees_all_divisi_data(): void
    {
        // Create PRs for multiple divisi
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_finance');
        $this->createPrForDivisi('divisi_hrd');

        $bendaharaUser = $this->createUserWithRole('bendahara_umum');

        $results = $this->service->getDatatableQuery([], $bendaharaUser)->get();

        $this->assertCount(3, $results, 'bendahara_umum should see all 3 PRs from all divisi');

        $divisiIds = $results->pluck('divisi_id')->unique()->sort()->values()->toArray();
        $this->assertContains('divisi_it', $divisiIds);
        $this->assertContains('divisi_finance', $divisiIds);
        $this->assertContains('divisi_hrd', $divisiIds);
    }

    /**
     * Validates: Requirements 9.1, 12.2
     */
    public function test_sarpras_user_sees_all_divisi_data(): void
    {
        // Create PRs for multiple divisi
        $this->createPrForDivisi('divisi_it');
        $this->createPrForDivisi('divisi_finance');

        $sarprasUser = $this->createUserWithRole('divisi_sarpras');

        $results = $this->service->getDatatableQuery([], $sarprasUser)->get();

        $this->assertCount(2, $results, 'divisi_sarpras should see all PRs from all divisi');

        $divisiIds = $results->pluck('divisi_id')->unique()->sort()->values()->toArray();
        $this->assertContains('divisi_it', $divisiIds);
        $this->assertContains('divisi_finance', $divisiIds);
    }
}
