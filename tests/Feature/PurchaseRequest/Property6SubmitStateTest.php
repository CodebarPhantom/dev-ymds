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
 * Property 6: Submit State Transition
 *
 * For any PurchaseRequest dengan status DRAFT, REJECTED_BENDAHARA, atau REJECTED_KETUA
 * yang disubmit oleh Divisi_Pengaju, status harus berubah menjadi PENDING_BENDAHARA.
 * Untuk status lainnya, sistem harus mengembalikan HTTP 422 (throw CustomException with 422).
 *
 * Validates: Requirements 3.1, 3.2, 3.5
 */
class Property6SubmitStateTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $role = Role::firstOrCreate(['name' => 'divisi_it', 'guard_name' => 'web']);
        $this->user = User::factory()->create(['location_id' => 'test-location']);
        $this->user->assignRole($role);
    }

    private function createPr(PurchaseRequestStatus $status): \App\Models\PurchaseRequest
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
        ], $this->user);

        if ($status !== PurchaseRequestStatus::DRAFT) {
            $pr->update(['status' => $status]);
            $pr->refresh();
        }

        return $pr;
    }

    /**
     * Validates: Requirements 3.1, 3.2, 3.5
     */
    public function test_submit_from_draft_transitions_to_pending_bendahara(): void
    {
        $pr = $this->createPr(PurchaseRequestStatus::DRAFT);

        $result = $this->service->submit($pr, $this->user);

        $this->assertEquals(PurchaseRequestStatus::PENDING_BENDAHARA, $result->status);
    }

    /**
     * Validates: Requirements 3.1, 3.2, 3.5
     */
    public function test_submit_from_rejected_bendahara_transitions_to_pending_bendahara(): void
    {
        $pr = $this->createPr(PurchaseRequestStatus::REJECTED_BENDAHARA);

        $result = $this->service->submit($pr, $this->user);

        $this->assertEquals(PurchaseRequestStatus::PENDING_BENDAHARA, $result->status);
    }

    /**
     * Validates: Requirements 3.1, 3.2, 3.5
     */
    public function test_submit_from_rejected_ketua_transitions_to_pending_bendahara(): void
    {
        $pr = $this->createPr(PurchaseRequestStatus::REJECTED_KETUA);

        $result = $this->service->submit($pr, $this->user);

        $this->assertEquals(PurchaseRequestStatus::PENDING_BENDAHARA, $result->status);
    }

    /**
     * Validates: Requirements 3.1, 3.2, 3.5
     */
    public function test_submit_from_invalid_statuses_throws_422(): void
    {
        $invalidStatuses = [
            PurchaseRequestStatus::PENDING_BENDAHARA,
            PurchaseRequestStatus::PENDING_KETUA,
            PurchaseRequestStatus::APPROVED,
            PurchaseRequestStatus::CANCELLED,
            PurchaseRequestStatus::PURCHASING,
            PurchaseRequestStatus::PARTIALLY_PURCHASED,
            PurchaseRequestStatus::COMPLETED,
        ];

        foreach ($invalidStatuses as $status) {
            $pr = $this->createPr($status);

            try {
                $this->service->submit($pr, $this->user);
                $this->fail("Expected CustomException for status {$status->value} but none was thrown.");
            } catch (CustomException $e) {
                $this->assertEquals(422, $e->getCode(), "Expected HTTP 422 for status {$status->value}");
            }
        }
    }
}
