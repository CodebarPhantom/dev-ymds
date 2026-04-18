# Implementation Plan: RAB Pencatatan

## Overview

Implementasi modul RAB (Rencana Anggaran Biaya) mengikuti pola arsitektur yang sudah ada: `MasterController` + `callFunction()`, `MasterService`, Spatie Permission, dan Sanctum. Alur approval dua tahap: Bendahara Umum → Ketua Yayasan.

## Tasks

- [x] 1. Buat migrations database
  - [x] 1.1 Buat migration `create_rabs_table`
    - Kolom: `id`, `nomor_rab` (varchar 20, unique), `divisi_id` (varchar 100), `bulan_pengajuan` (date), `status` (varchar 30, default `DRAFT`), `total_kegiatan` (integer, default 0), `total_biaya_anggaran` (decimal 15,2, default 0), `dibuat_oleh` (FK → users.id), `created_at`, `updated_at`
    - Index: `divisi_id`, `status`, `bulan_pengajuan`, `nomor_rab`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 10.1_

  - [x] 1.2 Buat migration `create_rab_items_table`
    - Kolom: `id`, `rab_id` (FK → rabs.id, CASCADE DELETE), `kegiatan` (varchar 255), `catatan_kegiatan` (text, nullable), `biaya_anggaran` (decimal 15,2), `waktu_pelaksanaan` (varchar 100), `created_at`, `updated_at`
    - Index: `rab_id`
    - _Requirements: 2.1_

  - [x] 1.3 Buat migration `create_rab_approval_logs_table`
    - Kolom: `id`, `rab_id` (FK → rabs.id, CASCADE DELETE), `aksi` (varchar 30), `dilakukan_oleh` (FK → users.id), `catatan` (text, nullable), `created_at`, `updated_at`
    - Index: `rab_id`, `dilakukan_oleh`
    - _Requirements: 3.3, 4.3, 4.4, 5.3, 5.4, 6.3_

- [x] 2. Buat Enums
  - [x] 2.1 Buat `App\Enums\RabStatus`
    - Cases: `DRAFT`, `PENDING_BENDAHARA`, `REJECTED_BENDAHARA`, `PENDING_KETUA`, `REJECTED_KETUA`, `APPROVED`, `CANCELLED`
    - _Requirements: 1.4, 3.1, 3.2, 4.1, 4.2, 5.1, 5.2, 6.2_

  - [x] 2.2 Buat `App\Enums\RabAksiLog`
    - Cases: `SUBMITTED`, `APPROVED_BENDAHARA`, `REJECTED_BENDAHARA`, `APPROVED_KETUA`, `REJECTED_KETUA`, `CANCELLED`
    - _Requirements: 3.3, 4.3, 4.4, 5.3, 5.4, 6.3_

- [x] 3. Buat Models dan Relationships
  - [x] 3.1 Buat `App\Models\Rab`
    - `$fillable`: semua kolom kecuali `id`
    - Cast `status` ke `RabStatus`, `bulan_pengajuan` ke `date`
    - Relations: `hasMany RabItem`, `hasMany RabApprovalLog`, `belongsTo User` (dibuat_oleh)
    - Accessors: `getStatusLabelAttribute()`, `getStatusColorAttribute()`
    - Helpers: `isEditable()` (status DRAFT|REJECTED_*), `isSubmittable()`, `isCancellable()` (status bukan APPROVED & CANCELLED)
    - _Requirements: 1.4, 2.4, 3.1, 6.1_

  - [x] 3.2 Buat `App\Models\RabItem`
    - `$fillable`: `rab_id`, `kegiatan`, `catatan_kegiatan`, `biaya_anggaran`, `waktu_pelaksanaan`
    - Cast `biaya_anggaran` ke decimal
    - Relation: `belongsTo Rab`
    - _Requirements: 2.1_

  - [x] 3.3 Buat `App\Models\RabApprovalLog`
    - `$fillable`: `rab_id`, `aksi`, `dilakukan_oleh`, `catatan`
    - Cast `aksi` ke `RabAksiLog`
    - Relations: `belongsTo Rab`, `belongsTo User` (dilakukan_oleh)
    - _Requirements: 3.3, 4.3, 5.3, 6.3_

