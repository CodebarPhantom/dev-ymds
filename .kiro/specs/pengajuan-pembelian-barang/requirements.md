# Requirements Document

## Introduction

Fitur Pengajuan Pembelian Barang adalah modul yang memungkinkan semua divisi mengajukan kebutuhan pembelian barang. Setiap pengajuan berisi header dan daftar item barang yang dibutuhkan. Pengajuan melewati alur approval dua tingkat: Bendahara Umum lalu Ketua Yayasan. Setelah disetujui, divisi Sarpras mengelola proses pembelian fisik dengan menginput barang yang sudah datang beserta harga aktualnya. Sistem ini dibangun di atas Laravel 12 dengan Spatie Permission untuk manajemen role, mengikuti pola MasterController dan Service yang sudah ada.

## Glossary

- **PurchaseRequest_System**: Sistem pengajuan pembelian barang
- **PurchaseRequest**: Dokumen pengajuan pembelian barang yang dibuat oleh satu divisi, berisi header dan daftar item barang
- **PurchaseRequest_Item**: Satu baris item barang dalam PurchaseRequest, berisi nama barang, satuan, jumlah, estimasi biaya, dan data pembelian aktual
- **PurchaseRequest_Log**: Catatan riwayat aksi perubahan status pada sebuah PurchaseRequest
- **Divisi**: Pengguna dengan role yang diawali `divisi_` (contoh: `divisi_it`, `divisi_finance`, `divisi_sarpras`)
- **Divisi_Pengaju**: Divisi yang membuat PurchaseRequest, diidentifikasi dari `role_name` pengguna pembuat
- **Bendahara_Umum**: Pengguna dengan role `bendahara_umum`, berwenang menyetujui/menolak PurchaseRequest pada tahap pertama
- **Ketua_Yayasan**: Pengguna dengan role `ketua_yayasan`, berwenang menyetujui/menolak PurchaseRequest pada tahap kedua
- **Divisi_Sarpras**: Pengguna dengan role `divisi_sarpras`, berwenang mengelola proses pembelian fisik setelah PurchaseRequest berstatus `APPROVED`
- **Nomor_Pengajuan**: Nomor unik PurchaseRequest dengan format `PB/YYYYMM/NNN`, sequence 3 digit reset tiap bulan, global lintas divisi
- **Status_PurchaseRequest**: Salah satu dari: `DRAFT`, `PENDING_BENDAHARA`, `REJECTED_BENDAHARA`, `PENDING_KETUA`, `REJECTED_KETUA`, `APPROVED`, `CANCELLED`, `PURCHASING`, `PARTIALLY_PURCHASED`, `COMPLETED`

---

## Requirements

### Requirement 1: Pembuatan Pengajuan Pembelian Barang

**User Story:** Sebagai anggota Divisi, saya ingin membuat pengajuan pembelian barang baru, agar kebutuhan barang divisi saya dapat diajukan untuk persetujuan dan diproses pembeliannya.

#### Acceptance Criteria

1. THE PurchaseRequest_System SHALL menyediakan form pembuatan PurchaseRequest dengan field: `judul_pengajuan` (teks, wajib), `keperluan` (teks panjang, opsional), `tanggal_dibutuhkan` (tanggal, wajib), dan minimal satu PurchaseRequest_Item.
2. WHEN pengguna dengan role `divisi_*` membuat PurchaseRequest, THE PurchaseRequest_System SHALL mengisi `divisi_id` secara otomatis dari `role_name` pengguna yang sedang login.
3. WHEN pengguna dengan role `divisi_*` membuat PurchaseRequest, THE PurchaseRequest_System SHALL mengisi `dibuat_oleh` secara otomatis dari `user_id` pengguna yang sedang login.
4. WHEN PurchaseRequest baru berhasil disimpan, THE PurchaseRequest_System SHALL menetapkan status awal PurchaseRequest sebagai `DRAFT`.
5. WHEN PurchaseRequest baru berhasil disimpan, THE PurchaseRequest_System SHALL menghasilkan `nomor_pengajuan` dengan format `PB/YYYYMM/NNN` menggunakan DB transaction dengan strategi MAX+1 untuk menjamin keunikan sequence per bulan secara global lintas divisi.
6. IF pengguna yang tidak memiliki role `divisi_*` mencoba membuat PurchaseRequest, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
7. IF form pembuatan PurchaseRequest dikirim tanpa PurchaseRequest_Item, THEN THE PurchaseRequest_System SHALL mengembalikan pesan validasi bahwa minimal satu item barang wajib diisi.
8. IF `biaya_estimasi` pada PurchaseRequest_Item diisi dengan nilai bukan numerik atau nilai negatif, THEN THE PurchaseRequest_System SHALL mengembalikan pesan validasi yang deskriptif.
9. IF `jumlah` pada PurchaseRequest_Item diisi dengan nilai bukan bilangan bulat positif, THEN THE PurchaseRequest_System SHALL mengembalikan pesan validasi bahwa jumlah harus berupa bilangan bulat lebih dari 0.

