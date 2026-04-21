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
 * Property 12: Cancel State Transition dan Otorisasi
 *
 * For any PurchaseRequest dengan status selain APPROVED, CANCELLED, dan COMPLETED:
 * - jika Divisi_Pengaju melakukan cancel → status berubah ke CANCELLED
 * - User yang bukan Divisi_Pengaju → CustomException HTTP 403 (tested via policy)
 * - Cancel pada status APPROVED, CANCELLED, atau COMPLETED → CustomException HTTP 422
 *
 * Validates: Requirements 7.1, 7.2, 7.4, 7.5, 7.6
 */
class Property12CancelStateTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $divisiUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $divisiRole = Role::firstOrCreate(['name' => 'divisi_it', 'guard_name' => 'web']);

        $this->divisiUser = User::factory()->create(['location_id' => 'test-location']);
        $this->divisiUser->assignRole($divisiRole);
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
     * Validates: Requirements 7.1, 7.2
     *
     * All cancellable statuses: DRAFT, PENDING_BENDAHARA, REJECTED_BENDAHARA,
     * PENDING_KETUA, REJECTED_KETUA, PURCHASING, PARTIALLY_PURCHASED
     */
    public function test_cancel_from_cancellable_statuses_transitions_to_cancelled(): void
    {
        $cancellableStatuses = [
            PurchaseRequestStatus::DRAFT,
            PurchaseRequestStatus::PENDING_BENDAHARA,
            PurchaseRequestStatus::REJECTED_BENDAHARA,
            PurchaseRequestStatus::PENDING_KETUA,
            PurchaseRequestStatus::REJECTED_KETUA,
            PurchaseRequestStatus::PURCHASING,
            PurchaseRequestStatus::PARTIALLY_PURCHASED,
        ];

        foreach ($cancellableStatuses as $status) {
            $pr = $this->createPrWithStatus($status);

            $result = $this->service->cancel($pr, $this->divisiUser);

            $this->assertEquals(
                PurchaseRequestStatus::CANCELLED,
                $result->status,
                "Expected CANCELLED after cancel from status {$status->value}"
            );
        }
    }

    /**
     * Validates: Requirements 7.4, 7.5, 7.6
     *
     * Non-cancellable statuses: APPROVED, CANCELLED, COMPLETED
     */
    public function test_cancel_from_non_cancellable_statuses_throws_422(): void
    {
        $nonCancellableStatuses = [
            PurchaseRequestStatus::APPROVED,
            PurchaseRequestStatus::CANCELLED,
            PurchaseRequestStatus::COMPLETED,
        ];

        foreach ($nonCancellableStatuses as $status) {
            $pr = $this->createPrWithStatus($status);

            try {
                $this->service->cancel($pr, $this->divisiUser);
                $this->fail("Expected CustomException for status {$status->value} but none was thrown");
            } catch (CustomException $e) {
                $this->assertEquals(
                    422,
                    $e->getCode(),
                    "Expected HTTP 422 for cancel on status {$status->value}"
                );
            }
        }
    }
}
