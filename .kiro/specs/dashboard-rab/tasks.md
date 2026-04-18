# Implementation Plan: Dashboard RAB

## Overview

Implementasi fitur Dashboard RAB mencakup: halaman ringkasan statistik dengan summary cards dan chart, filter periode dan divisi berbasis role, dua API endpoint untuk data dinamis (AJAX), dan export Excel dua sheet. Mengikuti pola arsitektur yang sudah ada: `MasterController` + `callFunction()`, `MasterService`, Spatie Permission, dan Maatwebsite Excel.

## Tasks

- [x] 1. Buat `App\Services\DashboardRabService`
  - [x] 1.1 Buat class `DashboardRabService` yang extend `MasterService`
    - Buat file `app/Services/Dashboard/DashboardRabService.php`
    - Method `getSummary(array $filters, User $user): array` — query agregat: `total_rab` (COUNT), `total_kegiatan` (SUM `total_kegiatan`), `total_anggaran` (SUM `total_biaya_anggaran`), `total_approved` (COUNT status APPROVED), `total_pending` (COUNT status PENDING_BENDAHARA atau PENDING_KETUA)
    - Method `getChartData(array $filters, User $user): array` — return array berisi `status_distribution` (jumlah per status), `monthly_trend` (total anggaran per bulan dalam tahun terpilih), `divisi_comparison` (total anggaran per divisi — hanya untuk Privileged_User)
    - Method `buildBaseQuery(array $filters, User $user): Builder` — query builder dengan filter role (divisi_* → filter by divisi sendiri; Privileged_User → filter opsional by divisi_id), filter tahun (REQUIRED), filter bulan (opsional)
    - Method `getAvailableYears(): array` — ambil distinct tahun dari kolom `bulan_pengajuan` di tabel `rabs`, tambahkan tahun berjalan jika belum ada, urutkan descending
    - Method `getDivisiList(): array` — ambil distinct `divisi_id` dari tabel `rabs` untuk dropdown filter divisi
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 3.1, 3.2, 3.3, 4.1, 4.2, 4.3, 5.1, 5.2, 5.3, 6.1, 6.2, 6.3, 6.4_

  - [x] 1.2 Implementasi filter role di `buildBaseQuery`
    - Jika role user diawali `divisi_`: selalu filter `divisi_id = role_name`, abaikan parameter `divisi_id` dari request
    - Jika Privileged_User (`bendahara_umum`, `ketua_yayasan`, `super_admin`): filter `divisi_id` opsional dari parameter
    - Filter tahun: `YEAR(bulan_pengajuan) = tahun` — gunakan `whereYear()` Eloquent
    - Filter bulan: `MONTH(bulan_pengajuan) = bulan` — gunakan `whereMonth()` Eloquent, opsional
    - _Requirements: 4.3, 4.4, 5.2, 5.3, 6.3, 6.4_

- [x] 2. Buat `App\Exports\RabExport`
  - [x] 2.1 Buat class `RabExport` di `app/Exports/RabExport.php`
    - Implement `WithMultipleSheets` dari Maatwebsite Excel
    - Constructor menerima `array $filters` dan `User $user`
    - Method `sheets()` mengembalikan array dua sheet: `[new RabHeaderSheet($filters, $user), new RabItemSheet($filters, $user)]`
    - _Requirements: 7.1, 7.7, 8.1, 9.1_

  - [x] 2.2 Buat class `RabHeaderSheet` di `app/Exports/RabHeaderSheet.php`
    - Implement `FromQuery`, `WithHeadings`, `WithTitle`, `WithMapping`, `WithStyles`
    - `title()` mengembalikan `"Data RAB"`
    - `headings()` mengembalikan: `['No', 'Nomor RAB', 'Divisi', 'Bulan Pengajuan', 'Total Kegiatan', 'Total Anggaran', 'Status', 'Dibuat Oleh', 'Tanggal Dibuat']`
    - `query()` mengembalikan query RAB dengan filter role + periode, eager load `dibuatOleh`, diurutkan `nomor_rab` ascending
    - `map($rab)` memetakan setiap row: kolom `Status` pakai `$rab->status_label`, `Bulan Pengajuan` format `"Bulan YYYY"` (contoh: `"April 2026"`), `Total Anggaran` sebagai float (bukan string) agar Excel format angka
    - `styles()` untuk bold header row
    - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5, 8.6, 10.1, 10.2, 10.3, 10.4_

  - [x] 2.3 Buat class `RabItemSheet` di `app/Exports/RabItemSheet.php`
    - Implement `FromQuery`, `WithHeadings`, `WithTitle`, `WithMapping`, `WithStyles`
    - `title()` mengembalikan `"Item RAB"`
    - `headings()` mengembalikan: `['No', 'Nomor RAB', 'Divisi', 'Kegiatan', 'Catatan Kegiatan', 'Biaya Anggaran', 'Waktu Pelaksanaan']`
    - `query()` mengembalikan query `RabItem` dengan join ke `rabs`, filter role + periode via `whereHas('rab', ...)`, eager load relasi `rab`, diurutkan `rabs.nomor_rab` ascending lalu `rab_items.id` ascending
    - `map($item)` memetakan setiap row: `Catatan Kegiatan` pakai `strip_tags($item->catatan_kegiatan)`, `Biaya Anggaran` sebagai float
    - `styles()` untuk bold header row
    - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 10.1, 10.2, 10.3, 10.4_