---

### Requirement 2: Pengelolaan Item Barang

**User Story:** Sebagai anggota Divisi, saya ingin menambah, mengubah, dan menghapus item barang dalam pengajuan, agar rincian kebutuhan barang dapat dikelola dengan akurat.

#### Acceptance Criteria

1. THE PurchaseRequest_System SHALL mendukung pengelolaan PurchaseRequest_Item dengan field: `nama_barang` (teks, wajib), `satuan` (teks, wajib, contoh: "pcs", "kg", "unit"), `jumlah` (integer, wajib, > 0), `biaya_estimasi` (desimal, wajib, ≥ 0, estimasi harga per satuan), dan `catatan_item` (teks panjang, opsional).
2. WHEN PurchaseRequest_Item ditambahkan, diubah, atau dihapus, THE PurchaseRequest_System SHALL memperbarui nilai `total_item` pada header PurchaseRequest sebagai jumlah total PurchaseRequest_Item yang dimiliki PurchaseRequest tersebut.
3. WHEN PurchaseRequest_Item ditambahkan, diubah, atau dihapus, THE PurchaseRequest_System SHALL memperbarui nilai `total_biaya_estimasi` pada header PurchaseRequest sebagai penjumlahan seluruh hasil perkalian `biaya_estimasi` × `jumlah` dari setiap PurchaseRequest_Item milik PurchaseRequest tersebut.
4. WHILE PurchaseRequest berstatus `DRAFT` atau `REJECTED_BENDAHARA` atau `REJECTED_KETUA`, THE PurchaseRequest_System SHALL mengizinkan Divisi_Pengaju untuk menambah, mengubah, dan menghapus PurchaseRequest_Item.
5. IF pengguna mencoba mengubah PurchaseRequest_Item pada PurchaseRequest yang berstatus selain `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 3: Pengajuan (Submit)

**User Story:** Sebagai anggota Divisi, saya ingin mengajukan PurchaseRequest yang sudah selesai dibuat, agar PurchaseRequest dapat diproses oleh Bendahara Umum.

#### Acceptance Criteria

1. WHEN Divisi_Pengaju mengajukan PurchaseRequest berstatus `DRAFT`, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `PENDING_BENDAHARA`.
2. WHEN Divisi_Pengaju mengajukan PurchaseRequest berstatus `REJECTED_BENDAHARA` atau `REJECTED_KETUA`, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `PENDING_BENDAHARA`.
3. WHEN PurchaseRequest berhasil diajukan, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `SUBMITTED`, `dilakukan_oleh` = user yang mengajukan, dan `created_at` = waktu pengajuan.
4. IF pengguna yang bukan Divisi_Pengaju mencoba mengajukan PurchaseRequest, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
5. IF Divisi_Pengaju mencoba mengajukan PurchaseRequest yang tidak berstatus `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.

---

### Requirement 4: Approval oleh Bendahara Umum

**User Story:** Sebagai Bendahara Umum, saya ingin menyetujui atau menolak pengajuan pembelian yang diajukan, agar proses persetujuan anggaran berjalan sesuai alur.

#### Acceptance Criteria