- [x] 4. Buat `App\Policies\RabPolicy`
  - Method `readPolicy`: cek permission `rab-read`
  - Method `createPolicy`: cek permission `rab-create` dan role user diawali `divisi_`
  - Method `updatePolicy`: cek permission `rab-update`, role `divisi_*`, RAB milik divisi sendiri (`divisi_id == user->getRoleNames()->first()`), dan `$rab->isEditable()`
  - Method `cancelPolicy`: cek role `divisi_*`, RAB milik divisi sendiri, dan `$rab->isCancellable()`
  - Method `approvePolicy`: cek role `bendahara_umum` atau `ketua_yayasan`
  - Daftarkan policy di `AppServiceProvider`
  - _Requirements: 1.6, 2.5, 4.5, 5.5, 6.5, 7.5, 8.6_

- [x] 5. Buat `App\Services\RabService` — method dasar
  - Extend `MasterService`
  - `generateNomorRab(string $bulanPengajuan): string` — jalankan dalam `DB::transaction()` dengan `SELECT MAX(nomor_rab) ... FOR UPDATE`, format `RAB/YYYYMM/NNN`, sequence reset per bulan
  - `storeRab(array $data, User $user): Rab` — buat Rab + RabItems dalam satu transaksi, auto-fill `divisi_id` dari role user, `dibuat_oleh` dari user id, status `DRAFT`, panggil `generateNomorRab()`
  - `updateRab(Rab $rab, array $data): Rab` — update header + sync items (delete lama, insert baru), panggil `updateComputedFields()`
  - `updateComputedFields(Rab $rab): void` — hitung ulang `total_kegiatan` = COUNT items, `total_biaya_anggaran` = SUM biaya_anggaran
  - `getDatatableQuery(array $filters, User $user): Builder` — jika role `divisi_*` filter `divisi_id = role_name`; jika `bendahara_umum`/`ketua_yayasan` tampil semua + support filter `status`, `divisi_id`, `bulan`; search pakai `ILIKE` pada `nomor_rab` dan `divisi_id`
  - _Requirements: 1.2, 1.3, 1.4, 1.5, 2.2, 2.3, 7.1, 8.1, 8.2, 10.1, 10.2, 10.3, 10.4_

  - [ ]* 5.1 Tulis property test untuk `generateNomorRab` (Property 2)
    - **Property 2: Nomor RAB Format dan Keunikan**
    - Verifikasi format `RAB/\d{6}/\d{3}`, keunikan global, sequence berurutan dari `001`, dan reset per bulan
    - **Validates: Requirements 1.5, 10.1, 10.2, 10.3, 10.4**

  - [ ]* 5.2 Tulis property test untuk `storeRab` auto-fill (Property 1)
    - **Property 1: New RAB Auto-Fill Invariant**
    - Verifikasi `divisi_id == role_name`, `dibuat_oleh == user.id`, `status == DRAFT` untuk semua user `divisi_*` valid
    - **Validates: Requirements 1.2, 1.3, 1.4**

  - [ ]* 5.3 Tulis property test untuk `updateComputedFields` (Property 4)
    - **Property 4: Computed Fields Invariant**
    - Untuk N items dengan biaya acak, verifikasi `total_kegiatan == COUNT` dan `total_biaya_anggaran == SUM` setelah setiap operasi tambah/ubah/hapus item
    - **Validates: Requirements 2.2, 2.3**

