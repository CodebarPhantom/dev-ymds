<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 5: Edit Permission Berdasarkan Status
 *
 * For any PurchaseRequest, operasi update hanya boleh berhasil jika status
 * PurchaseRequest adalah `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`.
 * Untuk status lainnya, isEditable() harus mengembalikan false.
 *
 * Validates: Requirements 2.4, 2.5
 */
class Property5EditPermissionTest extends TestCase
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

    private function createPrWithStatus(PurchaseRequestStatus $status): PurchaseRequest
    {
        static $seq = 0;
        $seq++;

        $pr = PurchaseRequest::create([
            'nomor_pengajuan'    => 'PB/202501/' . str_pad($seq, 3, '0', STR_PAD_LEFT),
            'divisi_id'          => 'divisi_it',
            'judul_pengajuan'    => 'Test Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'status'             => $status,
            'dibuat_oleh'        => $this->user->id,
        ]);

        $pr->items()->create([
            'nama_barang'    => 'Test Barang',
            'satuan'         => 'pcs',
            'jumlah'         => 1,
            'biaya_estimasi' => 10000,
        ]);

        return $pr;
    }

    private function updateData(): array
    {
        return [
            'judul_pengajuan'    => 'Updated Pengajuan',
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Updated Barang',
                    'satuan'         => 'pcs',
                    'jumlah'         => 2,
                    'biaya_estimasi' => 20000,
                ],
            ],
        ];
    }

    /**
     * Property 5: update berhasil ketika status DRAFT.
     *
     * Validates: Requirements 2.4
     */
    public function test_update_succeeds_for_draft_status(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::DRAFT);

        $this->assertTrue($pr->isEditable(), 'DRAFT harus isEditable() = true');

        $updated = $this->service->update($pr, $this->updateData());

        $this->assertEquals('Updated Pengajuan', $updated->judul_pengajuan);
        $this->assertEquals(PurchaseRequestStatus::DRAFT, $updated->status);
    }

    /**
     * Property 5: update berhasil ketika status REJECTED_BENDAHARA.
     *
     * Validates: Requirements 2.4
     */
    public function test_update_succeeds_for_rejected_bendahara_status(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::REJECTED_BENDAHARA);

        $this->assertTrue($pr->isEditable(), 'REJECTED_BENDAHARA harus isEditable() = true');

        $updated = $this->service->update($pr, $this->updateData());

        $this->assertEquals('Updated Pengajuan', $updated->judul_pengajuan);
        $this->assertEquals(PurchaseRequestStatus::REJECTED_BENDAHARA, $updated->status);
    }

    /**
     * Property 5: update berhasil ketika status REJECTED_KETUA.
     *
     * Validates: Requirements 2.4
     */
    public function test_update_succeeds_for_rejected_ketua_status(): void
    {
        $pr = $this->createPrWithStatus(PurchaseRequestStatus::REJECTED_KETUA);

        $this->assertTrue($pr->isEditable(), 'REJECTED_KETUA harus isEditable() = true');

        $updated = $this->service->update($pr, $this->updateData());

        $this->assertEquals('Updated Pengajuan', $updated->judul_pengajuan);
        $this->assertEquals(PurchaseRequestStatus::REJECTED_KETUA, $updated->status);
    }

    /**
     * Property 5: isEditable() mengembalikan false untuk semua status non-editable.
     *
     * Validates: Requirements 2.5
     */
    public function test_is_editable_returns_false_for_non_editable_statuses(): void
    {
        $nonEditableStatuses = [
            PurchaseRequestStatus::PENDING_BENDAHARA,
            PurchaseRequestStatus::PENDING_KETUA,
            PurchaseRequestStatus::APPROVED,
            PurchaseRequestStatus::CANCELLED,
            PurchaseRequestStatus::PURCHASING,
            PurchaseRequestStatus::PARTIALLY_PURCHASED,
            PurchaseRequestStatus::COMPLETED,
        ];

        foreach ($nonEditableStatuses as $status) {
            $pr = $this->createPrWithStatus($status);

            $this->assertFalse(
                $pr->isEditable(),
                "Status {$status->value} harus isEditable() = false"
            );
        }
    }
}