1. WHEN Bendahara_Umum menyetujui PurchaseRequest berstatus `PENDING_BENDAHARA`, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `PENDING_KETUA`.
2. WHEN Bendahara_Umum menolak PurchaseRequest berstatus `PENDING_BENDAHARA`, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `REJECTED_BENDAHARA`.
3. WHEN Bendahara_Umum menyetujui PurchaseRequest, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `APPROVED_BENDAHARA`, `dilakukan_oleh` = user Bendahara_Umum, dan `catatan` dari input opsional.
4. WHEN Bendahara_Umum menolak PurchaseRequest, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `REJECTED_BENDAHARA`, `dilakukan_oleh` = user Bendahara_Umum, dan `catatan` wajib diisi sebagai alasan penolakan.
5. IF pengguna yang tidak memiliki role `bendahara_umum` mencoba melakukan aksi approve/reject pada tahap Bendahara, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
6. IF Bendahara_Umum mencoba approve/reject PurchaseRequest yang tidak berstatus `PENDING_BENDAHARA`, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.
7. IF Bendahara_Umum menolak PurchaseRequest tanpa mengisi `catatan`, THEN THE PurchaseRequest_System SHALL mengembalikan pesan validasi bahwa catatan penolakan wajib diisi.

---

### Requirement 5: Approval oleh Ketua Yayasan

**User Story:** Sebagai Ketua Yayasan, saya ingin menyetujui atau menolak pengajuan pembelian yang sudah disetujui Bendahara Umum, agar keputusan akhir pembelian ada di tangan pimpinan.

#### Acceptance Criteria

1. WHEN Ketua_Yayasan menyetujui PurchaseRequest berstatus `PENDING_KETUA`, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `APPROVED`.
2. WHEN Ketua_Yayasan menolak PurchaseRequest berstatus `PENDING_KETUA`, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `REJECTED_KETUA`.
3. WHEN Ketua_Yayasan menyetujui PurchaseRequest, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `APPROVED_KETUA`, `dilakukan_oleh` = user Ketua_Yayasan, dan `catatan` dari input opsional.
4. WHEN Ketua_Yayasan menolak PurchaseRequest, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `REJECTED_KETUA`, `dilakukan_oleh` = user Ketua_Yayasan, dan `catatan` wajib diisi sebagai alasan penolakan.
5. IF pengguna yang tidak memiliki role `ketua_yayasan` mencoba melakukan aksi approve/reject pada tahap Ketua, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
6. IF Ketua_Yayasan mencoba approve/reject PurchaseRequest yang tidak berstatus `PENDING_KETUA`, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.
7. IF Ketua_Yayasan menolak PurchaseRequest tanpa mengisi `catatan`, THEN THE PurchaseRequest_System SHALL mengembalikan pesan validasi bahwa catatan penolakan wajib diisi.

---

### Requirement 6: Proses Pembelian oleh Divisi Sarpras

**User Story:** Sebagai anggota Divisi Sarpras, saya ingin mengelola proses pembelian fisik barang yang sudah disetujui, agar status kedatangan barang dan harga aktual dapat dicatat dengan akurat.

#### Acceptance Criteria

1. WHEN PurchaseRequest berstatus `APPROVED`, THE PurchaseRequest_System SHALL mengizinkan Divisi_Sarpras untuk memulai proses pembelian dengan mengubah status PurchaseRequest menjadi `PURCHASING`.
2. WHEN Divisi_Sarpras memulai proses pembelian, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `PURCHASING_STARTED`, `dilakukan_oleh` = user Divisi_Sarpras, dan `created_at` = waktu mulai pembelian.
3. WHILE PurchaseRequest berstatus `PURCHASING` atau `PARTIALLY_PURCHASED`, THE PurchaseRequest_System SHALL mengizinkan Divisi_Sarpras untuk menandai PurchaseRequest_Item sebagai sudah dibeli (`sudah_dibeli` = true) dan mengisi `harga_aktual` (harga total item yang sudah dibeli).
4. WHEN Divisi_Sarpras menandai satu atau lebih PurchaseRequest_Item sebagai sudah dibeli, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `ITEM_PURCHASED`, `dilakukan_oleh` = user Divisi_Sarpras, dan `catatan` berisi nama barang yang ditandai.
5. WHEN Divisi_Sarpras menandai PurchaseRequest_Item sebagai sudah dibeli namun masih ada item lain yang belum dibeli, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `PARTIALLY_PURCHASED`.
6. WHEN semua PurchaseRequest_Item pada PurchaseRequest telah ditandai sebagai sudah dibeli (`sudah_dibeli` = true), THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `COMPLETED` dan mencatat PurchaseRequest_Log dengan `aksi` = `COMPLETED`.
7. WHEN PurchaseRequest_Item ditandai sebagai sudah dibeli, THE PurchaseRequest_System SHALL memperbarui nilai `total_biaya_aktual` pada header PurchaseRequest sebagai penjumlahan seluruh `harga_aktual` dari PurchaseRequest_Item yang memiliki `sudah_dibeli` = true.
8. IF pengguna yang tidak memiliki role `divisi_sarpras` mencoba melakukan aksi proses pembelian, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
9. IF Divisi_Sarpras mencoba memulai proses pembelian pada PurchaseRequest yang tidak berstatus `APPROVED`, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.
10. IF `harga_aktual` diisi dengan nilai bukan numerik atau nilai negatif, THEN THE PurchaseRequest_System SHALL mengembalikan pesan validasi yang deskriptif.

