# Requirements Document

## Introduction

Fitur RAB (Rencana Anggaran Biaya) adalah modul pencatatan dan persetujuan anggaran kegiatan per divisi. Setiap divisi dapat membuat RAB yang berisi daftar kegiatan beserta biaya yang direncanakan. RAB melewati alur approval dua tingkat: Bendahara Umum lalu Ketua Yayasan. Sistem ini dibangun di atas Laravel 12 dengan Spatie Permission untuk manajemen role, mengikuti pola MasterController dan Service yang sudah ada.

## Glossary

- **RAB_System**: Sistem pencatatan Rencana Anggaran Biaya
- **RAB**: Dokumen Rencana Anggaran Biaya yang dibuat oleh satu divisi untuk satu periode bulan
- **RAB_Item**: Satu baris kegiatan dalam RAB, berisi nama kegiatan, catatan, biaya, dan waktu pelaksanaan
- **RAB_Log**: Catatan riwayat aksi approval/perubahan status pada sebuah RAB
- **Divisi**: Pengguna dengan role yang diawali `divisi_` (contoh: `divisi_it`, `divisi_finance`)
- **Bendahara_Umum**: Pengguna dengan role `bendahara_umum`, berwenang menyetujui/menolak RAB pada tahap pertama
- **Ketua_Yayasan**: Pengguna dengan role `ketua_yayasan`, berwenang menyetujui/menolak RAB pada tahap kedua
- **Nomor_RAB**: Nomor unik RAB dengan format `RAB/YYYYMM/NNN`, sequence 3 digit reset tiap bulan, global lintas divisi
- **Status_RAB**: Salah satu dari: `DRAFT`, `PENDING_BENDAHARA`, `REJECTED_BENDAHARA`, `PENDING_KETUA`, `REJECTED_KETUA`, `APPROVED`, `CANCELLED`
- **Divisi_Pengaju**: Divisi yang membuat RAB, diidentifikasi dari `role_name` pengguna pembuat

---

## Requirements

### Requirement 1: Pembuatan RAB

**User Story:** Sebagai anggota Divisi, saya ingin membuat RAB baru, agar kegiatan dan anggaran divisi saya dapat diajukan untuk persetujuan.

#### Acceptance Criteria

1. THE RAB_System SHALL menyediakan form pembuatan RAB dengan field: `bulan_pengajuan` (bulan & tahun), dan minimal satu RAB_Item.
2. WHEN pengguna dengan role `divisi_*` membuat RAB, THE RAB_System SHALL mengisi `divisi_id` secara otomatis dari `role_name` pengguna yang sedang login.
3. WHEN pengguna dengan role `divisi_*` membuat RAB, THE RAB_System SHALL mengisi `dibuat_oleh` secara otomatis dari `user_id` pengguna yang sedang login.
4. WHEN RAB baru berhasil disimpan, THE RAB_System SHALL menetapkan status awal RAB sebagai `DRAFT`.
5. WHEN RAB baru berhasil disimpan, THE RAB_System SHALL menghasilkan `nomor_rab` dengan format `RAB/YYYYMM/NNN` menggunakan DB transaction dengan strategi MAX+1 untuk menjamin keunikan sequence per bulan secara global lintas divisi.
6. IF pengguna yang tidak memiliki role `divisi_*` mencoba membuat RAB, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
7. IF form pembuatan RAB dikirim tanpa RAB_Item, THEN THE RAB_System SHALL mengembalikan pesan validasi bahwa minimal satu item kegiatan wajib diisi.
8. IF `biaya_anggaran` pada RAB_Item diisi dengan nilai bukan numerik atau nilai negatif, THEN THE RAB_System SHALL mengembalikan pesan validasi yang deskriptif.

---

### Requirement 2: Pengelolaan Item RAB

**User Story:** Sebagai anggota Divisi, saya ingin menambah, mengubah, dan menghapus item kegiatan dalam RAB, agar rincian anggaran dapat dikelola dengan akurat.

#### Acceptance Criteria

