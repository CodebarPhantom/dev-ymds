<?php

namespace Tests\Feature\Rab;

use App\Models\User;
use Eris\Generators;
use Eris\TestTrait;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature: rab-pencatatan, Property 3: Validasi Biaya Anggaran
 *
 * Validates: Requirements 1.8
 *
 * For any request pembuatan atau update RAB_Item dengan biaya_anggaran bernilai
 * negatif atau non-numerik, sistem harus menolak request tersebut dengan
 * response validasi error (HTTP 422).
 */
class BiayaAnggaranValidationPropertyTest extends TestCase
{
    use RefreshDatabase, TestTrait;

    private User $divisiUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create role and permissions for divisi user
        $role = Role::create(['name' => 'divisi_it', 'guard_name' => 'web']);
        $createPermission = Permission::create(['name' => 'rab-create', 'guard_name' => 'web']);
        $updatePermission = Permission::create(['name' => 'rab-update', 'guard_name' => 'web']);
        $readPermission   = Permission::create(['name' => 'rab-read', 'guard_name' => 'web']);

        $role->givePermissionTo([$createPermission, $updatePermission, $readPermission]);

        $this->divisiUser = User::factory()->create(['location_id' => '1']);
        $this->divisiUser->assignRole($role);
    }

    /**
     * **Validates: Requirements 1.8**
     *
     * Property 3: Negative biaya_anggaran values must be rejected with HTTP 422.
     */
    public function testNegativeBiayaAnggaranIsRejectedOnStore(): void
    {
        // Feature: rab-pencatatan, Property 3: Validasi Biaya Anggaran
        $this->forAll(
            Generators::neg() // strictly negative integers
        )->then(function (int $negativeBiaya) {
            $response = $this->actingAs($this->divisiUser)->post(route('rab.store'), [
                'bulan_pengajuan' => '2025-01-01',
                'items' => [
                    [
                        'kegiatan'          => 'Kegiatan Test',
                        'catatan_kegiatan'  => null,
                        'biaya_anggaran'    => $negativeBiaya,
                        'waktu_pelaksanaan' => 'Januari 2025',
                    ],
                ],
            ]);

            $response->assertStatus(302); // redirect back with errors (web form)
            $response->assertSessionHasErrors(['items.0.biaya_anggaran']);
        });
    }

    /**
     * **Validates: Requirements 1.8**
     *
     * Property 3: Non-numeric biaya_anggaran values must be rejected with HTTP 422.
     */
    public function testNonNumericBiayaAnggaranIsRejectedOnStore(): void
    {
        // Feature: rab-pencatatan, Property 3: Validasi Biaya Anggaran
        $nonNumericValues = ['abc', 'satu juta', '-', '--', 'NaN', 'null', '1e999x', '1,000'];

        $this->forAll(
            Generators::elements($nonNumericValues)
        )->then(function (string $nonNumericBiaya) {
            $response = $this->actingAs($this->divisiUser)->post(route('rab.store'), [
                'bulan_pengajuan' => '2025-01-01',
                'items' => [
                    [
                        'kegiatan'          => 'Kegiatan Test',
                        'catatan_kegiatan'  => null,
                        'biaya_anggaran'    => $nonNumericBiaya,
                        'waktu_pelaksanaan' => 'Januari 2025',
                    ],
                ],
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors(['items.0.biaya_anggaran']);
        });
    }

    /**
     * **Validates: Requirements 1.8**
     *
     * Property 3: Negative biaya_anggaran values must be rejected on update too.
     */
    public function testNegativeBiayaAnggaranIsRejectedOnUpdate(): void
    {
        // Feature: rab-pencatatan, Property 3: Validasi Biaya Anggaran
        // Create a RAB in DRAFT status first
        $rab = \App\Models\Rab::create([
            'nomor_rab'           => 'RAB/202501/001',
            'divisi_id'           => 'divisi_it',
            'bulan_pengajuan'     => '2025-01-01',
            'status'              => \App\Enums\RabStatus::DRAFT,
            'total_kegiatan'      => 0,
            'total_biaya_anggaran'=> 0,
            'dibuat_oleh'         => $this->divisiUser->id,
        ]);

        $this->forAll(
            Generators::neg()
        )->then(function (int $negativeBiaya) use ($rab) {
            $response = $this->actingAs($this->divisiUser)->put(route('rab.update', $rab), [
                'bulan_pengajuan' => '2025-01-01',
                'items' => [
                    [
                        'kegiatan'          => 'Kegiatan Test',
                        'catatan_kegiatan'  => null,
                        'biaya_anggaran'    => $negativeBiaya,
                        'waktu_pelaksanaan' => 'Januari 2025',
                    ],
                ],
            ]);

            $response->assertStatus(302);
            $response->assertSessionHasErrors(['items.0.biaya_anggaran']);
        });
    }

    /**
     * **Validates: Requirements 1.8**
     *
     * Property 3: Valid (non-negative numeric) biaya_anggaran must pass validation.
     * This is the positive case — ensures the validator doesn't over-reject.
     */
    public function testValidBiayaAnggaranPassesValidation(): void
    {
        // Feature: rab-pencatatan, Property 3: Validasi Biaya Anggaran
        $this->forAll(
            Generators::nat() // natural numbers (0 and above)
        )->then(function (int $validBiaya) {
            $response = $this->actingAs($this->divisiUser)->post(route('rab.store'), [
                'bulan_pengajuan' => '2025-01-01',
                'items' => [
                    [
                        'kegiatan'          => 'Kegiatan Test',
                        'catatan_kegiatan'  => null,
                        'biaya_anggaran'    => $validBiaya,
                        'waktu_pelaksanaan' => 'Januari 2025',
                    ],
                ],
            ]);

            $response->assertSessionDoesntHaveErrors(['items.0.biaya_anggaran']);
        });
    }
}