- [x] 6. Buat `RabService` — method approval
  - `submitRab(Rab $rab, User $user): Rab` — validasi status (DRAFT|REJECTED_*), ubah ke `PENDING_BENDAHARA`, catat log `SUBMITTED`
  - `cancelRab(Rab $rab, User $user): Rab` — validasi status bukan APPROVED/CANCELLED, ubah ke `CANCELLED`, catat log `CANCELLED`
  - `approveRab(Rab $rab, User $user, ?string $catatan): Rab` — deteksi role: jika `bendahara_umum` validasi status `PENDING_BENDAHARA` → ubah ke `PENDING_KETUA` + log `APPROVED_BENDAHARA`; jika `ketua_yayasan` validasi status `PENDING_KETUA` → ubah ke `APPROVED` + log `APPROVED_KETUA`; role lain throw HTTP 403
  - `rejectRab(Rab $rab, User $user, string $catatan): Rab` — validasi `catatan` tidak kosong (throw `ValidationException` jika kosong); deteksi role: `bendahara_umum` validasi `PENDING_BENDAHARA` → `REJECTED_BENDAHARA` + log; `ketua_yayasan` validasi `PENDING_KETUA` → `REJECTED_KETUA` + log; status salah throw HTTP 422
  - _Requirements: 3.1, 3.2, 3.3, 3.5, 4.1, 4.2, 4.3, 4.4, 4.6, 4.7, 5.1, 5.2, 5.3, 5.4, 5.6, 5.7, 6.2, 6.3, 6.4, 6.6_

  - [ ]* 6.1 Tulis property test untuk submit state transition (Property 6)
    - **Property 6: Submit State Transition**
    - Verifikasi status DRAFT/REJECTED_* → PENDING_BENDAHARA; status lain → HTTP 422
    - **Validates: Requirements 3.1, 3.2, 3.5**

  - [ ]* 6.2 Tulis property test untuk approval log invariant (Property 7)
    - **Property 7: Approval Log Invariant**
    - Setiap aksi status-changing harus menghasilkan tepat satu log entry dengan aksi, dilakukan_oleh, dan created_at yang benar; reject harus punya catatan terisi
    - **Validates: Requirements 3.3, 4.3, 4.4, 5.3, 5.4, 6.3**

  - [ ]* 6.3 Tulis property test untuk Bendahara approval transition (Property 8)
    - **Property 8: Bendahara Approval State Transition**
    - Approve `PENDING_BENDAHARA` → `PENDING_KETUA`; reject → `REJECTED_BENDAHARA`; non-bendahara → 403; status salah → 422
    - **Validates: Requirements 4.1, 4.2, 4.5, 4.6**

  - [ ]* 6.4 Tulis property test untuk Ketua approval transition (Property 9)
    - **Property 9: Ketua Approval State Transition**
    - Approve `PENDING_KETUA` → `APPROVED`; reject → `REJECTED_KETUA`; non-ketua → 403; status salah → 422
    - **Validates: Requirements 5.1, 5.2, 5.5, 5.6**

  - [ ]* 6.5 Tulis property test untuk cancel terminal state (Property 10)
    - **Property 10: Cancel Terminal State**
    - RAB berstatus `CANCELLED` tidak bisa diubah oleh aksi apapun → semua aksi return 422
    - **Validates: Requirements 6.4**

  - [ ]* 6.6 Tulis property test untuk cancel state transition (Property 11)
    - **Property 11: Cancel State Transition dan Otorisasi**
    - Divisi pengaju cancel status non-APPROVED/CANCELLED → `CANCELLED`; non-pengaju → 403; APPROVED/CANCELLED → 422
    - **Validates: Requirements 6.2, 6.5, 6.6**

- [x] 7. Checkpoint — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan ke user jika ada pertanyaan.

- [x] 8. Buat `Api\V1\Rab\RabController` — dataTable
  - Extend `MasterController`, inject `RabService`
  - Method `dataTable(Request $request)`: panggil `Gate::authorize('readPolicy', Rab::class)`, ambil params (`page`, `size`, `search`, `sortField`, `sortOrder`, `status`, `divisi_id`, `bulan`), panggil `rabService->getDatatableQuery()`, paginate, map response sesuai format datatable dengan field: `id`, `nomor_rab`, `divisi_id`, `bulan_pengajuan`, `bulan_pengajuan_label`, `total_kegiatan`, `total_biaya_anggaran`, `total_biaya_anggaran_formatted`, `status`, `status_label`, `status_color`, `can_edit`, `can_submit`, `can_cancel`, `can_approve`
  - Role `divisi_*`: filter otomatis by divisi sendiri, parameter `divisi_id` dari request diabaikan
  - Role `bendahara_umum`/`ketua_yayasan`: semua RAB tampil, filter `status`/`divisi_id`/`bulan` dari request diteruskan
  - _Requirements: 7.1, 7.2, 8.1, 8.2, 8.3_

  - [ ]* 8.1 Tulis property test untuk role-based data visibility (Property 12)
    - **Property 12: Role-Based Data Visibility**
    - User `divisi_*` hanya melihat RAB divisi sendiri; `bendahara_umum`/`ketua_yayasan` melihat semua
    - **Validates: Requirements 7.1, 8.1, 9.3, 9.4**

  - [ ]* 8.2 Tulis property test untuk filter datatable konsistensi (Property 13)
    - **Property 13: Filter Datatable Konsistensi**
    - Semua item response harus memenuhi semua kriteria filter yang diberikan
    - **Validates: Requirements 8.2**

