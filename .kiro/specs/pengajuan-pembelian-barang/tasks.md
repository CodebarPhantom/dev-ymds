# Implementation Plan: Pengajuan Pembelian Barang

## Overview

Implementasi modul Pengajuan Pembelian Barang di atas Laravel 12 mengikuti pola arsitektur yang sudah ada: MasterController + callFunction(), MasterService, Spatie Permission, Sanctum, dan Fortify. Urutan implementasi dimulai dari fondasi database dan enum, lalu model dan service, kemudian controller dan view, dan diakhiri dengan dashboard serta export Excel.

## Tasks

- [ ] 1. Buat migrasi database dan enum
  - Buat file migrasi `create_purchase_requests_table` dengan semua kolom, index, dan constraint sesuai desain
  - Buat file migrasi `create_purchase_request_items_table` dengan semua kolom, index, dan FK cascade delete
  - Buat file migrasi `create_purchase_request_logs_table` dengan semua kolom, index, dan FK cascade delete
  - Buat `App\Enums\PurchaseRequestStatus` dengan semua case enum
  - Buat `App\Enums\PurchaseRequestAksiLog` dengan semua case enum
  - Jalankan migrasi
  - _Requirements: 1.4, 1.5, 2.1, 6.3, 11.1_

- [ ] 2. Buat Model Eloquent
  - [ ] 2.1 Buat model `PurchaseRequest`
    - Definisikan `$fillable`, `$casts` (status ke PurchaseRequestStatus, tanggal_dibutuhkan ke date)
    - Tambahkan relasi `hasMany PurchaseRequestItem`, `hasMany PurchaseRequestLog`, `belongsTo User (dibuat_oleh)`
    - Implementasikan accessor `getStatusLabelAttribute()` dan `getStatusColorAttribute()`
    - Implementasikan helper methods: `isEditable()`, `isSubmittable()`, `isCancellable()`, `isPurchasable()`, `isInPurchasing()`
    - _Requirements: 1.4, 2.4, 3.1, 7.1_

  - [ ] 2.2 Buat model `PurchaseRequestItem`
    - Definisikan `$fillable`, `$casts` (sudah_dibeli ke boolean)
    - Tambahkan relasi `belongsTo PurchaseRequest`
    - _Requirements: 2.1, 6.3_

  - [ ] 2.3 Buat model `PurchaseRequestLog`
    - Definisikan `$fillable`, `$casts` (aksi ke PurchaseRequestAksiLog)
    - Tambahkan relasi `belongsTo PurchaseRequest`, `belongsTo User (dilakukan_oleh)`
    - _Requirements: 3.3, 4.3, 5.3, 6.2, 7.3_

- [ ] 3. Buat PurchaseRequestPolicy dan daftarkan permission
  - Buat `App\Policies\PurchaseRequestPolicy` dengan method: `readPolicy`, `createPolicy`, `updatePolicy`, `cancelPolicy`, `approvePolicy`, `purchasingPolicy`
  - Daftarkan policy di `AppServiceProvider`
  - Tambahkan permission baru ke seeder: `purchase-request-read`, `purchase-request-create`, `purchase-request-update`, `purchase-request-cancel`, `purchase-request-approve`
  - _Requirements: 12.3, 12.4_

- [ ] 4. Implementasikan PurchaseRequestService — core CRUD dan nomor pengajuan
  - [ ] 4.1 Buat `App\Services\PurchaseRequestService` (extend MasterService)
    - Implementasikan `generateNomorPengajuan(): string` menggunakan `DB::transaction()` dengan `SELECT ... FOR UPDATE` dan strategi MAX+1
    - Implementasikan `store(array $data, User $user): PurchaseRequest` — buat PR + items + generate nomor, set divisi_id dari role_name user, set dibuat_oleh dari user id, set status DRAFT
    - Implementasikan `update(PurchaseRequest $pr, array $data): PurchaseRequest` — update header + sync items + panggil updateComputedFields
    - Implementasikan `updateComputedFields(PurchaseRequest $pr): void` — hitung ulang total_item dan total_biaya_estimasi
    - _Requirements: 1.2, 1.3, 1.4, 1.5, 2.2, 2.3, 11.1, 11.2, 11.3, 11.4_

  - [ ]* 4.2 Tulis property test untuk auto-fill invariant (Property 1)
    - **Property 1: New PurchaseRequest Auto-Fill Invariant**
    - **Validates: Requirements 1.2, 1.3, 1.4**

  - [ ]* 4.3 Tulis property test untuk nomor pengajuan format dan keunikan (Property 2)
    - **Property 2: Nomor Pengajuan Format dan Keunikan**
    - **Validates: Requirements 1.5, 11.1, 11.2, 11.3, 11.4**

  - [ ]* 4.4 Tulis property test untuk computed fields invariant (Property 4)
    - **Property 4: Computed Fields Invariant**
    - **Validates: Requirements 2.2, 2.3**