---

### Requirement 7: Pembatalan Pengajuan

**User Story:** Sebagai anggota Divisi, saya ingin membatalkan pengajuan pembelian yang sudah dibuat, agar pengajuan yang tidak relevan tidak terus diproses.

#### Acceptance Criteria

1. WHILE PurchaseRequest berstatus selain `APPROVED`, `CANCELLED`, dan `COMPLETED`, THE PurchaseRequest_System SHALL menampilkan opsi Cancel kepada Divisi_Pengaju.
2. WHEN Divisi_Pengaju membatalkan PurchaseRequest, THE PurchaseRequest_System SHALL mengubah status PurchaseRequest menjadi `CANCELLED`.
3. WHEN PurchaseRequest berhasil dibatalkan, THE PurchaseRequest_System SHALL mencatat PurchaseRequest_Log dengan `aksi` = `CANCELLED`, `dilakukan_oleh` = user yang membatalkan, dan `created_at` = waktu pembatalan.
4. THE PurchaseRequest_System SHALL menjaga PurchaseRequest berstatus `CANCELLED` agar tidak dapat diubah statusnya kembali ke status apapun.
5. IF pengguna yang bukan Divisi_Pengaju mencoba membatalkan PurchaseRequest, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
6. IF Divisi_Pengaju mencoba membatalkan PurchaseRequest berstatus `APPROVED`, `CANCELLED`, atau `COMPLETED`, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.

---

### Requirement 8: Tampilan Daftar Pengajuan untuk Divisi

**User Story:** Sebagai anggota Divisi, saya ingin melihat daftar pengajuan pembelian dari divisi saya, agar saya dapat memantau status pengajuan barang divisi.

#### Acceptance Criteria

1. WHEN pengguna dengan role `divisi_*` mengakses halaman daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan hanya PurchaseRequest yang memiliki `divisi_id` sama dengan `role_name` pengguna yang sedang login.
2. THE PurchaseRequest_System SHALL menampilkan kolom: `nomor_pengajuan`, `judul_pengajuan`, `tanggal_dibutuhkan`, `total_item`, `total_biaya_estimasi`, `status`, dan aksi yang tersedia.
3. WHILE pengguna Divisi melihat daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan tombol Edit & Resubmit hanya pada PurchaseRequest berstatus `REJECTED_BENDAHARA` atau `REJECTED_KETUA`.
4. WHILE pengguna Divisi melihat daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan tombol Cancel hanya pada PurchaseRequest yang berstatus selain `APPROVED`, `CANCELLED`, dan `COMPLETED`.
5. IF pengguna yang tidak memiliki role `divisi_*` mengakses halaman daftar PurchaseRequest Divisi, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 9: Tampilan Daftar Pengajuan untuk Bendahara Umum, Ketua Yayasan, dan Divisi Sarpras

**User Story:** Sebagai Bendahara Umum, Ketua Yayasan, atau Divisi Sarpras, saya ingin melihat semua pengajuan pembelian dari seluruh divisi dengan kemampuan filter, agar proses review, approval, dan pembelian dapat dilakukan dengan efisien.