- [x] 3. Buat `Api\V1\Rab\RabDashboardController`
  - [x] 3.1 Buat file `app/Http/Controllers/Api/V1/Rab/RabDashboardController.php`
    - Extend `MasterController`, inject `DashboardRabService`
    - Method `summary(Request $request)`: validasi `tahun` (required|integer), `bulan` (nullable|integer|between:1,12), `divisi_id` (nullable|string); jika validasi gagal return HTTP 422; panggil `dashboardRabService->getSummary()`; return JSON sesuai format requirement 6.6
    - Method `chart(Request $request)`: validasi sama dengan `summary`; panggil `dashboardRabService->getChartData()`; return JSON dengan key `status_distribution`, `monthly_trend`, `divisi_comparison`
    - Kedua method gunakan `Gate::authorize('readPolicy', Rab::class)` sebelum proses
    - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6_

- [x] 4. Buat `Web\Rab\RabDashboardController`
  - [x] 4.1 Buat file `app/Http/Controllers/Web/Rab/RabDashboardController.php`
    - Extend `MasterController`, inject `DashboardRabService`
    - Method `index()`: `Gate::authorize('readPolicy', Rab::class)`, ambil `availableYears` dan `divisiList` dari service, set breadcrumbs `['RAB', 'Dashboard']` dan `pageTitle = 'Dashboard RAB'`, render view `rab.dashboard`
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7_

  - [x] 4.2 Buat `Web\Rab\RabExportController`
    - Buat file `app/Http/Controllers/Web/Rab/RabExportController.php`
    - Extend `MasterController`, inject `DashboardRabService`
    - Method `export(Request $request)`: `Gate::authorize('readPolicy', Rab::class)`, ambil filter dari request (`tahun`, `bulan`, `divisi_id`), buat instance `RabExport`, return `Excel::download()` dengan nama file `RAB_Export_{timestamp}.xlsx` (format `Ymd_His`)
    - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6, 7.7_

- [x] 5. Daftarkan routes
  - [x] 5.1 Tambah web routes di `routes/web.php` dalam prefix `/rab`
    - `GET /rab/dashboard` → `Web\Rab\RabDashboardController@index` (name: `rab.dashboard`)
    - `GET /rab/export` → `Web\Rab\RabExportController@export` (name: `rab.export`)
    - Pastikan kedua route didaftarkan SEBELUM route `/{rab}` agar tidak tertangkap sebagai parameter
    - _Requirements: 1.1, 7.1_

  - [x] 5.2 Tambah API routes di `routes/api.php` dalam prefix `/rab`
    - `GET /api/v1/rab/dashboard/summary` → `Api\V1\Rab\RabDashboardController@summary` (name: `rab.dashboard.summary`)
    - `GET /api/v1/rab/dashboard/chart` → `Api\V1\Rab\RabDashboardController@chart` (name: `rab.dashboard.chart`)
    - Tambahkan dalam middleware `auth:sanctum`
    - _Requirements: 6.1, 6.2_

