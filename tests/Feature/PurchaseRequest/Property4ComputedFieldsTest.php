<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 4: Computed Fields Invariant
 *
 * For any PurchaseRequest setelah operasi tambah, ubah, atau hapus PurchaseRequest_Item,
 * nilai `total_item` harus selalu sama dengan `COUNT(purchase_request_items)` dan
 * `total_biaya_estimasi` harus selalu sama dengan `SUM(biaya_estimasi * jumlah)`
 * dari seluruh item milik PurchaseRequest tersebut.
 *
 * Validates: Requirements 2.2, 2.3
 */
class Property4ComputedFieldsTest extends TestCase
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

    private function makeItems(array $itemDefs): array
    {
        return array_map(fn($def) => array_merge([
            'nama_barang'    => 'Barang Test',
            'satuan'         => 'pcs',
            'catatan_item'   => null,
        ], $def), $itemDefs);
    }

    private function assertComputedFields($pr, array $items): void
    {
        $expectedCount = count($items);
        $expectedTotal = array_sum(array_map(
            fn($item) => $item['biaya_estimasi'] * $item['jumlah'],
            $items
        ));

        $pr->refresh();

        $this->assertEquals(
            $expectedCount,
            $pr->total_item,
            "total_item harus sama dengan COUNT(items)"
        );

        $this->assertEquals(
            $expectedTotal,
            (float) $pr->total_biaya_estimasi,
            "total_biaya_estimasi harus sama dengan SUM(biaya_estimasi * jumlah)"
        );
    }

    /**
     * Property 4: Computed Fields Invariant — after store
     *
     * Setelah membuat PR dengan beberapa item, total_item dan total_biaya_estimasi
     * harus dihitung dengan benar.
     *
     * Validates: Requirements 2.2, 2.3
     */
    public function test_computed_fields_correct_after_store(): void
    {
        $items = $this->makeItems([
            ['jumlah' => 2, 'biaya_estimasi' => 50000],
            ['jumlah' => 3, 'biaya_estimasi' => 20000],
            ['jumlah' => 1, 'biaya_estimasi' => 150000],
        ]);

        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Store',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $items,
        ], $this->user);

        // total_item = 3, total_biaya_estimasi = (2*50000) + (3*20000) + (1*150000) = 310000
        $this->assertComputedFields($pr, $items);
    }

    /**
     * Property 4: Computed Fields Invariant — after update with more items
     *
     * Setelah update PR dengan lebih banyak item, computed fields harus dihitung ulang.
     *
     * Validates: Requirements 2.2, 2.3
     */
    public function test_computed_fields_correct_after_update_with_more_items(): void
    {
        $initialItems = $this->makeItems([
            ['jumlah' => 1, 'biaya_estimasi' => 100000],
        ]);

        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Update More',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $initialItems,
        ], $this->user);

        $updatedItems = $this->makeItems([
            ['jumlah' => 1, 'biaya_estimasi' => 100000],
            ['jumlah' => 5, 'biaya_estimasi' => 30000],
            ['jumlah' => 2, 'biaya_estimasi' => 75000],
            ['jumlah' => 10, 'biaya_estimasi' => 5000],
        ]);

        $pr = $this->service->update($pr, [
            'judul_pengajuan'    => 'Test Update More',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $updatedItems,
        ]);

        // total_item = 4, total_biaya_estimasi = 100000 + 150000 + 150000 + 50000 = 450000
        $this->assertComputedFields($pr, $updatedItems);
    }

    /**
     * Property 4: Computed Fields Invariant — after update with fewer items
     *
     * Setelah update PR dengan lebih sedikit item, computed fields harus dihitung ulang.
     *
     * Validates: Requirements 2.2, 2.3
     */
    public function test_computed_fields_correct_after_update_with_fewer_items(): void
    {
        $initialItems = $this->makeItems([
            ['jumlah' => 2, 'biaya_estimasi' => 50000],
            ['jumlah' => 3, 'biaya_estimasi' => 20000],
            ['jumlah' => 1, 'biaya_estimasi' => 150000],
            ['jumlah' => 4, 'biaya_estimasi' => 10000],
        ]);

        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Update Fewer',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $initialItems,
        ], $this->user);

        $updatedItems = $this->makeItems([
            ['jumlah' => 1, 'biaya_estimasi' => 200000],
        ]);

        $pr = $this->service->update($pr, [
            'judul_pengajuan'    => 'Test Update Fewer',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $updatedItems,
        ]);

        // total_item = 1, total_biaya_estimasi = 200000
        $this->assertComputedFields($pr, $updatedItems);
    }

    /**
     * Property 4: Computed Fields Invariant — various quantities and prices
     *
     * Menguji berbagai kombinasi jumlah dan biaya_estimasi untuk memastikan
     * perhitungan SUM(biaya_estimasi * jumlah) selalu benar.
     *
     * Validates: Requirements 2.2, 2.3
     */
    public function test_computed_fields_with_various_quantities_and_prices(): void
    {
        $combinations = [
            // [jumlah, biaya_estimasi]
            [1, 0],
            [100, 1],
            [1, 999999],
            [50, 25000],
            [7, 33333],
        ];

        $items = $this->makeItems(array_map(
            fn($c) => ['jumlah' => $c[0], 'biaya_estimasi' => $c[1]],
            $combinations
        ));

        $pr = $this->service->store([
            'judul_pengajuan'    => 'Test Various',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => $items,
        ], $this->user);

        $this->assertComputedFields($pr, $items);

        // Also verify the DB-level count matches
        $this->assertEquals(
            $pr->items()->count(),
            $pr->total_item,
            "total_item harus sama dengan jumlah baris di purchase_request_items"
        );
    }
}