#### Acceptance Criteria

1. WHEN pengguna dengan role `bendahara_umum`, `ketua_yayasan`, atau `divisi_sarpras` mengakses halaman daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan seluruh PurchaseRequest dari semua divisi.
2. THE PurchaseRequest_System SHALL menyediakan filter daftar PurchaseRequest berdasarkan: `status`, `divisi_id`, dan `tanggal_dibutuhkan`.
3. THE PurchaseRequest_System SHALL menampilkan kolom: `nomor_pengajuan`, `divisi_id`, `judul_pengajuan`, `tanggal_dibutuhkan`, `total_item`, `total_biaya_estimasi`, `total_biaya_aktual`, `status`, dan aksi yang tersedia.
4. WHILE Bendahara_Umum melihat daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan tombol Approve dan Reject hanya pada PurchaseRequest berstatus `PENDING_BENDAHARA`.
5. WHILE Ketua_Yayasan melihat daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan tombol Approve dan Reject hanya pada PurchaseRequest berstatus `PENDING_KETUA`.
6. WHILE Divisi_Sarpras melihat daftar PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan tombol Proses Pembelian hanya pada PurchaseRequest berstatus `APPROVED`, `PURCHASING`, atau `PARTIALLY_PURCHASED`.
7. IF pengguna yang tidak memiliki role `bendahara_umum`, `ketua_yayasan`, atau `divisi_sarpras` mengakses halaman daftar PurchaseRequest ini, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 10: Halaman Detail Pengajuan

**User Story:** Sebagai pengguna yang berwenang, saya ingin melihat detail lengkap sebuah pengajuan pembelian beserta riwayat aksinya, agar saya dapat memahami konteks dan histori pengajuan.

#### Acceptance Criteria

1. WHEN pengguna yang berwenang mengakses halaman detail PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan seluruh data header PurchaseRequest dan daftar PurchaseRequest_Item beserta status pembelian masing-masing item.
2. THE PurchaseRequest_System SHALL menampilkan timeline PurchaseRequest_Log secara kronologis dari yang paling baru ke yang paling lama (descending), berisi: `aksi`, `dilakukan_oleh`, `catatan`, dan `created_at`.
3. WHILE pengguna Divisi mengakses detail PurchaseRequest, THE PurchaseRequest_System SHALL membatasi akses hanya pada PurchaseRequest yang `divisi_id`-nya sama dengan `role_name` pengguna tersebut.
4. IF pengguna Divisi mencoba mengakses detail PurchaseRequest milik divisi lain, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
5. IF PurchaseRequest dengan ID yang diminta tidak ditemukan, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 404.
6. WHILE PurchaseRequest berstatus `PURCHASING` atau `PARTIALLY_PURCHASED`, THE PurchaseRequest_System SHALL menampilkan kolom `sudah_dibeli` dan `harga_aktual` pada setiap PurchaseRequest_Item di halaman detail.

---

### Requirement 11: Penomoran Pengajuan Otomatis

**User Story:** Sebagai sistem, saya ingin menghasilkan nomor pengajuan yang unik dan berurutan, agar setiap PurchaseRequest dapat diidentifikasi dengan jelas.

#### Acceptance Criteria

1. WHEN PurchaseRequest baru dibuat, THE PurchaseRequest_System SHALL menghasilkan `nomor_pengajuan` dengan format `PB/YYYYMM/NNN` di mana `YYYYMM` adalah tahun dan bulan saat pengajuan dibuat, dan `NNN` adalah sequence 3 digit yang dimulai dari `001`.
2. THE PurchaseRequest_System SHALL menghitung sequence dengan strategi MAX+1 dari seluruh PurchaseRequest pada bulan yang sama, dieksekusi dalam DB transaction untuk mencegah race condition.
3. THE PurchaseRequest_System SHALL mereset sequence kembali ke `001` pada setiap pergantian bulan (bulan baru tidak melanjutkan sequence bulan sebelumnya).
4. THE PurchaseRequest_System SHALL menjamin keunikan `nomor_pengajuan` secara global lintas seluruh divisi.
5. IF terjadi kegagalan dalam proses generate `nomor_pengajuan` akibat konflik transaksi, THEN THE PurchaseRequest_System SHALL melakukan rollback dan mengembalikan pesan error yang deskriptif.

