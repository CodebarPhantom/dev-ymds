<?php

namespace Tests\Feature\PurchaseRequest;

use App\Http\Requests\PurchaseRequest\StorePurchaseRequestRequest;
use App\Http\Requests\PurchaseRequest\UpdatePurchaseRequestRequest;
use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 3: Validasi Input Item
 *
 * For any request pembuatan atau update PurchaseRequest_Item dengan
 * `biaya_estimasi` bernilai negatif atau non-numerik, atau `jumlah` bernilai
 * bukan bilangan bulat positif, sistem harus menolak request tersebut dengan
 * ValidationException (HTTP 422).
 *
 * Validates: Requirements 1.8, 1.9, 6.10
 */
class Property3ValidationTest extends TestCase
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

    private function validItemData(array $overrides = []): array
    {
        return array_merge([
            'nama_barang'    => 'Test Barang',
            'satuan'         => 'pcs',
            'jumlah'         => 1,
            'biaya_estimasi' => 10000,
        ], $overrides);
    }

    private function validPrData(array $itemOverrides = []): array
    {
        return [
            'judul_pengajuan'    => 'Test Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [$this->validItemData($itemOverrides)],
        ];
    }

    private function validateStoreRequest(array $data): array
    {
        $request = new StorePurchaseRequestRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());
        return $validator->errors()->toArray();
    }

    private function validateUpdateRequest(array $data): array
    {
        $request = new UpdatePurchaseRequestRequest();
        $validator = Validator::make($data, $request->rules(), $request->messages());
        return $validator->errors()->toArray();
    }

    /**
     * Property 3: biaya_estimasi negatif harus ditolak oleh Form Request.
     *
     * Validates: Requirements 1.8, 1.9
     */
    public function test_negative_biaya_estimasi_throws_validation_exception(): void
    {
        $data = $this->validPrData(['biaya_estimasi' => -1]);

        $storeErrors = $this->validateStoreRequest($data);
        $updateErrors = $this->validateUpdateRequest($data);

        $this->assertArrayHasKey('items.0.biaya_estimasi', $storeErrors,
            'StorePurchaseRequestRequest harus menolak biaya_estimasi negatif');
        $this->assertArrayHasKey('items.0.biaya_estimasi', $updateErrors,
            'UpdatePurchaseRequestRequest harus menolak biaya_estimasi negatif');
    }

    /**
     * Property 3: jumlah = 0 harus ditolak (min:1).
     *
     * Validates: Requirements 1.8, 1.9
     */
    public function test_zero_jumlah_throws_validation_exception(): void
    {
        $data = $this->validPrData(['jumlah' => 0]);

        $storeErrors = $this->validateStoreRequest($data);
        $updateErrors = $this->validateUpdateRequest($data);

        $this->assertArrayHasKey('items.0.jumlah', $storeErrors,
            'StorePurchaseRequestRequest harus menolak jumlah = 0');
        $this->assertArrayHasKey('items.0.jumlah', $updateErrors,
            'UpdatePurchaseRequestRequest harus menolak jumlah = 0');
    }

    /**
     * Property 3: jumlah negatif harus ditolak.
     *
     * Validates: Requirements 1.8, 1.9
     */
    public function test_negative_jumlah_throws_validation_exception(): void
    {
        $data = $this->validPrData(['jumlah' => -1]);

        $storeErrors = $this->validateStoreRequest($data);
        $updateErrors = $this->validateUpdateRequest($data);

        $this->assertArrayHasKey('items.0.jumlah', $storeErrors,
            'StorePurchaseRequestRequest harus menolak jumlah negatif');
        $this->assertArrayHasKey('items.0.jumlah', $updateErrors,
            'UpdatePurchaseRequestRequest harus menolak jumlah negatif');
    }

    /**
     * Property 3: harga_aktual negatif pada markItemPurchased harus melempar ValidationException.
     *
     * Validates: Requirements 6.10
     */
    public function test_negative_harga_aktual_throws_validation_exception(): void
    {
        // Create a PR in PURCHASING status
        $pr = PurchaseRequest::create([
            'nomor_pengajuan'    => 'PB/202501/001',
            'divisi_id'          => 'divisi_it',
            'judul_pengajuan'    => 'Test',
            'tanggal_dibutuhkan' => '2025-12-31',
            'status'             => PurchaseRequestStatus::PURCHASING,
            'dibuat_oleh'        => $this->user->id,
        ]);

        $item = PurchaseRequestItem::create([
            'purchase_request_id' => $pr->id,
            'nama_barang'         => 'Test Barang',
            'satuan'              => 'pcs',
            'jumlah'              => 1,
            'biaya_estimasi'      => 10000,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->markItemPurchased($pr, $item, ['harga_aktual' => -1], $this->user);
    }

    /**
     * Property 3: biaya_estimasi = 0 harus diterima (min:0).
     *
     * Validates: Requirements 1.8
     */
    public function test_valid_zero_biaya_estimasi_is_accepted(): void
    {
        $data = $this->validPrData(['biaya_estimasi' => 0]);

        $storeErrors = $this->validateStoreRequest($data);
        $updateErrors = $this->validateUpdateRequest($data);

        $this->assertArrayNotHasKey('items.0.biaya_estimasi', $storeErrors,
            'StorePurchaseRequestRequest harus menerima biaya_estimasi = 0');
        $this->assertArrayNotHasKey('items.0.biaya_estimasi', $updateErrors,
            'UpdatePurchaseRequestRequest harus menerima biaya_estimasi = 0');

        // Also verify the service accepts it
        $pr = $this->service->store($data, $this->user);
        $this->assertEquals(0, $pr->items()->first()->biaya_estimasi);
    }
}