1. THE RAB_System SHALL mendukung pengelolaan RAB_Item dengan field: `kegiatan` (teks wajib), `catatan_kegiatan` (rich text via Summernote CDN, opsional), `biaya_anggaran` (desimal, wajib, ≥ 0), dan `waktu_pelaksanaan` (string, wajib).
2. WHEN RAB_Item ditambahkan atau diubah, THE RAB_System SHALL memperbarui nilai `total_kegiatan` pada header RAB sebagai jumlah total RAB_Item yang dimiliki RAB tersebut.
3. WHEN RAB_Item ditambahkan atau diubah, THE RAB_System SHALL memperbarui nilai `total_biaya_anggaran` pada header RAB sebagai penjumlahan seluruh `biaya_anggaran` RAB_Item milik RAB tersebut.
4. WHILE RAB berstatus `DRAFT` atau `REJECTED_BENDAHARA` atau `REJECTED_KETUA`, THE RAB_System SHALL mengizinkan Divisi pengaju untuk menambah, mengubah, dan menghapus RAB_Item.
5. IF pengguna mencoba mengubah RAB_Item pada RAB yang berstatus selain `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 3: Pengajuan RAB (Submit)

**User Story:** Sebagai anggota Divisi, saya ingin mengajukan RAB yang sudah selesai dibuat, agar RAB dapat diproses oleh Bendahara Umum.

#### Acceptance Criteria

1. WHEN Divisi pengaju mengajukan RAB berstatus `DRAFT`, THE RAB_System SHALL mengubah status RAB menjadi `PENDING_BENDAHARA`.
2. WHEN Divisi pengaju mengajukan RAB berstatus `REJECTED_BENDAHARA` atau `REJECTED_KETUA`, THE RAB_System SHALL mengubah status RAB menjadi `PENDING_BENDAHARA`.
3. WHEN RAB berhasil diajukan, THE RAB_System SHALL mencatat RAB_Log dengan `aksi` = `SUBMITTED`, `dilakukan_oleh` = user yang mengajukan, dan `created_at` = waktu pengajuan.
4. IF pengguna yang bukan Divisi pengaju mencoba mengajukan RAB, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
5. IF Divisi pengaju mencoba mengajukan RAB yang tidak berstatus `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`, THEN THE RAB_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.

---

### Requirement 4: Approval oleh Bendahara Umum

**User Story:** Sebagai Bendahara Umum, saya ingin menyetujui atau menolak RAB yang diajukan, agar proses persetujuan anggaran berjalan sesuai alur.

#### Acceptance Criteria

1. WHEN Bendahara_Umum menyetujui RAB berstatus `PENDING_BENDAHARA`, THE RAB_System SHALL mengubah status RAB menjadi `PENDING_KETUA`.
2. WHEN Bendahara_Umum menolak RAB berstatus `PENDING_BENDAHARA`, THE RAB_System SHALL mengubah status RAB menjadi `REJECTED_BENDAHARA`.
3. WHEN Bendahara_Umum menyetujui RAB, THE RAB_System SHALL mencatat RAB_Log dengan `aksi` = `APPROVED_BENDAHARA`, `dilakukan_oleh` = user Bendahara_Umum, dan `catatan` dari input opsional.
4. WHEN Bendahara_Umum menolak RAB, THE RAB_System SHALL mencatat RAB_Log dengan `aksi` = `REJECTED_BENDAHARA`, `dilakukan_oleh` = user Bendahara_Umum, dan `catatan` wajib diisi sebagai alasan penolakan.
5. IF pengguna yang tidak memiliki role `bendahara_umum` mencoba melakukan aksi approve/reject pada tahap Bendahara, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
6. IF Bendahara_Umum mencoba approve/reject RAB yang tidak berstatus `PENDING_BENDAHARA`, THEN THE RAB_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.
7. IF Bendahara_Umum menolak RAB tanpa mengisi `catatan`, THEN THE RAB_System SHALL mengembalikan pesan validasi bahwa catatan penolakan wajib diisi.

---

### Requirement 5: Approval oleh Ketua Yayasan

**User Story:** Sebagai Ketua Yayasan, saya ingin menyetujui atau menolak RAB yang sudah disetujui Bendahara Umum, agar keputusan akhir anggaran ada di tangan pimpinan.

#### Acceptance Criteria

1. WHEN Ketua_Yayasan menyetujui RAB berstatus `PENDING_KETUA`, THE RAB_System SHALL mengubah status RAB menjadi `APPROVED`.
2. WHEN Ketua_Yayasan menolak RAB berstatus `PENDING_KETUA`, THE RAB_System SHALL mengubah status RAB menjadi `REJECTED_KETUA`.
3. WHEN Ketua_Yayasan menyetujui RAB, THE RAB_System SHALL mencatat RAB_Log dengan `aksi` = `APPROVED_KETUA`, `dilakukan_oleh` = user Ketua_Yayasan, dan `catatan` dari input opsional.
4. WHEN Ketua_Yayasan menolak RAB, THE RAB_System SHALL mencatat RAB_Log dengan `aksi` = `REJECTED_KETUA`, `dilakukan_oleh` = user Ketua_Yayasan, dan `catatan` wajib diisi sebagai alasan penolakan.
5. IF pengguna yang tidak memiliki role `ketua_yayasan` mencoba melakukan aksi approve/reject pada tahap Ketua, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
6. IF Ketua_Yayasan mencoba approve/reject RAB yang tidak berstatus `PENDING_KETUA`, THEN THE RAB_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.
7. IF Ketua_Yayasan menolak RAB tanpa mengisi `catatan`, THEN THE RAB_System SHALL mengembalikan pesan validasi bahwa catatan penolakan wajib diisi.

---

### Requirement 6: Pembatalan RAB

**User Story:** Sebagai anggota Divisi, saya ingin membatalkan RAB yang sudah dibuat, agar RAB yang tidak relevan tidak terus diproses.

#### Acceptance Criteria

1. WHILE RAB berstatus selain `APPROVED` dan selain `CANCELLED`, THE RAB_System SHALL menampilkan tombol Cancel kepada Divisi pengaju.
2. WHEN Divisi pengaju membatalkan RAB, THE RAB_System SHALL mengubah status RAB menjadi `CANCELLED`.
3. WHEN RAB berhasil dibatalkan, THE RAB_System SHALL mencatat RAB_Log dengan `aksi` = `CANCELLED`, `dilakukan_oleh` = user yang membatalkan, dan `created_at` = waktu pembatalan.
4. THE RAB_System SHALL menjaga RAB berstatus `CANCELLED` agar tidak dapat diubah statusnya kembali ke status apapun.
5. IF pengguna yang bukan Divisi pengaju mencoba membatalkan RAB, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
6. IF Divisi pengaju mencoba membatalkan RAB berstatus `APPROVED` atau `CANCELLED`, THEN THE RAB_System SHALL mengembalikan respons HTTP 422 dengan pesan yang deskriptif.

---

### Requirement 7: Tampilan Daftar RAB untuk Divisi

**User Story:** Sebagai anggota Divisi, saya ingin melihat daftar RAB milik divisi saya, agar saya dapat memantau status pengajuan anggaran divisi.

#### Acceptance Criteria

1. WHEN pengguna dengan role `divisi_*` mengakses halaman daftar RAB, THE RAB_System SHALL menampilkan hanya RAB yang memiliki `divisi_id` sama dengan `role_name` pengguna yang sedang login.
2. THE RAB_System SHALL menampilkan kolom: `nomor_rab`, `bulan_pengajuan`, `total_kegiatan`, `total_biaya_anggaran`, `status`, dan aksi yang tersedia.
3. WHILE pengguna Divisi melihat daftar RAB, THE RAB_System SHALL menampilkan tombol Edit & Resubmit hanya pada RAB berstatus `REJECTED_BENDAHARA` atau `REJECTED_KETUA`.
4. WHILE pengguna Divisi melihat daftar RAB, THE RAB_System SHALL menampilkan tombol Cancel hanya pada RAB yang berstatus selain `APPROVED` dan selain `CANCELLED`.
5. IF pengguna yang tidak memiliki role `divisi_*` mengakses halaman daftar RAB Divisi, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 8: Tampilan Daftar RAB untuk Bendahara Umum dan Ketua Yayasan

**User Story:** Sebagai Bendahara Umum atau Ketua Yayasan, saya ingin melihat semua RAB dari seluruh divisi dengan kemampuan filter, agar proses review dan approval dapat dilakukan dengan efisien.

#### Acceptance Criteria

1. WHEN pengguna dengan role `bendahara_umum` atau `ketua_yayasan` mengakses halaman daftar RAB, THE RAB_System SHALL menampilkan seluruh RAB dari semua divisi.
2. THE RAB_System SHALL menyediakan filter daftar RAB berdasarkan: `status`, `divisi_id`, dan `bulan_pengajuan`.
3. THE RAB_System SHALL menampilkan kolom: `nomor_rab`, `divisi_id`, `bulan_pengajuan`, `total_kegiatan`, `total_biaya_anggaran`, `status`, dan aksi yang tersedia.
4. WHILE Bendahara_Umum melihat daftar RAB, THE RAB_System SHALL menampilkan tombol Approve dan Reject hanya pada RAB berstatus `PENDING_BENDAHARA`.
5. WHILE Ketua_Yayasan melihat daftar RAB, THE RAB_System SHALL menampilkan tombol Approve dan Reject hanya pada RAB berstatus `PENDING_KETUA`.
6. IF pengguna yang tidak memiliki role `bendahara_umum` atau `ketua_yayasan` mengakses halaman daftar RAB ini, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.

---

### Requirement 9: Halaman Detail RAB

**User Story:** Sebagai pengguna yang berwenang, saya ingin melihat detail lengkap sebuah RAB beserta riwayat approval-nya, agar saya dapat memahami konteks dan histori pengajuan.

#### Acceptance Criteria

1. WHEN pengguna yang berwenang mengakses halaman detail RAB, THE RAB_System SHALL menampilkan seluruh data header RAB dan daftar RAB_Item.
2. THE RAB_System SHALL menampilkan timeline RAB_Log secara kronologis dari yang paling lama ke yang paling baru, berisi: `aksi`, `dilakukan_oleh`, `catatan`, dan `created_at`.
3. WHILE pengguna Divisi mengakses detail RAB, THE RAB_System SHALL membatasi akses hanya pada RAB yang `divisi_id`-nya sama dengan `role_name` pengguna tersebut.
4. IF pengguna Divisi mencoba mengakses detail RAB milik divisi lain, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
5. IF RAB dengan ID yang diminta tidak ditemukan, THEN THE RAB_System SHALL mengembalikan respons HTTP 404.

---

### Requirement 10: Penomoran RAB Otomatis

**User Story:** Sebagai sistem, saya ingin menghasilkan nomor RAB yang unik dan berurutan, agar setiap RAB dapat diidentifikasi dengan jelas.

#### Acceptance Criteria

1. WHEN RAB baru dibuat, THE RAB_System SHALL menghasilkan `nomor_rab` dengan format `RAB/YYYYMM/NNN` di mana `YYYYMM` adalah tahun dan bulan dari `bulan_pengajuan`, dan `NNN` adalah sequence 3 digit yang dimulai dari `001`.
2. THE RAB_System SHALL menghitung sequence dengan strategi MAX+1 dari seluruh RAB pada bulan yang sama, dieksekusi dalam DB transaction untuk mencegah race condition.
3. THE RAB_System SHALL mereset sequence kembali ke `001` pada setiap pergantian bulan (bulan baru tidak melanjutkan sequence bulan sebelumnya).
4. THE RAB_System SHALL menjamin keunikan `nomor_rab` secara global lintas seluruh divisi.
5. IF terjadi kegagalan dalam proses generate `nomor_rab` akibat konflik transaksi, THEN THE RAB_System SHALL melakukan rollback dan mengembalikan pesan error yang deskriptif.

---

### Requirement 11: Integrasi Summernote untuk Rich Text

**User Story:** Sebagai anggota Divisi, saya ingin mengisi catatan kegiatan dengan format teks kaya, agar informasi kegiatan dapat disajikan dengan lebih jelas dan terstruktur.

#### Acceptance Criteria

1. THE RAB_System SHALL mengintegrasikan editor Summernote via CDN pada field `catatan_kegiatan` di form RAB_Item.
2. WHEN halaman form RAB_Item dimuat, THE RAB_System SHALL menginisialisasi Summernote pada field `catatan_kegiatan`.
3. THE RAB_System SHALL menyimpan konten `catatan_kegiatan` sebagai HTML yang dihasilkan oleh Summernote.
4. WHEN halaman detail RAB ditampilkan, THE RAB_System SHALL merender konten HTML `catatan_kegiatan` sebagai rich text (bukan plain text).
5. IF Summernote gagal dimuat dari CDN, THE RAB_System SHALL tetap menampilkan field `catatan_kegiatan` sebagai textarea biasa sehingga pengguna tetap dapat mengisi data.