---

### Requirement 12: Visibilitas Data dan Otorisasi Berbasis Role

**User Story:** Sebagai sistem, saya ingin memastikan setiap pengguna hanya dapat mengakses data yang sesuai dengan role-nya, agar keamanan dan privasi data antar divisi terjaga.

#### Acceptance Criteria

1. WHEN pengguna dengan role `divisi_*` (kecuali `divisi_sarpras` dalam kapasitas approver) mengakses data PurchaseRequest, THE PurchaseRequest_System SHALL membatasi data yang ditampilkan hanya pada PurchaseRequest dengan `divisi_id` yang sama dengan `role_name` pengguna tersebut.
2. WHEN pengguna dengan role `bendahara_umum`, `ketua_yayasan`, atau `divisi_sarpras` mengakses data PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan seluruh PurchaseRequest dari semua divisi.
3. THE PurchaseRequest_System SHALL menggunakan permission `purchase-request-read` untuk mengontrol akses baca, `purchase-request-create` untuk akses buat, `purchase-request-update` untuk akses ubah, `purchase-request-cancel` untuk akses batalkan, dan `purchase-request-approve` untuk akses approve/reject.
4. IF pengguna mengakses endpoint PurchaseRequest tanpa permission yang sesuai, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 13: Dashboard Pengajuan Pembelian Barang

**User Story:** Sebagai pengguna yang berwenang, saya ingin melihat dashboard ringkasan data pengajuan pembelian barang, agar saya dapat memantau status pengajuan, total biaya estimasi, dan total biaya aktual secara cepat dan informatif.

#### Acceptance Criteria

**13.1 — Halaman Dashboard**

1. THE PurchaseRequest_System SHALL menyediakan halaman Dashboard_PurchaseRequest yang dapat diakses melalui route `GET /purchase-requests/dashboard`.
2. WHEN pengguna mengakses halaman Dashboard_PurchaseRequest, THE PurchaseRequest_System SHALL memverifikasi bahwa pengguna memiliki permission `purchase-request-read` sebelum menampilkan halaman.
3. IF pengguna tidak memiliki permission `purchase-request-read` mengakses halaman Dashboard_PurchaseRequest, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
4. THE PurchaseRequest_System SHALL menampilkan breadcrumb `['Pengajuan Pembelian Barang', 'Dashboard']` dan judul halaman `'Dashboard Pengajuan Pembelian Barang'` pada halaman Dashboard_PurchaseRequest.
5. WHEN pengguna dengan role `divisi_*` (kecuali `divisi_sarpras`) mengakses halaman Dashboard_PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan data hanya untuk divisi pengguna tersebut tanpa menampilkan filter divisi.
6. WHEN Privileged_User mengakses halaman Dashboard_PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan filter divisi tambahan yang memungkinkan pemilihan satu atau semua divisi.

**13.2 — Summary Cards**

7. THE PurchaseRequest_System SHALL menampilkan Summary_Card jumlah total PurchaseRequest dalam periode yang dipilih.
8. THE PurchaseRequest_System SHALL menampilkan Summary_Card jumlah PurchaseRequest yang sedang dalam proses approval (berstatus `PENDING_BENDAHARA` atau `PENDING_KETUA`) dalam periode yang dipilih.
9. THE PurchaseRequest_System SHALL menampilkan Summary_Card jumlah PurchaseRequest yang sedang dalam proses pembelian (berstatus `PURCHASING` atau `PARTIALLY_PURCHASED`) dalam periode yang dipilih.
10. THE PurchaseRequest_System SHALL menampilkan Summary_Card jumlah PurchaseRequest berstatus `COMPLETED` dalam periode yang dipilih.
11. THE PurchaseRequest_System SHALL menampilkan Summary_Card total nilai `total_biaya_estimasi` (sum dari seluruh PurchaseRequest) dalam periode yang dipilih, diformat sebagai mata uang Rupiah.
12. THE PurchaseRequest_System SHALL menampilkan Summary_Card total nilai `total_biaya_aktual` (sum dari seluruh PurchaseRequest) dalam periode yang dipilih, diformat sebagai mata uang Rupiah.
13. WHEN pengguna mengubah nilai filter periode atau divisi, THE PurchaseRequest_System SHALL memperbarui semua Summary_Card secara dinamis melalui AJAX tanpa reload halaman penuh.
14. WHILE data Summary_Card sedang dimuat, THE PurchaseRequest_System SHALL menampilkan indikator loading pada setiap Summary_Card.

