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
 * Property 13: CANCELLED adalah Terminal State
 *
 * For any PurchaseRequest berstatus CANCELLED, tidak ada aksi apapun
 * (submit, approve, reject, cancel) yang boleh mengubah statusnya.
 * Semua aksi tersebut harus mengembalikan CustomException HTTP 422.
 *
 * Validates: Requirements 7.4
 */
class Property13CancelledTerminalTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $divisiUser;
    private User $bendaharaUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $divisiRole    = Role::firstOrCreate(['name' => 'divisi_it',     'guard_name' => 'web']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara_umum', 'guard_name' => 'web']);

        $this->divisiUser    = User::factory()->create(['location_id' => 'test-location']);
        $this->bendaharaUser = User::factory()->create(['location_id' => 'test-location']);

        $this->divisiUser->assignRole($divisiRole);
        $this->bendaharaUser->assignRole($bendaharaRole);
    }

    private function createCancelledPr(): \App\Models\PurchaseRequest
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

        $pr->update(['status' => PurchaseRequestStatus::CANCELLED]);

        return $pr->refresh();
    }

    /**
     * Validates: Requirements 7.4
     */
    public function test_submit_on_cancelled_throws_422(): void
    {
        $pr = $this->createCancelledPr();

        $this->expectException(CustomException::class);

        try {
            $this->service->submit($pr, $this->divisiUser);
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }

    /**
     * Validates: Requirements 7.4
     */
    public function test_cancel_on_cancelled_throws_422(): void
    {
        $pr = $this->createCancelledPr();

        $this->expectException(CustomException::class);

        try {
            $this->service->cancel($pr, $this->divisiUser);
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }

    /**
     * Validates: Requirements 7.4
     */
    public function test_approve_on_cancelled_throws_422(): void
    {
        $pr = $this->createCancelledPr();

        $this->expectException(CustomException::class);

        try {
            $this->service->approve($pr, $this->bendaharaUser, null);
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }

    /**
     * Validates: Requirements 7.4
     */
    public function test_reject_on_cancelled_throws_422(): void
    {
        $pr = $this->createCancelledPr();

        $this->expectException(CustomException::class);

        try {
            $this->service->reject($pr, $this->bendaharaUser, 'Alasan penolakan');
        } catch (CustomException $e) {
            $this->assertEquals(422, $e->getCode());
            throw $e;
        }
    }
}
