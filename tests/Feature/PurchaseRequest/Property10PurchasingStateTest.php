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
 * Property 10: Purchasing State Transitions
 *
 * For any PurchaseRequest berstatus APPROVED:
 * - memulai proses pembelian oleh divisi_sarpras → status ke PURCHASING
 * - menandai sebagian item → status ke PARTIALLY_PURCHASED
 * - menandai semua item → status ke COMPLETED
 * - Memulai pembelian pada status selain APPROVED → CustomException HTTP 422
 *
 * Validates: Requirements 6.1, 6.5, 6.6, 6.8, 6.9
 */
class Property10PurchasingStateTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $divisiUser;
    private User $sarprasUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $divisiRole  = Role::firstOrCreate(['name' => 'divisi_it', 'guard_name' => 'web']);
        $sarprasRole = Role::firstOrCreate(['name' => 'divisi_sarpras', 'guard_name' => 'web']);

        $this->divisiUser  = User::factory()->create(['location_id' => 'test-location']);
        $this->sarprasUser = User::factory()->create(['location_id' => 'test-location']);

        $this->divisiUser->assignRole($divisiRole);
        $this->sarprasUser->assignRole($sarprasRole);
    }

    private function createApprovedPr(int $itemCount = 2): \App\Models\PurchaseRequest
    {
        $items = [];
        for ($i = 1; $i <= $itemCount; $i++) {
            $items[] = [
                'nama_barang'    => "Barang {$i}",
                'satuan'         => 'pcs',
                'jumlah'         => 1,
                'biaya_estimasi' => 10000 * $i,
            ];
        }

        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Pengajuan Purchasing',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $items,
        ], $this->divisiUser);

        $pr->update(['status' => PurchaseRequestStatus::APPROVED]);
        $pr->refresh();

        return $pr;
    }

    /**
     * Validates: Requirements 6.1
     */
    public function test_start_purchasing_transitions_to_purchasing(): void
    {
        $pr = $this->createApprovedPr();

        $result = $this->service->startPurchasing($pr, $this->sarprasUser);

        $this->assertEquals(PurchaseRequestStatus::PURCHASING, $result->status);
    }

    /**
     * Validates: Requirements 6.5, 6.6
     */
    public function test_mark_partial_items_transitions_to_partially_purchased(): void
    {
        $pr = $this->createApprovedPr(2);
        $this->service->startPurchasing($pr, $this->sarprasUser);
        $pr->refresh();

        $firstItem = $pr->items()->first();

        $result = $this->service->markItemPurchased($pr, $firstItem, ['harga_aktual' => 15000], $this->sarprasUser);

        $this->assertEquals(PurchaseRequestStatus::PARTIALLY_PURCHASED, $result->status);
    }

    /**
     * Validates: Requirements 6.8
     */
    public function test_mark_all_items_transitions_to_completed(): void
    {
        $pr = $this->createApprovedPr(2);
        $this->service->startPurchasing($pr, $this->sarprasUser);
        $pr->refresh();

        $items = $pr->items()->get();

        // Mark first item
        $pr = $this->service->markItemPurchased($pr, $items[0], ['harga_aktual' => 15000], $this->sarprasUser);
        $this->assertEquals(PurchaseRequestStatus::PARTIALLY_PURCHASED, $pr->status);

        // Mark second (last) item
        $pr = $this->service->markItemPurchased($pr, $items[1], ['harga_aktual' => 20000], $this->sarprasUser);
        $this->assertEquals(PurchaseRequestStatus::COMPLETED, $pr->status);
    }

    /**
     * Validates: Requirements 6.9
     */
    public function test_start_purchasing_on_wrong_status_throws_422(): void
    {
        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Pengajuan Draft',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Barang Test',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => 10000,
                ],
            ],
        ], $this->divisiUser);

        // PR is still DRAFT — should throw 422
        $this->expectException(CustomException::class);

        try {
            $this->service->startPurchasing($pr, $this->sarprasUser);
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }
}