- [ ] 5. Implementasikan PurchaseRequestService — state machine (submit, cancel, approve, reject)
  - [ ] 5.1 Implementasikan method `submit`, `cancel`, `approve`, `reject` di PurchaseRequestService
    - `submit(PurchaseRequest $pr, User $user)`: validasi status submittable, ubah ke PENDING_BENDAHARA, catat log SUBMITTED
    - `cancel(PurchaseRequest $pr, User $user)`: validasi status cancellable, ubah ke CANCELLED, catat log CANCELLED
    - `approve(PurchaseRequest $pr, User $user, ?string $catatan)`: deteksi role user, ubah status sesuai (PENDING_KETUA atau APPROVED), catat log APPROVED_BENDAHARA atau APPROVED_KETUA
    - `reject(PurchaseRequest $pr, User $user, string $catatan)`: validasi catatan tidak kosong, deteksi role user, ubah status sesuai (REJECTED_BENDAHARA atau REJECTED_KETUA), catat log
    - _Requirements: 3.1, 3.2, 3.3, 3.5, 4.1, 4.2, 4.3, 4.4, 4.6, 4.7, 5.1, 5.2, 5.3, 5.4, 5.6, 5.7, 7.2, 7.3, 7.4_

  - [ ]* 5.2 Tulis property test untuk submit state transition (Property 6)
    - **Property 6: Submit State Transition**
    - **Validates: Requirements 3.1, 3.2, 3.5**

  - [ ]* 5.3 Tulis property test untuk approval log invariant (Property 7)
    - **Property 7: Approval Log Invariant**
    - **Validates: Requirements 3.3, 4.3, 4.4, 5.3, 5.4, 6.2, 6.4, 7.3**

  - [ ]* 5.4 Tulis property test untuk Bendahara approval state transition (Property 8)
    - **Property 8: Bendahara Approval State Transition**
    - **Validates: Requirements 4.1, 4.2, 4.5, 4.6**

  - [ ]* 5.5 Tulis property test untuk Ketua approval state transition (Property 9)
    - **Property 9: Ketua Approval State Transition**
    - **Validates: Requirements 5.1, 5.2, 5.5, 5.6**

  - [ ]* 5.6 Tulis property test untuk cancel state transition dan otorisasi (Property 12)
    - **Property 12: Cancel State Transition dan Otorisasi**
    - **Validates: Requirements 7.1, 7.2, 7.4, 7.5, 7.6**

  - [ ]* 5.7 Tulis property test untuk CANCELLED sebagai terminal state (Property 13)
    - **Property 13: CANCELLED adalah Terminal State**
    - **Validates: Requirements 7.4**

- [ ] 6. Checkpoint — Pastikan semua test lulus
  - Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

- [ ] 7. Implementasikan PurchaseRequestService — proses pembelian (Sarpras)
  - [ ] 7.1 Implementasikan method `startPurchasing` dan `markItemPurchased` di PurchaseRequestService
    - `startPurchasing(PurchaseRequest $pr, User $user)`: validasi status APPROVED, ubah ke PURCHASING, catat log PURCHASING_STARTED
    - `markItemPurchased(PurchaseRequest $pr, PurchaseRequestItem $item, array $data, User $user)`: validasi harga_aktual tidak negatif, set sudah_dibeli=true dan harga_aktual, panggil updateActualFields, cek apakah semua item sudah dibeli untuk tentukan status PARTIALLY_PURCHASED atau COMPLETED, catat log ITEM_PURCHASED (dan COMPLETED jika selesai)
    - Implementasikan `updateActualFields(PurchaseRequest $pr): void` — hitung ulang total_biaya_aktual dari item sudah_dibeli=true
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 6.7, 6.9, 6.10_

  - [ ]* 7.2 Tulis property test untuk purchasing state transitions (Property 10)
    - **Property 10: Purchasing State Transitions**
    - **Validates: Requirements 6.1, 6.5, 6.6, 6.8, 6.9**

  - [ ]* 7.3 Tulis property test untuk total biaya aktual invariant (Property 11)
    - **Property 11: Total Biaya Aktual Invariant**
    - **Validates: Requirements 6.7**