- [x] 6. Buat View Blade `resources/views/rab/dashboard.blade.php`
  - [x] 6.1 Buat struktur halaman dan filter
    - Extend layout utama, set breadcrumb dan page title
    - Komponen filter: dropdown tahun (dari `$data['availableYears']`), dropdown bulan (1–12 + "Semua Bulan"), dropdown divisi (dari `$data['divisiList']` — hanya tampil untuk Privileged_User, sembunyikan untuk role `divisi_*`)
    - Default filter: tahun berjalan, "Semua Bulan", "Semua Divisi"
    - Tombol Export Excel yang memanggil `route('rab.export')` dengan parameter filter aktif
    - _Requirements: 1.4, 1.5, 1.6, 1.7, 4.1, 4.2, 4.5, 4.6, 5.1, 5.4, 5.5_

  - [x] 6.2 Buat komponen Summary Cards
    - 5 kartu: Total RAB, Total Kegiatan, Total Anggaran (format Rupiah), RAB Disetujui, RAB Pending
    - Setiap kartu memiliki area loading indicator (spinner) yang tampil saat AJAX berjalan
    - Nilai kartu diisi via JavaScript setelah AJAX selesai
    - Tampilkan pesan "Tidak ada data untuk periode ini" jika semua nilai 0
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 3.5_

  - [x] 6.3 Buat komponen Chart
    - Chart distribusi status (pie/donut chart) menggunakan library chart (ApexCharts atau Chart.js sesuai yang sudah ada di project)
    - Chart tren anggaran per bulan (bar/line chart) — tampilkan 12 bulan dalam tahun terpilih
    - Chart perbandingan anggaran per divisi (bar chart) — hanya render untuk Privileged_User
    - Semua chart diupdate via JavaScript saat filter berubah
    - Tampilkan pesan "Tidak ada data untuk periode ini" jika data kosong
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5_

  - [x] 6.4 Implementasi AJAX dan interaktivitas filter
    - Buat fungsi JavaScript `loadDashboardData()` yang memanggil kedua endpoint API (`/api/v1/rab/dashboard/summary` dan `/api/v1/rab/dashboard/chart`) secara bersamaan menggunakan `fetch` atau `axios`
    - Sertakan Sanctum token (dari meta tag atau cookie) pada setiap request API
    - Panggil `loadDashboardData()` saat halaman pertama kali dimuat dan setiap kali nilai filter berubah (event `change` pada dropdown)
    - Update semua summary cards dan chart dengan data response
    - _Requirements: 2.6, 2.7, 3.4, 4.3, 4.4, 5.2, 5.3_

- [x] 7. Update `config/menus.php`
  - Tambahkan sub-menu atau entry terpisah untuk Dashboard RAB di bawah menu RAB yang sudah ada
  - Entry: `title = 'Dashboard RAB'`, `route = 'rab.dashboard'`, `pathUrl = ['rab/dashboard*']`, `permission = ['rab-read']`
  - _Requirements: 1.1_

- [x] 8. Checkpoint — Pastikan semua komponen terintegrasi
  - Pastikan semua tests lulus, tanyakan ke user jika ada pertanyaan.

- [ ] 9. Tulis Feature Tests
  - [ ]* 9.1 Tulis feature test untuk akses kontrol halaman dashboard
    - Test HTTP 403 untuk user tanpa permission `rab-read`
    - Test HTTP 200 untuk user dengan permission `rab-read`
    - _Requirements: 1.2, 1.3_

  - [ ]* 9.2 Tulis feature test untuk API endpoint summary
    - Test validasi: `tahun` tidak diberikan → HTTP 422
    - Test filter role: user `divisi_*` tidak bisa lihat data divisi lain
    - Test format response sesuai requirement 6.6
    - _Requirements: 6.3, 6.4, 6.5, 6.6_

  - [ ]* 9.3 Tulis feature test untuk export Excel
    - Test HTTP 403 untuk user tanpa permission `rab-read`
    - Test file yang dihasilkan memiliki dua sheet dengan nama yang benar
    - Test filter role: export user `divisi_*` hanya berisi data divisi sendiri
    - Test export dengan filter kosong menghasilkan file valid (hanya header)
    - _Requirements: 7.2, 7.3, 8.1, 9.1, 10.1, 10.5_

- [ ] 10. Checkpoint akhir — Pastikan semua tests lulus
  - Pastikan semua tests lulus, tanyakan ke user jika ada pertanyaan.

## Notes

- Tasks bertanda `*` bersifat opsional dan bisa dilewati untuk MVP lebih cepat
- Route `/rab/dashboard` dan `/rab/export` HARUS didaftarkan sebelum `/{rab}` di `routes/web.php` untuk menghindari konflik route parameter
- Filter role diimplementasikan di `DashboardRabService::buildBaseQuery()` — controller tidak boleh langsung query model
- Semua query menggunakan `whereYear()` / `whereMonth()` Eloquent (PostgreSQL-compatible)
- Export menggunakan Maatwebsite Excel dengan `WithMultipleSheets` — dua sheet terpisah dalam satu file
- Kolom angka di Excel (Total Anggaran, Biaya Anggaran) harus berupa float/numeric, bukan string, agar Excel mengenali sebagai format angka
- Privileged_User: role `bendahara_umum`, `ketua_yayasan`, atau `super_admin`
- AJAX di frontend menggunakan Sanctum token — pastikan token tersedia di halaman (meta tag atau cookie)
