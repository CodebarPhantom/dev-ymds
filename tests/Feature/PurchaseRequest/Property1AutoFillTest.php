<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 1: New PurchaseRequest Auto-Fill Invariant
 *
 * For any user dengan role `divisi_*` yang membuat PurchaseRequest baru
 * dengan data valid, PurchaseRequest yang tersimpan harus memiliki:
 * - divisi_id sama dengan role_name user tersebut
 * - dibuat_oleh sama dengan id user tersebut
 * - status sama dengan PurchaseRequestStatus::DRAFT
 *
 * Validates: Requirements 1.2, 1.3, 1.4
 */
class Property1AutoFillTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();
    }

    private function validPrData(): array
    {
        return [
            'judul_pengajuan'   => 'Test Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'             => [
                [
                    'nama_barang'    => 'Test Barang',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => 10000,
                ],
            ],
        ];
    }

    /**
     * Property 1: New PurchaseRequest Auto-Fill Invariant
     *
     * Tests that for any divisi_* role, the stored PurchaseRequest always has:
     * - divisi_id === user's role name
     * - dibuat_oleh === user's id
     * - status === DRAFT
     *
     * Validates: Requirements 1.2, 1.3, 1.4
     */
    public function test_new_purchase_request_auto_fills_divisi_id_dibuat_oleh_and_status(): void
    {
        $divisiRoles = ['divisi_it', 'divisi_finance', 'divisi_hr', 'divisi_ops'];

        foreach ($divisiRoles as $roleName) {
            // Create role if not exists
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Create user with that role
            $user = User::factory()->create(['location_id' => 'test-location']);
            $user->assignRole($role);

            // Call service store()
            $pr = $this->service->store($this->validPrData(), $user);

            // Assert divisi_id === user's role name
            $this->assertEquals(
                $roleName,
                $pr->divisi_id,
                "divisi_id should equal role name '{$roleName}' for user with that role"
            );

            // Assert dibuat_oleh === user's id
            $this->assertEquals(
                $user->id,
                $pr->dibuat_oleh,
                "dibuat_oleh should equal user id for role '{$roleName}'"
            );

            // Assert status === DRAFT
            $this->assertEquals(
                PurchaseRequestStatus::DRAFT,
                $pr->status,
                "status should be DRAFT for newly created PurchaseRequest with role '{$roleName}'"
            );
        }
    }
}