**13.3 — Filter Periode**

15. THE PurchaseRequest_System SHALL menyediakan filter tahun pada Dashboard_PurchaseRequest dengan pilihan tahun yang tersedia berdasarkan data PurchaseRequest yang ada di database, ditambah tahun berjalan.
16. THE PurchaseRequest_System SHALL menyediakan filter bulan pada Dashboard_PurchaseRequest dengan pilihan bulan 1–12 ditambah opsi "Semua Bulan".
17. WHEN pengguna memilih tahun tanpa memilih bulan spesifik, THE PurchaseRequest_System SHALL menampilkan data agregat untuk seluruh bulan dalam tahun tersebut.
18. WHEN pengguna memilih tahun dan bulan tertentu, THE PurchaseRequest_System SHALL menampilkan data hanya untuk bulan tersebut berdasarkan field `created_at` PurchaseRequest.
19. THE PurchaseRequest_System SHALL menggunakan tahun berjalan sebagai nilai default filter tahun saat halaman pertama kali dimuat.
20. THE PurchaseRequest_System SHALL menggunakan "Semua Bulan" sebagai nilai default filter bulan saat halaman pertama kali dimuat.

**13.4 — Filter Divisi (Privileged User)**

21. WHEN Privileged_User mengakses Dashboard_PurchaseRequest, THE PurchaseRequest_System SHALL menampilkan dropdown filter divisi yang berisi daftar seluruh divisi yang memiliki data PurchaseRequest, ditambah opsi "Semua Divisi".
22. WHEN Privileged_User memilih "Semua Divisi", THE PurchaseRequest_System SHALL menampilkan data agregat dari seluruh divisi.
23. WHEN Privileged_User memilih satu divisi tertentu, THE PurchaseRequest_System SHALL menampilkan data hanya untuk divisi tersebut.
24. THE PurchaseRequest_System SHALL menggunakan "Semua Divisi" sebagai nilai default filter divisi saat halaman pertama kali dimuat oleh Privileged_User.
25. WHILE pengguna dengan role `divisi_*` (kecuali `divisi_sarpras`) mengakses Dashboard_PurchaseRequest, THE PurchaseRequest_System SHALL menyembunyikan filter divisi dan secara otomatis membatasi data pada divisi pengguna tersebut.

**13.5 — API Endpoint Data Dashboard**

26. THE PurchaseRequest_System SHALL menyediakan endpoint `GET /api/v1/purchase-requests/dashboard/summary` yang mengembalikan data Summary_Card dalam format JSON.
27. THE PurchaseRequest_System SHALL menyediakan endpoint `GET /api/v1/purchase-requests/dashboard/chart` yang mengembalikan data untuk semua chart dalam format JSON.
28. WHEN endpoint dashboard dipanggil dengan parameter `tahun` (integer, wajib), `bulan` (integer 1–12, opsional), dan `divisi_id` (string, opsional), THE PurchaseRequest_System SHALL memfilter data sesuai parameter tersebut.
29. WHEN pengguna dengan role `divisi_*` (kecuali `divisi_sarpras`) memanggil endpoint dashboard, THE PurchaseRequest_System SHALL mengabaikan parameter `divisi_id` dan selalu memfilter data berdasarkan divisi pengguna tersebut.
30. IF parameter `tahun` tidak diberikan atau tidak valid, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 422 dengan pesan validasi yang deskriptif.
31. THE PurchaseRequest_System SHALL mengembalikan response summary dalam format:
    ```json
    {
      "total_pengajuan": 10,
      "total_dalam_approval": 3,
      "total_dalam_pembelian": 2,
      "total_selesai": 4,
      "total_biaya_estimasi": 75000000,
      "total_biaya_estimasi_formatted": "75.000.000",
      "total_biaya_aktual": 60000000,
      "total_biaya_aktual_formatted": "60.000.000"
    }
    ```