- [ ] 8. Implementasikan PurchaseRequestService — datatable query
  - Implementasikan `getDatatableQuery(array $filters, User $user): Builder` di PurchaseRequestService
  - Terapkan filter role: user divisi_* (kecuali divisi_sarpras) hanya lihat data divisi sendiri; privileged user lihat semua
  - Terapkan filter opsional: status, divisi_id (hanya untuk privileged), tanggal_dibutuhkan
  - Terapkan search ILIKE pada nomor_pengajuan dan judul_pengajuan (gunakan ILIKE karena PostgreSQL)
  - _Requirements: 8.1, 9.1, 9.2, 12.1, 12.2_

  - [ ]* 8.1 Tulis property test untuk role-based data visibility (Property 14)
    - **Property 14: Role-Based Data Visibility**
    - **Validates: Requirements 8.1, 9.1, 12.1, 12.2**

  - [ ]* 8.2 Tulis property test untuk filter datatable konsistensi (Property 15)
    - **Property 15: Filter Datatable Konsistensi**
    - **Validates: Requirements 9.2**

- [ ] 9. Buat validasi input (Form Request / inline validation)
  - Buat validasi untuk store/update PurchaseRequest: judul_pengajuan wajib, tanggal_dibutuhkan wajib, minimal satu item
  - Buat validasi untuk PurchaseRequest_Item: nama_barang wajib, satuan wajib, jumlah integer > 0, biaya_estimasi numerik >= 0, catatan_item opsional
  - Buat validasi untuk reject: catatan wajib diisi
  - Buat validasi untuk markItemPurchased: harga_aktual numerik >= 0
  - _Requirements: 1.1, 1.7, 1.8, 1.9, 2.1, 4.7, 5.7, 6.10_

  - [ ]* 9.1 Tulis property test untuk validasi input item (Property 3)
    - **Property 3: Validasi Input Item**
    - **Validates: Requirements 1.8, 1.9, 6.10**

  - [ ]* 9.2 Tulis property test untuk edit permission berdasarkan status (Property 5)
    - **Property 5: Edit Permission Berdasarkan Status**
    - **Validates: Requirements 2.4, 2.5**

- [ ] 10. Buat API Controllers
  - [ ] 10.1 Buat `Api\V1\PurchaseRequest\PurchaseRequestController`
    - Implementasikan `dataTable(Request)` — panggil getDatatableQuery, paginate, format response sesuai desain (termasuk can_edit, can_submit, can_cancel, can_approve, can_start_purchasing)
    - _Requirements: 8.2, 9.3, 12.3_

  - [ ] 10.2 Buat `Api\V1\PurchaseRequest\PurchaseRequestApprovalController`
    - Implementasikan `submit`, `cancel`, `approve`, `reject` menggunakan callFunction() pattern
    - Gunakan Gate::authorize dengan policy yang sesuai
    - _Requirements: 3.4, 4.5, 5.5, 7.5, 12.4_

  - [ ] 10.3 Buat `Api\V1\PurchaseRequest\PurchaseRequestPurchasingController`
    - Implementasikan `startPurchasing` dan `markItemPurchased` menggunakan callFunction() pattern
    - Gunakan Gate::authorize purchasingPolicy
    - _Requirements: 6.8, 6.9, 6.10_

- [ ] 11. Buat Web Controllers
  - [ ] 11.1 Buat `Web\PurchaseRequest\PurchaseRequestController`
    - Implementasikan `index()`, `create()`, `store()`, `show()`, `edit()`, `update()` menggunakan callFunction() pattern
    - Set breadcrumbs di setiap method
    - Gunakan Gate::authorize dengan policy yang sesuai
    - _Requirements: 1.6, 2.5, 8.5, 10.3, 10.4, 10.5, 12.4_

  - [ ] 11.2 Daftarkan routes web dan API di routes/web.php dan routes/api.php
    - Tambahkan route group web `/purchase-requests` dengan urutan yang benar (dashboard dan export sebelum /{purchaseRequest})
    - Tambahkan route group API `/api/v1/purchase-requests`
    - _Requirements: 13.1_

