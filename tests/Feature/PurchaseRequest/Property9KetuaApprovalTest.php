<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use App\Exceptions\CustomException;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 9: Ketua Approval State Transition
 *
 * For any PurchaseRequest berstatus PENDING_KETUA:
 * - jika di-approve oleh ketua_yayasan → status berubah ke APPROVED
 * - jika di-reject → status berubah ke REJECTED_KETUA
 * - Aksi oleh user non-ketua_yayasan → CustomException HTTP 403
 * - Aksi pada status selain PENDING_KETUA → CustomException HTTP 422
 *
 * Validates: Requirements 5.1, 5.2, 5.5, 5.6
 */
class Property9KetuaApprovalTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $divisiUser;
    private User $ketuaUser;
    private User $nonKetuaUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $divisiRole  = Role::firstOrCreate(['name' => 'divisi_it',     'guard_name' => 'web']);
        $ketuaRole   = Role::firstOrCreate(['name' => 'ketua_yayasan', 'guard_name' => 'web']);

        $this->divisiUser   = User::factory()->create(['location_id' => 'test-location']);
        $this->ketuaUser    = User::factory()->create(['location_id' => 'test-location']);
        $this->nonKetuaUser = User::factory()->create(['location_id' => 'test-location']);

        $this->divisiUser->assignRole($divisiRole);
        $this->ketuaUser->assignRole($ketuaRole);
        $this->nonKetuaUser->assignRole($divisiRole);
    }

    private function createPrWithStatus(PurchaseRequestStatus $status): \App\Models\PurchaseRequest
    {
        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Test Barang',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => 10000,
                ],
            ],
        ], $this->divisiUser);

        $pr->update(['status' => $status]);

        return $pr->refresh();
    }

    /**
     * Validates: Requirements 5.1
     */
    public function test_ketua_approve_transitions_to_approved(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::PENDING_KETUA);

        $result = $this->service->approve($pr, $this->ketuaUser, null);

        $this->assertEquals(PurchaseRequestStatus::APPROVED, $result->status);
    }

    /**
     * Validates: Requirements 5.2
     */
    public function test_ketua_reject_transitions_to_rejected_ketua(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::PENDING_KETUA);

        $result = $this->service->reject($pr, $this->ketuaUser, 'Perlu revisi spesifikasi barang');

        $this->assertEquals(PurchaseRequestStatus::REJECTED_KETUA, $result->status);
    }

    /**
     * Validates: Requirements 5.5
     */
    public function test_non_ketua_approve_throws_403(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::PENDING_KETUA);

        $this->expectException(CustomException::class);

        try {
            $this->service->approve($pr, $this->nonKetuaUser, null);
        } catch (CustomException $e) {
            $this->assertEquals(403, $e->getCode());
            throw $e;
        }
    }

    /**
     * Validates: Requirements 5.6
     */
    public function test_approve_on_wrong_status_throws_422(): void
    {
        // Use DRAFT status — not PENDING_KETUA
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::DRAFT);

        $this->expectException(CustomException::class);

        try {
            $this->service->approve($pr, $this->ketuaUser, null);
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }
}
