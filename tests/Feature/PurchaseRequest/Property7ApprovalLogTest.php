<?php

namespace Tests\Feature\PurchaseRequest;

use App\Enums\PurchaseRequestAksiLog;
use App\Enums\PurchaseRequestStatus;
use App\Models\PurchaseRequestLog;
use App\Models\User;
use App\Services\PurchaseRequestService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 7: Approval Log Invariant
 *
 * For any aksi yang mengubah status PurchaseRequest (submit, approve, reject, cancel),
 * sistem harus selalu mencatat satu entri `purchase_request_logs` dengan `aksi` yang sesuai,
 * `dilakukan_oleh` yang benar, dan `created_at` yang valid.
 * Untuk aksi reject, field `catatan` harus terisi.
 *
 * Validates: Requirements 3.3, 4.3, 4.4, 5.3, 5.4, 6.2, 6.4, 7.3
 */
class Property7ApprovalLogTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestService $service;
    private User $divisiUser;
    private User $bendaharaUser;
    private User $ketuaUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PurchaseRequestService();

        $divisiRole    = Role::firstOrCreate(['name' => 'divisi_it', 'guard_name' => 'web']);
        $bendaharaRole = Role::firstOrCreate(['name' => 'bendahara_umum', 'guard_name' => 'web']);
        $ketuaRole     = Role::firstOrCreate(['name' => 'ketua_yayasan', 'guard_name' => 'web']);

        $this->divisiUser    = User::factory()->create(['location_id' => 'test-location']);
        $this->bendaharaUser = User::factory()->create(['location_id' => 'test-location']);
        $this->ketuaUser     = User::factory()->create(['location_id' => 'test-location']);

        $this->divisiUser->assignRole($divisiRole);
        $this->bendaharaUser->assignRole($bendaharaRole);
        $this->ketuaUser->assignRole($ketuaRole);
    }

    private function createDraftPr(): \App\Models\PurchaseRequest
    {
        return $this->service->store([
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
    }

    /**
     * Validates: Requirements 3.3
     */
    public function test_submit_creates_log_with_submitted_action(): void
    {
        $pr = $this->createDraftPr();

        $this->service->submit($pr, $this->divisiUser);

        $log = PurchaseRequestLog::where('purchase_request_id', $pr->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(PurchaseRequestAksiLog::SUBMITTED, $log->aksi);
        $this->assertEquals($this->divisiUser->id, $log->dilakukan_oleh);
        $this->assertNotNull($log->created_at);
    }

    /**
     * Validates: Requirements 7.3
     */
    public function test_cancel_creates_log_with_cancelled_action(): void
    {
        $pr = $this->createDraftPr();

        $this->service->cancel($pr, $this->divisiUser);

        $log = PurchaseRequestLog::where('purchase_request_id', $pr->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(PurchaseRequestAksiLog::CANCELLED, $log->aksi);
        $this->assertEquals($this->divisiUser->id, $log->dilakukan_oleh);
        $this->assertNotNull($log->created_at);
    }

    /**
     * Validates: Requirements 4.3
     */
    public function test_bendahara_approve_creates_log_with_approved_bendahara_action(): void
    {
        $pr = $this->createDraftPr();
        $pr->update(['status' => PurchaseRequestStatus::PENDING_BENDAHARA]);
        $pr->refresh();

        $this->service->approve($pr, $this->bendaharaUser, null);

        $log = PurchaseRequestLog::where('purchase_request_id', $pr->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(PurchaseRequestAksiLog::APPROVED_BENDAHARA, $log->aksi);
        $this->assertEquals($this->bendaharaUser->id, $log->dilakukan_oleh);
        $this->assertNotNull($log->created_at);
    }

    /**
     * Validates: Requirements 4.4
     */
    public function test_bendahara_reject_creates_log_with_catatan(): void
    {
        $pr = $this->createDraftPr();
        $pr->update(['status' => PurchaseRequestStatus::PENDING_BENDAHARA]);
        $pr->refresh();

        $catatan = 'Anggaran tidak mencukupi';
        $this->service->reject($pr, $this->bendaharaUser, $catatan);

        $log = PurchaseRequestLog::where('purchase_request_id', $pr->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(PurchaseRequestAksiLog::REJECTED_BENDAHARA, $log->aksi);
        $this->assertEquals($this->bendaharaUser->id, $log->dilakukan_oleh);
        $this->assertNotNull($log->created_at);
        $this->assertNotEmpty($log->catatan);
        $this->assertEquals($catatan, $log->catatan);
    }

    /**
     * Validates: Requirements 5.3
     */
    public function test_ketua_approve_creates_log_with_approved_ketua_action(): void
    {
        $pr = $this->createDraftPr();
        $pr->update(['status' => PurchaseRequestStatus::PENDING_KETUA]);
        $pr->refresh();

        $this->service->approve($pr, $this->ketuaUser, null);

        $log = PurchaseRequestLog::where('purchase_request_id', $pr->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(PurchaseRequestAksiLog::APPROVED_KETUA, $log->aksi);
        $this->assertEquals($this->ketuaUser->id, $log->dilakukan_oleh);
        $this->assertNotNull($log->created_at);
    }

    /**
     * Validates: Requirements 5.4
     */
    public function test_ketua_reject_creates_log_with_catatan(): void
    {
        $pr = $this->createDraftPr();
        $pr->update(['status' => PurchaseRequestStatus::PENDING_KETUA]);
        $pr->refresh();

        $catatan = 'Perlu revisi spesifikasi barang';
        $this->service->reject($pr, $this->ketuaUser, $catatan);

        $log = PurchaseRequestLog::where('purchase_request_id', $pr->id)->latest()->first();

        $this->assertNotNull($log);
        $this->assertEquals(PurchaseRequestAksiLog::REJECTED_KETUA, $log->aksi);
        $this->assertEquals($this->ketuaUser->id, $log->dilakukan_oleh);
        $this->assertNotNull($log->created_at);
        $this->assertNotEmpty($log->catatan);
        $this->assertEquals($catatan, $log->catatan);
    }
}