- [ ] 12. Buat Blade Views — index dan create/edit
  - [ ] 12.1 Buat `resources/views/purchase-request/index.blade.php`
    - Tampilkan datatable dengan kolom sesuai desain (nomor_pengajuan, judul_pengajuan, tanggal_dibutuhkan, total_item, total_biaya_estimasi, status, aksi)
    - Tampilkan kolom divisi_id hanya untuk privileged user
    - Tampilkan tombol Edit & Resubmit hanya untuk status REJECTED_BENDAHARA atau REJECTED_KETUA
    - Tampilkan tombol Cancel hanya untuk status yang bisa dicancel
    - Tampilkan tombol Approve/Reject untuk Bendahara (PENDING_BENDAHARA) dan Ketua (PENDING_KETUA)
    - Tampilkan tombol Proses Pembelian untuk Sarpras (APPROVED, PURCHASING, PARTIALLY_PURCHASED)
    - _Requirements: 8.2, 8.3, 8.4, 9.3, 9.4, 9.5, 9.6_

  - [ ] 12.2 Buat `resources/views/purchase-request/create.blade.php`
    - Form dengan field judul_pengajuan, keperluan (Summernote), tanggal_dibutuhkan
    - Dynamic item rows (tambah/hapus item) dengan field nama_barang, satuan, jumlah, biaya_estimasi, catatan_item
    - _Requirements: 1.1_

  - [ ] 12.3 Buat `resources/views/purchase-request/edit.blade.php`
    - Form edit yang sama dengan create, pre-filled dengan data existing
    - Summernote untuk field keperluan
    - _Requirements: 2.4_

- [ ] 13. Buat Blade View — show (detail)
  - Buat `resources/views/purchase-request/show.blade.php`
  - Tampilkan semua data header PurchaseRequest
  - Tampilkan daftar PurchaseRequest_Item; tampilkan kolom sudah_dibeli dan harga_aktual saat status PURCHASING atau PARTIALLY_PURCHASED
  - Tampilkan timeline PurchaseRequest_Log secara descending (terbaru di atas) dengan aksi, dilakukan_oleh, catatan, created_at
  - Render field keperluan dengan {!! $purchaseRequest->keperluan !!}
  - _Requirements: 10.1, 10.2, 10.6_

- [ ] 14. Tambahkan menu entry di config/menus.php
  - Tambahkan entry menu "Pengajuan Pembelian" dengan children Dashboard dan Daftar Pengajuan sesuai desain
  - _Requirements: 13.1_

- [ ] 15. Checkpoint — Pastikan semua test lulus
  - Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

- [ ] 16. Implementasikan DashboardPurchaseRequestService
  - Buat `App\Services\Dashboard\DashboardPurchaseRequestService` (extend MasterService)
  - Implementasikan method untuk menghitung summary: total_pengajuan, total_dalam_approval (PENDING_BENDAHARA + PENDING_KETUA), total_dalam_pembelian (PURCHASING + PARTIALLY_PURCHASED), total_selesai (COMPLETED), total_biaya_estimasi, total_biaya_aktual
  - Terapkan filter tahun (wajib), bulan (opsional), divisi_id (opsional, diabaikan untuk user divisi_*)
  - Terapkan role-based data scoping (sama dengan getDatatableQuery)
  - Format nilai biaya sebagai angka dengan pemisah ribuan (format Rupiah)
  - _Requirements: 13.2, 13.7, 13.8, 13.9, 13.10, 13.11, 13.12, 13.17, 13.18, 13.29_

  - [ ]* 16.1 Tulis property test untuk dashboard summary konsistensi (Property 16)
    - **Property 16: Dashboard Summary Konsistensi**
    - **Validates: Requirements 13.7, 13.8, 13.9, 13.10, 13.11, 13.12**