- [x] 9. Buat `Api\V1\Rab\RabController` — submit dan cancel
  - Method `submit(Rab $rab)`: `Gate::authorize('cancelPolicy', $rab)` (divisi pengaju), panggil `rabService->submitRab()` dalam `callFunction()`
  - Method `cancel(Rab $rab)`: `Gate::authorize('cancelPolicy', $rab)`, panggil `rabService->cancelRab()` dalam `callFunction()`
  - _Requirements: 3.1, 3.2, 3.4, 3.5, 6.1, 6.2, 6.5, 6.6_

- [x] 10. Buat `Api\V1\Rab\RabApprovalController`
  - Extend `MasterController`, inject `RabService`
  - Method `approve(Request $request, Rab $rab)`: `Gate::authorize('approvePolicy', $rab)`, panggil `rabService->approveRab($rab, auth()->user(), $request->catatan)` dalam `callFunction()`
  - Method `reject(Request $request, Rab $rab)`: `Gate::authorize('approvePolicy', $rab)`, validasi `catatan` required, panggil `rabService->rejectRab($rab, auth()->user(), $request->catatan)` dalam `callFunction()`
  - Service mendeteksi role otomatis (bendahara vs ketua) dan memvalidasi status yang sesuai
  - _Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7_

- [x] 11. Buat `Web\Rab\RabController`
  - Extend `MasterController`, inject `RabService`
  - `index()`: `Gate::authorize('readPolicy', Rab::class)`, set breadcrumbs, render `rab.index`
  - `create()`: `Gate::authorize('createPolicy', Rab::class)`, set breadcrumbs, render `rab.create`
  - `store(Request $request)`: `Gate::authorize('createPolicy', Rab::class)`, validasi input (bulan_pengajuan required, items array min 1, biaya_anggaran numeric|min:0), panggil `rabService->storeRab()`, redirect `rab.index`
  - `show(Rab $rab)`: `Gate::authorize('readPolicy', Rab::class)` + cek divisi jika role `divisi_*`, load `rab.items`, `rab.approvalLogs.user`, render `rab.show`
  - `edit(Rab $rab)`: `Gate::authorize('updatePolicy', $rab)`, render `rab.edit`
  - `update(Request $request, Rab $rab)`: `Gate::authorize('updatePolicy', $rab)`, validasi input, panggil `rabService->updateRab()`, redirect `rab.index`
  - _Requirements: 1.1, 1.6, 1.7, 1.8, 2.4, 2.5, 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 12. Daftarkan routes
  - [x] 12.1 Tambah web routes di `routes/web.php` dalam middleware `auth`
    - `GET /rab` → `Web\Rab\RabController@index` (name: `rab.index`)
    - `GET /rab/create` → `@create` (name: `rab.create`)
    - `POST /rab` → `@store` (name: `rab.store`)
    - `GET /rab/{rab}` → `@show` (name: `rab.show`)
    - `GET /rab/{rab}/edit` → `@edit` (name: `rab.edit`)
    - `PUT /rab/{rab}` → `@update` (name: `rab.update`)
    - _Requirements: 1.1, 9.1_

  - [x] 12.2 Tambah API routes di `routes/api.php` dalam middleware `auth:sanctum`
    - `GET /api/v1/rab/datatable` → `Api\V1\Rab\RabController@dataTable`
    - `POST /api/v1/rab/{rab}/submit` → `@submit`
    - `POST /api/v1/rab/{rab}/cancel` → `@cancel`
    - `POST /api/v1/rab/{rab}/approve` → `Api\V1\Rab\RabApprovalController@approve`
    - `POST /api/v1/rab/{rab}/reject` → `@reject`
    - _Requirements: 3.1, 4.1, 5.1, 6.2, 7.1, 8.1_

