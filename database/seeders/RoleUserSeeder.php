<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoleUserSeeder extends Seeder
{
    /**
     * Roles dan user yang akan di-seed.
     * Setiap role mendapat permission RAB + Pengajuan Pembelian Barang.
     */
    private array $roles = [
        'ketua_yayasan'        => 'Ketua Yayasan',
        'bendahara_umum'       => 'Bendahara Umum',
        'divisi_dakwah'        => 'Divisi Dakwah',
        'divisi_humas_publikasi' => 'Divisi Humas & Publikasi',
        'divisi_sarpras'       => 'Divisi Sarpras',
        'divisi_sosial'        => 'Divisi Sosial',
        'divisi_sub_janaiz'    => 'Divisi Sub Janaiz',
    ];

    private array $rabPermissions = [
        'rab-read',
        'rab-create',
        'rab-update',
        'rab-cancel',
        'rab-approve',
    ];

    private array $purchaseRequestPermissions = [
        'purchase-request-read',
        'purchase-request-create',
        'purchase-request-update',
        'purchase-request-cancel',
        'purchase-request-approve',
    ];

    public function run(): void
    {
        $location = Location::firstOrCreate(
            ['name' => 'B1/10'],
            ['name' => 'B1/10', 'phone' => 'XXXXXXX', 'address' => 'XXXXXXX']
        );

        $allowedPermissions = array_merge($this->rabPermissions, $this->purchaseRequestPermissions);

        foreach ($this->roles as $roleName => $displayName) {
            // Buat atau ambil role
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web']
            );

            // Sync permissions: hanya RAB + Pengajuan Pembelian
            $permissions = Permission::whereIn('name', $allowedPermissions)->get();
            $role->syncPermissions($permissions);

            // Buat user untuk role ini (1 user per role)
            $email = $roleName . '@ymds.test';
            $user  = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'              => $displayName,
                    'email'             => $email,
                    'email_verified_at' => now(),
                    'password'          => Hash::make('password'),
                    'remember_token'    => Str::random(10),
                    'location_id'       => $location->id,
                    'role_id'           => $role->id,
                ]
            );

            // Update role_id jika user sudah ada sebelumnya
            $user->update(['role_id' => $role->id]);

            $user->syncRoles([$role]);

            $this->command->info("✓ Role [{$roleName}] + User [{$email}] selesai.");
        }
    }
}
