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
 * Property 8: Bendahara Approval State Transition
 *
 * For any PurchaseRequest berstatus PENDING_BENDAHARA:
 * - jika di-approve oleh bendahara_umum → status berubah ke PENDING_KETUA
 * - jika di-reject → status berubah ke REJECTED_BENDAHARA
 * - Aksi oleh user non-bendahara_umum → CustomException HTTP 403
 * - Aksi pada status selain PENDING_BENDAHARA → CustomException HTTP 422
 *
 * Validates: Requirements 4.1, 4.2, 4.5, 4.6
 */
class Property8BendaharaApprovalTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $divisiUser;
    private User $bendaharaUser;
    private User $nonBendaharaUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $divisiRole      = Role::firstOrCreate(['name' => 'divisi_it',     'guard_name' => 'web']);
        $bendaharaRole   = Role::firstOrCreate(['name' => 'bendahara_umum', 'guard_name' => 'web']);

        $this->divisiUser      = User::factory()->create(['location_id' => 'test-location']);
        $this->bendaharaUser   = User::factory()->create(['location_id' => 'test-location']);
        $this->nonBendaharaUser = User::factory()->create(['location_id' => 'test-location']);

        $this->divisiUser->assignRole($divisiRole);
        $this->bendaharaUser->assignRole($bendaharaRole);
        $this->nonBendaharaUser->assignRole($divisiRole);
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
     * Validates: Requirements 4.1
     */
    public function test_bendahara_approve_transitions_to_pending_ketua(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::PENDING_BENDAHARA);

        $result = $this->service->approve($pr, $this->bendaharaUser, null);

        $this->assertEquals(PurchaseRequestStatus::PENDING_KETUA, $result->status);
    }

    /**
     * Validates: Requirements 4.2
     */
    public function test_bendahara_reject_transitions_to_rejected_bendahara(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::PENDING_BENDAHARA);

        $result = $this->service->reject($pr, $this->bendaharaUser, 'Anggaran tidak mencukupi');

        $this->assertEquals(PurchaseRequestStatus::REJECTED_BENDAHARA, $result->status);
    }

    /**
     * Validates: Requirements 4.5
     */
    public function test_non_bendahara_approve_throws_403(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::PENDING_BENDAHARA);

        $this->expectException(CustomException::class);

        try {
            $this->service->approve($pr, $this->nonBendaharaUser, null);
        } catch (CustomException $e) {
            $this->assertEquals(403, $e->getCode());
            throw $e;
        }
    }

    /**
     * Validates: Requirements 4.6
     */
    public function test_approve_on_wrong_status_throws_422(): void
    {
        // Use DRAFT status — not PENDING_BENDAHARA
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::DRAFT);

        $this->expectException(CustomException::class);

        try {
            $this->service->approve($pr, $this->bendaharaUser, null);
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }
}