- [x] 13. Buat Views Blade
  - [x] 13.1 Buat `resources/views/rab/index.blade.php`
    - Tabel datatable dengan kolom: `nomor_rab`, `divisi_id` (hanya untuk bendahara/ketua), `bulan_pengajuan`, `total_kegiatan`, `total_biaya_anggaran`, `status` (badge warna), aksi
    - Filter: `status`, `divisi_id` (hanya untuk bendahara/ketua), `bulan_pengajuan`
    - Tombol aksi per row: Edit & Resubmit (REJECTED_*), Cancel (bukan APPROVED/CANCELLED), Approve/Reject (sesuai role)
    - _Requirements: 7.2, 7.3, 7.4, 8.2, 8.3, 8.4, 8.5_

  - [x] 13.2 Buat `resources/views/rab/create.blade.php`
    - Form: `bulan_pengajuan` (month picker), dynamic rows untuk RAB items (kegiatan, catatan_kegiatan, biaya_anggaran, waktu_pelaksanaan)
    - Integrasi Summernote via CDN pada field `catatan_kegiatan` tiap item
    - Tombol tambah/hapus item row
    - _Requirements: 1.1, 2.1, 11.1, 11.2, 11.5_

  - [x] 13.3 Buat `resources/views/rab/edit.blade.php`
    - Sama seperti create, pre-fill data existing RAB dan items
    - Summernote diinisialisasi dengan konten existing
    - _Requirements: 2.1, 2.4, 11.1, 11.2_

  - [x] 13.4 Buat `resources/views/rab/show.blade.php`
    - Tampilkan header RAB (nomor, divisi, bulan, total, status badge)
    - Tabel daftar RAB items, render `catatan_kegiatan` sebagai HTML (`{!! !!}`)
    - Timeline approval log: kronologis lama → baru, tampilkan aksi, nama user, catatan, waktu
    - _Requirements: 9.1, 9.2, 11.4_

- [x] 14. Update `config/menus.php`
  - Tambahkan entry menu RAB sebelum menu Lokasi:
    ```php
    [
        'title' => 'RAB',
        'icon' => 'ki-filled ki-document',
        'permission' => ['rab-read'],
        'route' => 'rab.index',
        'pathUrl' => ['rab*']
    ]
    ```
  - _Requirements: 7.1, 8.1_

- [x] 15. Buat Validation untuk biaya_anggaran (Property 3)
  - Pastikan validasi di `Web\Rab\RabController@store` dan `@update` menolak `biaya_anggaran` negatif atau non-numerik dengan pesan deskriptif
  - Rule: `numeric|min:0`

  - [ ]* 15.1 Tulis property test untuk validasi biaya anggaran (Property 3)
    - **Property 3: Validasi Biaya Anggaran**
    - Semua request dengan `biaya_anggaran` negatif atau non-numerik harus ditolak dengan HTTP 422
    - **Validates: Requirements 1.8**

- [ ] 16. Tulis Feature Tests untuk happy path dan edge cases
  - [ ]* 16.1 Tulis feature test alur lengkap: buat RAB → submit → approve bendahara → approve ketua
    - Verifikasi setiap transisi status dan log entry yang dihasilkan
    - _Requirements: 3.1, 4.1, 5.1_

  - [ ]* 16.2 Tulis unit test RabPolicy untuk semua kombinasi role × aksi
    - Verifikasi 403 untuk role yang tidak berwenang pada setiap method policy
    - _Requirements: 1.6, 2.5, 4.5, 5.5, 6.5_

  - [ ]* 16.3 Tulis property test untuk catatan_kegiatan round trip (Property 14)
    - **Property 14: Catatan Kegiatan Round Trip**
    - Konten HTML yang disimpan harus identik saat dibaca kembali
    - **Validates: Requirements 11.3**

- [x] 17. Checkpoint akhir — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan ke user jika ada pertanyaan.

## Notes

- Tasks bertanda `*` bersifat opsional dan bisa dilewati untuk MVP lebih cepat
- Approval flow WAJIB berurutan: Bendahara Umum dulu, baru Ketua Yayasan — tidak boleh dibalik atau digabung
- `approveRab()` dan `rejectRab()` di service mendeteksi role otomatis; controller tidak perlu tahu tahap mana yang sedang berjalan
- Datatable filter by role diimplementasikan di `getDatatableQuery()` di service, bukan di controller
- Semua query search menggunakan `ILIKE` (PostgreSQL)
- Property tests menggunakan library **Eris** (`composer require --dev giorgiosironi/eris`)
- Setiap property test harus memiliki tag komentar: `// Feature: rab-pencatatan, Property {N}: {property_text}`
