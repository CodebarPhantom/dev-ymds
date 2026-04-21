<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 11: Total Biaya Aktual Invariant
 *
 * For any PurchaseRequest setelah operasi markItemPurchased, nilai total_biaya_aktual
 * harus selalu sama dengan SUM(harga_aktual) dari seluruh PurchaseRequest_Item
 * yang memiliki sudah_dibeli = true.
 *
 * Validates: Requirements 6.7
 */
class Property11TotalBiayaAktualTest extends TestCase
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

    private function createPurchasingPr(array $itemBiaya): \App\Models\PurchaseRequest
    {
        $items = [];
        foreach ($itemBiaya as $i => $biaya) {
            $items[] = [
                'nama_barang'    => 'Barang ' . ($i + 1),
                'satuan'         => 'pcs',
                'jumlah'         => 1,
                'biaya_estimasi' => $biaya,
            ];
        }

        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Biaya Aktual',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $items,
        ], $this->divisiUser);

        $pr->update(['status' => PurchaseRequestStatus::APPROVED]);
        $pr->refresh();

        $pr = $this->service->startPurchasing($pr, $this->sarprasUser);

        return $pr;
    }

    /**
     * Validates: Requirements 6.7
     */
    public function test_total_biaya_aktual_after_first_item_purchased(): void
    {
        $pr    = $this->createPurchasingPr([10000, 20000]);
        $item  = $pr->items()->first();
        $harga = 15000;

        $pr = $this->service->markItemPurchased($pr, $item, ['harga_aktual' => $harga], $this->sarprasUser);

        $expectedTotal = $pr->items()
            ->where('sudah_dibeli', true)
            ->sum('harga_aktual');

        $this->assertEquals($expectedTotal, $pr->total_biaya_aktual);
        $this->assertEquals($harga, $pr->total_biaya_aktual);
    }

    /**
     * Validates: Requirements 6.7
     */
    public function test_total_biaya_aktual_after_multiple_items_purchased(): void
    {
        $pr    = $this->createPurchasingPr([10000, 20000, 30000]);
        $items = $pr->items()->get();

        $harga1 = 12000;
        $harga2 = 25000;

        $pr = $this->service->markItemPurchased($pr, $items[0], ['harga_aktual' => $harga1], $this->sarprasUser);
        $pr = $this->service->markItemPurchased($pr, $items[1], ['harga_aktual' => $harga2], $this->sarprasUser);

        $expectedTotal = $pr->items()
            ->where('sudah_dibeli', true)
            ->sum('harga_aktual');

        $this->assertEquals($expectedTotal, $pr->total_biaya_aktual);
        $this->assertEquals($harga1 + $harga2, $pr->total_biaya_aktual);
    }

    /**
     * Validates: Requirements 6.7
     */
    public function test_total_biaya_aktual_equals_sum_of_harga_aktual_for_purchased_items(): void
    {
        $hargaAktual = [11000, 22000, 33000];
        $pr          = $this->createPurchasingPr([10000, 20000, 30000]);
        $items       = $pr->items()->get();

        foreach ($items as $index => $item) {
            $pr = $this->service->markItemPurchased(
                $pr,
                $item,
                ['harga_aktual' => $hargaAktual[$index]],
                $this->sarprasUser
            );

            // After each mark, invariant must hold
            $expectedTotal = $pr->items()
                ->where('sudah_dibeli', true)
                ->sum('harga_aktual');

            $this->assertEquals(
                $expectedTotal,
                $pr->total_biaya_aktual,
                "Invariant violated after marking item {$index}: total_biaya_aktual ({$pr->total_biaya_aktual}) != SUM(harga_aktual) ({$expectedTotal})"
            );
        }

        // Final check: all items purchased, total equals sum of all harga_aktual
        $this->assertEquals(array_sum($hargaAktual), $pr->total_biaya_aktual);
    }
}