- [ ] 17. Buat API Dashboard Controller dan Web Dashboard Controller
  - [ ] 17.1 Buat `Api\V1\PurchaseRequest\PurchaseRequestDashboardController`
    - Implementasikan `summary(Request)` — validasi parameter tahun wajib, panggil DashboardPurchaseRequestService, kembalikan JSON sesuai format desain
    - Implementasikan `chart(Request)` — kembalikan data chart (distribusi status per bulan)
    - Validasi: jika tahun tidak diberikan atau tidak valid, kembalikan HTTP 422
    - _Requirements: 13.26, 13.27, 13.28, 13.30, 13.31_

  - [ ] 17.2 Buat `Web\PurchaseRequest\PurchaseRequestDashboardController`
    - Implementasikan `index()` — set breadcrumbs, pass data tahun tersedia dan flag isPrivilegedUser ke view
    - Gunakan Gate::authorize readPolicy
    - _Requirements: 13.1, 13.2, 13.3, 13.4_

  - [ ] 17.3 Buat `resources/views/purchase-request/dashboard.blade.php`
    - Tampilkan 6 summary cards (total pengajuan, dalam approval, dalam pembelian, selesai, total biaya estimasi, total biaya aktual)
    - Tampilkan indikator loading saat data sedang dimuat
    - Filter tahun (default tahun berjalan) dan bulan (default "Semua Bulan")
    - Tampilkan filter divisi hanya untuk privileged user (default "Semua Divisi")
    - Sembunyikan filter divisi untuk user divisi_* (kecuali divisi_sarpras)
    - Update summary cards via AJAX tanpa reload halaman saat filter berubah
    - _Requirements: 13.4, 13.5, 13.6, 13.13, 13.14, 13.15, 13.16, 13.19, 13.20, 13.21, 13.22, 13.23, 13.24, 13.25_

- [ ] 18. Implementasikan Export Excel
  - [ ] 18.1 Buat `App\Exports\PurchaseRequestHeaderSheet` (implement FromQuery, WithHeadings, WithMapping, WithTitle)
    - Sheet name: "Data Pengajuan"
    - Kolom: No, Nomor Pengajuan, Divisi, Judul Pengajuan, Tanggal Dibutuhkan, Total Item, Total Biaya Estimasi, Total Biaya Aktual, Status (label), Dibuat Oleh, Tanggal Dibuat
    - Format kolom biaya sebagai angka dengan pemisah ribuan (NumberFormat Excel)
    - Urutkan berdasarkan nomor_pengajuan ascending
    - _Requirements: 13.39, 13.41, 13.42, 13.43_

  - [ ] 18.2 Buat `App\Exports\PurchaseRequestItemSheet` (implement FromQuery, WithHeadings, WithMapping, WithTitle)
    - Sheet name: "Item Barang"
    - Kolom: No, Nomor Pengajuan, Divisi, Nama Barang, Satuan, Jumlah, Biaya Estimasi, Sudah Dibeli, Harga Aktual
    - Format kolom biaya sebagai angka dengan pemisah ribuan (NumberFormat Excel)
    - Urutkan berdasarkan nomor_pengajuan ascending kemudian urutan item
    - _Requirements: 13.40, 13.41, 13.43_

  - [ ] 18.3 Buat `App\Exports\PurchaseRequestExport` (implement WithMultipleSheets)
    - Gabungkan PurchaseRequestHeaderSheet dan PurchaseRequestItemSheet
    - Terapkan filter role-based scoping (sama dengan datatable)
    - Jika tidak ada data, hasilkan file valid dengan hanya baris header
    - _Requirements: 13.32, 13.44_

  - [ ] 18.4 Buat `Web\PurchaseRequest\PurchaseRequestExportController`
    - Implementasikan `export(Request)` — validasi permission, terapkan filter, generate nama file `PurchaseRequest_Export_YYYYMMDD_HHmmss.xlsx`, return download response
    - _Requirements: 13.33, 13.34, 13.35, 13.36, 13.37, 13.38_

  - [ ]* 18.5 Tulis property test untuk export data completeness dan role isolation (Property 17)
    - **Property 17: Export Data Completeness dan Role Isolation**
    - **Validates: Requirements 13.36, 13.37, 13.45, 13.46**

- [ ] 19. Final Checkpoint — Pastikan semua test lulus
  - Pastikan semua test lulus, tanyakan kepada user jika ada pertanyaan.

## Notes

- Task bertanda `*` bersifat opsional dan dapat dilewati untuk MVP yang lebih cepat
- Setiap task mereferensikan requirement spesifik untuk keterlacakan
- Checkpoint memastikan validasi inkremental di setiap tahap
- Property tests memvalidasi properti kebenaran universal dari desain
- Unit tests memvalidasi contoh spesifik dan edge case
- Gunakan `ILIKE` (bukan `LIKE`) untuk semua pencarian teks karena database PostgreSQL
- Ikuti pola MasterController + callFunction() untuk semua controller
- Ikuti pola MasterService untuk semua service
