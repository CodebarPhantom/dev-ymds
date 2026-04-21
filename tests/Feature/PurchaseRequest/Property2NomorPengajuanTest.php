<?php

namespace Tests\Feature\PurchaseRequest;

use App\Models\User;
use App\Services\PurchaseRequestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Property 2: Nomor Pengajuan Format dan Keunikan
 *
 * For any kumpulan PurchaseRequest yang dibuat (termasuk dari bulan yang sama
 * maupun berbeda), setiap nomor_pengajuan harus:
 * (a) match pattern PB/\d{6}/\d{3}
 * (b) unik secara global
 * (c) sequence-nya berurutan mulai dari 001 per bulan
 * (d) sequence reset ke 001 saat bulan berganti
 *
 * Validates: Requirements 1.5, 11.1, 11.2, 11.3, 11.4
 */
class Property2NomorPengajuanTest extends TestCase
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

    protected function tearDown(): void
    {
        Carbon::setTestNow(null);
        parent::tearDown();
    }

    private function validPrData(string $judul = 'Test Pengajuan'): array
    {
        return [
            'judul_pengajuan'    => $judul,
            'tanggal_dibutuhkan' => '2025-12-31',
            'items'              => [
                [
                    'nama_barang'    => 'Test Barang',
                    'satuan'         => 'pcs',
                    'jumlah'         => 1,
                    'biaya_estimasi' => 10000,
                ],
            ],
        ];
    }

    /**
     * (a) Setiap nomor_pengajuan harus match pattern PB/\d{6}/\d{3}
     *
     * Validates: Requirements 1.5, 11.1
     */
    public function test_nomor_pengajuan_matches_format_pattern(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 15));

        $prs = [];
        for ($i = 1; $i <= 5; $i++) {
            $prs[] = $this->service->store($this->validPrData("Pengajuan {$i}"), $this->user);
        }

        foreach ($prs as $pr) {
            $this->assertMatchesRegularExpression(
                '/^PB\/\d{6}\/\d{3}$/',
                $pr->nomor_pengajuan,
                "nomor_pengajuan '{$pr->nomor_pengajuan}' harus match pattern PB/\\d{6}/\\d{3}"
            );
        }
    }

    /**
     * (b) Semua nomor_pengajuan harus unik secara global
     *
     * Validates: Requirements 11.2
     */
    public function test_nomor_pengajuan_is_globally_unique(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 6, 15));

        $nomorList = [];
        for ($i = 1; $i <= 10; $i++) {
            $pr = $this->service->store($this->validPrData("Pengajuan {$i}"), $this->user);
            $nomorList[] = $pr->nomor_pengajuan;
        }

        $unique = array_unique($nomorList);
        $this->assertCount(
            count($nomorList),
            $unique,
            'Semua nomor_pengajuan harus unik secara global'
        );
    }

    /**
     * (c) PR pertama di suatu bulan harus memiliki sequence 001
     *
     * Validates: Requirements 11.3
     */
    public function test_nomor_pengajuan_sequence_starts_at_001(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 8, 1));

        $pr = $this->service->store($this->validPrData(), $this->user);

        $this->assertStringEndsWith(
            '/001',
            $pr->nomor_pengajuan,
            "PR pertama di bulan baru harus memiliki sequence 001, dapat: {$pr->nomor_pengajuan}"
        );
    }

    /**
     * (c) Sequence harus berurutan: 001, 002, 003, dst.
     *
     * Validates: Requirements 11.3
     */
    public function test_nomor_pengajuan_sequence_increments(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 9, 1));

        $pr1 = $this->service->store($this->validPrData('Pengajuan 1'), $this->user);
        $pr2 = $this->service->store($this->validPrData('Pengajuan 2'), $this->user);
        $pr3 = $this->service->store($this->validPrData('Pengajuan 3'), $this->user);

        $this->assertStringEndsWith('/001', $pr1->nomor_pengajuan, "PR ke-1 harus sequence 001");
        $this->assertStringEndsWith('/002', $pr2->nomor_pengajuan, "PR ke-2 harus sequence 002");
        $this->assertStringEndsWith('/003', $pr3->nomor_pengajuan, "PR ke-3 harus sequence 003");
    }

    /**
     * (d) Sequence harus reset ke 001 saat bulan berganti
     *
     * Validates: Requirements 11.4
     */
    public function test_nomor_pengajuan_sequence_resets_per_month(): void
    {
        // Bulan pertama: buat 3 PR di bulan Oktober 2025
        Carbon::setTestNow(Carbon::create(2025, 10, 1));

        $prOkt1 = $this->service->store($this->validPrData('Oktober 1'), $this->user);
        $prOkt2 = $this->service->store($this->validPrData('Oktober 2'), $this->user);
        $prOkt3 = $this->service->store($this->validPrData('Oktober 3'), $this->user);

        $this->assertStringEndsWith('/001', $prOkt1->nomor_pengajuan);
        $this->assertStringEndsWith('/002', $prOkt2->nomor_pengajuan);
        $this->assertStringEndsWith('/003', $prOkt3->nomor_pengajuan);

        // Bulan berganti: November 2025 — sequence harus reset ke 001
        Carbon::setTestNow(Carbon::create(2025, 11, 1));

        $prNov1 = $this->service->store($this->validPrData('November 1'), $this->user);
        $prNov2 = $this->service->store($this->validPrData('November 2'), $this->user);

        $this->assertStringContainsString('202511', $prNov1->nomor_pengajuan, "Bulan pada nomor harus 202511");
        $this->assertStringEndsWith('/001', $prNov1->nomor_pengajuan, "PR pertama bulan baru harus reset ke 001");
        $this->assertStringEndsWith('/002', $prNov2->nomor_pengajuan, "PR kedua bulan baru harus sequence 002");

        // Pastikan nomor Oktober tidak berubah
        $this->assertStringContainsString('202510', $prOkt1->nomor_pengajuan);
        $this->assertStringContainsString('202510', $prOkt3->nomor_pengajuan);
    }
}