**13.6 — Export Excel**

32. THE PurchaseRequest_System SHALL menyediakan endpoint `GET /purchase-requests/export` yang menghasilkan file export dalam format `.xlsx`.
33. WHEN pengguna mengakses endpoint export, THE PurchaseRequest_System SHALL memverifikasi bahwa pengguna memiliki permission `purchase-request-read` sebelum memproses export.
34. IF pengguna tidak memiliki permission `purchase-request-read` mengakses endpoint export, THEN THE PurchaseRequest_System SHALL mengembalikan respons HTTP 403.
35. THE PurchaseRequest_System SHALL menerima parameter filter `tahun` (integer, opsional), `bulan` (integer 1–12, opsional), dan `divisi_id` (string, opsional) pada endpoint export.
36. WHEN pengguna dengan role `divisi_*` (kecuali `divisi_sarpras`) melakukan export, THE PurchaseRequest_System SHALL mengabaikan parameter `divisi_id` dan selalu mengekspor hanya data PurchaseRequest milik divisi pengguna tersebut.
37. WHEN Privileged_User melakukan export tanpa parameter `divisi_id`, THE PurchaseRequest_System SHALL mengekspor data PurchaseRequest dari seluruh divisi.
38. THE PurchaseRequest_System SHALL memberi nama file export dengan format `PurchaseRequest_Export_YYYYMMDD_HHmmss.xlsx` menggunakan timestamp saat export dilakukan.
39. THE PurchaseRequest_System SHALL menghasilkan Sheet 1 pada file export dengan nama sheet `"Data Pengajuan"` yang berisi kolom: `No`, `Nomor Pengajuan`, `Divisi`, `Judul Pengajuan`, `Tanggal Dibutuhkan`, `Total Item`, `Total Biaya Estimasi`, `Total Biaya Aktual`, `Status`, `Dibuat Oleh`, `Tanggal Dibuat`.
40. THE PurchaseRequest_System SHALL menghasilkan Sheet 2 pada file export dengan nama sheet `"Item Barang"` yang berisi kolom: `No`, `Nomor Pengajuan`, `Divisi`, `Nama Barang`, `Satuan`, `Jumlah`, `Biaya Estimasi`, `Sudah Dibeli`, `Harga Aktual`.
41. THE PurchaseRequest_System SHALL memformat kolom `Total Biaya Estimasi`, `Total Biaya Aktual`, `Biaya Estimasi`, dan `Harga Aktual` pada kedua sheet sebagai angka dengan pemisah ribuan (format angka Excel, bukan teks).
42. THE PurchaseRequest_System SHALL mengisi kolom `Status` pada Sheet 1 menggunakan label status yang dapat dibaca manusia (bukan nilai enum mentah).
43. THE PurchaseRequest_System SHALL mengurutkan data pada Sheet 1 berdasarkan `nomor_pengajuan` secara ascending, dan data pada Sheet 2 berdasarkan `nomor_pengajuan` ascending kemudian urutan item dalam PurchaseRequest tersebut.
44. IF tidak ada data PurchaseRequest yang sesuai dengan filter yang diberikan, THEN THE PurchaseRequest_System SHALL tetap menghasilkan file export yang valid dengan hanya baris header tanpa data.

**13.7 — Kontrol Akses Export Berbasis Role**

45. WHEN pengguna dengan role `divisi_*` (kecuali `divisi_sarpras`) melakukan export, THE PurchaseRequest_System SHALL memastikan file export hanya berisi data PurchaseRequest dengan `divisi_id` yang sama dengan role pengguna tersebut.
46. WHEN Privileged_User melakukan export dengan filter `divisi_id` tertentu, THE PurchaseRequest_System SHALL memastikan file export hanya berisi data PurchaseRequest dari divisi yang dipilih.
47. WHEN Privileged_User melakukan export tanpa filter `divisi_id`, THE PurchaseRequest_System SHALL memastikan file export berisi data PurchaseRequest dari seluruh divisi.
48. THE PurchaseRequest_System SHALL menerapkan filter periode (tahun/bulan) pada export secara konsisten dengan filter yang diterapkan pada tampilan dashboard.
