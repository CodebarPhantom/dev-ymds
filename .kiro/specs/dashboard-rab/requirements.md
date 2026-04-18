# Requirements Document

## Introduction

Fitur Dashboard RAB adalah halaman ringkasan (summary) data Rencana Anggaran Biaya yang menampilkan statistik kegiatan dan anggaran secara visual dan informatif. Dashboard menerapkan kontrol akses berbasis role: Bendahara Umum dan Ketua Yayasan dapat melihat data seluruh divisi, sedangkan role lain hanya melihat data divisi masing-masing. Fitur ini juga mencakup kemampuan export data RAB ke format Excel dengan dua sheet (header RAB dan item RAB), dapat difilter berdasarkan divisi dan periode.

Fitur dibangun di atas Laravel 12 dengan pola arsitektur yang sudah ada: `MasterController` + `callFunction()`, `MasterService`, Spatie Permission untuk otorisasi berbasis role, dan Maatwebsite Excel untuk export.

## Glossary

- **Dashboard_RAB**: Halaman ringkasan data RAB yang menampilkan statistik dan visualisasi
- **RAB_System**: Sistem pencatatan Rencana Anggaran Biaya
- **RAB**: Dokumen Rencana Anggaran Biaya yang dibuat oleh satu divisi untuk satu periode bulan
- **RAB_Item**: Satu baris kegiatan dalam RAB, berisi nama kegiatan, catatan, biaya, dan waktu pelaksanaan
- **Divisi**: Pengguna dengan role yang diawali `divisi_` (contoh: `divisi_it`, `divisi_finance`)
- **Bendahara_Umum**: Pengguna dengan role `bendahara_umum`, dapat melihat semua RAB lintas divisi
- **Ketua_Yayasan**: Pengguna dengan role `ketua_yayasan`, dapat melihat semua RAB lintas divisi
- **Super_Admin**: Pengguna dengan role `super_admin`, dapat melihat semua RAB lintas divisi
- **Privileged_User**: Pengguna dengan role `bendahara_umum`, `ketua_yayasan`, atau `super_admin`
- **Filter_Periode**: Kombinasi filter tahun dan/atau bulan untuk mempersempit rentang data yang ditampilkan
- **Filter_Divisi**: Filter berdasarkan `divisi_id` untuk mempersempit data per divisi (hanya tersedia untuk Privileged_User)
- **Summary_Card**: Komponen UI berupa kartu yang menampilkan satu metrik ringkasan (jumlah kegiatan, total anggaran, dsb.)
- **Export_RAB**: File Excel hasil export data RAB dengan dua sheet: Sheet 1 berisi header RAB, Sheet 2 berisi item-item RAB
- **DashboardRabService**: Service baru yang extend `MasterService`, menangani logika query dan kalkulasi dashboard serta export
- **RabExport**: Class Maatwebsite Excel yang menghasilkan file Export_RAB

---

## Requirements

### Requirement 1: Halaman Dashboard RAB

**User Story:** Sebagai pengguna yang berwenang, saya ingin melihat halaman dashboard RAB, agar saya dapat memantau ringkasan data anggaran secara cepat dan informatif.

#### Acceptance Criteria

1. THE RAB_System SHALL menyediakan halaman Dashboard_RAB yang dapat diakses melalui route `GET /rab/dashboard`.
2. WHEN pengguna mengakses halaman Dashboard_RAB, THE RAB_System SHALL memverifikasi bahwa pengguna memiliki permission `rab-read` sebelum menampilkan halaman.
3. IF pengguna tidak memiliki permission `rab-read` mengakses halaman Dashboard_RAB, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
4. THE RAB_System SHALL menampilkan breadcrumb `['RAB', 'Dashboard']` dan judul halaman `'Dashboard RAB'` pada halaman Dashboard_RAB.
5. THE RAB_System SHALL menampilkan komponen filter pada halaman Dashboard_RAB yang memungkinkan pengguna memilih tahun dan bulan.
6. WHEN pengguna dengan role `divisi_*` mengakses halaman Dashboard_RAB, THE RAB_System SHALL menampilkan data hanya untuk divisi pengguna tersebut tanpa menampilkan filter divisi.
7. WHEN Privileged_User mengakses halaman Dashboard_RAB, THE RAB_System SHALL menampilkan filter divisi tambahan yang memungkinkan pemilihan satu atau semua divisi.

---

### Requirement 2: Summary Card Statistik RAB

**User Story:** Sebagai pengguna yang berwenang, saya ingin melihat ringkasan statistik RAB dalam bentuk kartu, agar saya dapat memahami kondisi anggaran secara sekilas.

#### Acceptance Criteria

1. THE RAB_System SHALL menampilkan Summary_Card jumlah total RAB dalam periode yang dipilih.
2. THE RAB_System SHALL menampilkan Summary_Card jumlah total kegiatan (sum dari `total_kegiatan` seluruh RAB) dalam periode yang dipilih.
3. THE RAB_System SHALL menampilkan Summary_Card total nilai anggaran yang diajukan (sum dari `total_biaya_anggaran` seluruh RAB) dalam periode yang dipilih, diformat sebagai mata uang Rupiah.
4. THE RAB_System SHALL menampilkan Summary_Card jumlah RAB berstatus `APPROVED` dalam periode yang dipilih.
5. THE RAB_System SHALL menampilkan Summary_Card jumlah RAB berstatus `PENDING_BENDAHARA` atau `PENDING_KETUA` (sedang dalam proses approval) dalam periode yang dipilih.
6. WHEN pengguna mengubah nilai filter periode atau divisi, THE RAB_System SHALL memperbarui semua Summary_Card secara dinamis melalui AJAX tanpa reload halaman penuh.
7. WHILE data Summary_Card sedang dimuat, THE RAB_System SHALL menampilkan indikator loading pada setiap Summary_Card.

---

### Requirement 3: Visualisasi Chart RAB

**User Story:** Sebagai pengguna yang berwenang, saya ingin melihat visualisasi data RAB dalam bentuk chart, agar saya dapat memahami tren dan distribusi anggaran dengan lebih mudah.

#### Acceptance Criteria

1. THE RAB_System SHALL menampilkan chart distribusi status RAB (jumlah RAB per status) dalam periode yang dipilih.
2. THE RAB_System SHALL menampilkan chart tren total anggaran per bulan dalam tahun yang dipilih, menampilkan nilai `total_biaya_anggaran` untuk setiap bulan.
3. WHEN Privileged_User mengakses Dashboard_RAB, THE RAB_System SHALL menampilkan chart perbandingan total anggaran per divisi dalam periode yang dipilih.
4. WHEN pengguna mengubah nilai filter, THE RAB_System SHALL memperbarui semua chart secara dinamis melalui AJAX tanpa reload halaman penuh.
5. IF tidak ada data RAB untuk periode dan filter yang dipilih, THEN THE RAB_System SHALL menampilkan pesan informatif "Tidak ada data untuk periode ini" pada area chart dan Summary_Card.

---

### Requirement 4: Filter Berdasarkan Periode

**User Story:** Sebagai pengguna yang berwenang, saya ingin memfilter data dashboard berdasarkan tahun dan bulan, agar saya dapat menganalisis data anggaran pada periode tertentu.

#### Acceptance Criteria

1. THE RAB_System SHALL menyediakan filter tahun pada Dashboard_RAB dengan pilihan tahun yang tersedia berdasarkan data RAB yang ada di database, ditambah tahun berjalan.
2. THE RAB_System SHALL menyediakan filter bulan pada Dashboard_RAB dengan pilihan bulan 1–12 ditambah opsi "Semua Bulan".
3. WHEN pengguna memilih tahun tanpa memilih bulan spesifik, THE RAB_System SHALL menampilkan data agregat untuk seluruh bulan dalam tahun tersebut.
4. WHEN pengguna memilih tahun dan bulan tertentu, THE RAB_System SHALL menampilkan data hanya untuk bulan tersebut.
5. THE RAB_System SHALL menggunakan tahun berjalan sebagai nilai default filter tahun saat halaman pertama kali dimuat.
6. THE RAB_System SHALL menggunakan "Semua Bulan" sebagai nilai default filter bulan saat halaman pertama kali dimuat.

---

### Requirement 5: Filter Berdasarkan Divisi (Privileged User)

**User Story:** Sebagai Bendahara Umum atau Ketua Yayasan, saya ingin memfilter data dashboard berdasarkan divisi, agar saya dapat menganalisis anggaran per divisi secara terpisah.

#### Acceptance Criteria

1. WHEN Privileged_User mengakses Dashboard_RAB, THE RAB_System SHALL menampilkan dropdown filter divisi yang berisi daftar seluruh divisi yang memiliki data RAB, ditambah opsi "Semua Divisi".
2. WHEN Privileged_User memilih "Semua Divisi", THE RAB_System SHALL menampilkan data agregat dari seluruh divisi.
3. WHEN Privileged_User memilih satu divisi tertentu, THE RAB_System SHALL menampilkan data hanya untuk divisi tersebut.
4. THE RAB_System SHALL menggunakan "Semua Divisi" sebagai nilai default filter divisi saat halaman pertama kali dimuat oleh Privileged_User.
5. WHILE pengguna dengan role `divisi_*` mengakses Dashboard_RAB, THE RAB_System SHALL menyembunyikan filter divisi dan secara otomatis membatasi data pada divisi pengguna tersebut.

---

### Requirement 6: API Endpoint Data Dashboard

**User Story:** Sebagai sistem frontend, saya ingin mengambil data dashboard melalui API, agar tampilan dapat diperbarui secara dinamis tanpa reload halaman.

#### Acceptance Criteria

1. THE RAB_System SHALL menyediakan endpoint `GET /api/v1/rab/dashboard/summary` yang mengembalikan data Summary_Card dalam format JSON.
2. THE RAB_System SHALL menyediakan endpoint `GET /api/v1/rab/dashboard/chart` yang mengembalikan data untuk semua chart dalam format JSON.
3. WHEN endpoint dashboard dipanggil dengan parameter `tahun` (integer, wajib), `bulan` (integer 1–12, opsional), dan `divisi_id` (string, opsional), THE RAB_System SHALL memfilter data sesuai parameter tersebut.
4. WHEN pengguna dengan role `divisi_*` memanggil endpoint dashboard, THE RAB_System SHALL mengabaikan parameter `divisi_id` dan selalu memfilter data berdasarkan divisi pengguna tersebut.
5. IF parameter `tahun` tidak diberikan atau tidak valid, THEN THE RAB_System SHALL mengembalikan respons HTTP 422 dengan pesan validasi yang deskriptif.
6. THE RAB_System SHALL mengembalikan response summary dalam format:
   ```json
   {
     "total_rab": 10,
     "total_kegiatan": 45,
     "total_anggaran": 150000000,
     "total_anggaran_formatted": "150.000.000",
     "total_approved": 6,
     "total_pending": 3
   }
   ```

---

### Requirement 7: Export Excel RAB

**User Story:** Sebagai pengguna yang berwenang, saya ingin mengekspor data RAB ke file Excel, agar saya dapat membuat rekapan anggaran untuk keperluan pelaporan.

#### Acceptance Criteria

1. THE RAB_System SHALL menyediakan endpoint `GET /rab/export` yang menghasilkan file Export_RAB dalam format `.xlsx`.
2. WHEN pengguna mengakses endpoint export, THE RAB_System SHALL memverifikasi bahwa pengguna memiliki permission `rab-read` sebelum memproses export.
3. IF pengguna tidak memiliki permission `rab-read` mengakses endpoint export, THEN THE RAB_System SHALL mengembalikan respons HTTP 403.
4. THE RAB_System SHALL menerima parameter filter `tahun` (integer, opsional), `bulan` (integer 1–12, opsional), dan `divisi_id` (string, opsional) pada endpoint export.
5. WHEN pengguna dengan role `divisi_*` melakukan export, THE RAB_System SHALL mengabaikan parameter `divisi_id` dan selalu mengekspor hanya data RAB milik divisi pengguna tersebut.
6. WHEN Privileged_User melakukan export tanpa parameter `divisi_id`, THE RAB_System SHALL mengekspor data RAB dari seluruh divisi.
7. THE RAB_System SHALL memberi nama file export dengan format `RAB_Export_YYYYMMDD_HHmmss.xlsx` menggunakan timestamp saat export dilakukan.

---

### Requirement 8: Format Sheet 1 Export Excel (Header RAB)

**User Story:** Sebagai pengguna yang berwenang, saya ingin Sheet 1 file export berisi data header RAB yang terstruktur, agar saya dapat melihat ringkasan setiap RAB dengan mudah.

#### Acceptance Criteria

1. THE RAB_System SHALL menghasilkan Sheet 1 pada Export_RAB dengan nama sheet `"Data RAB"`.
2. THE RAB_System SHALL menampilkan baris header pada Sheet 1 dengan kolom: `No`, `Nomor RAB`, `Divisi`, `Bulan Pengajuan`, `Total Kegiatan`, `Total Anggaran`, `Status`, `Dibuat Oleh`, `Tanggal Dibuat`.
3. THE RAB_System SHALL mengisi setiap baris data pada Sheet 1 dengan nilai yang sesuai dari model `Rab`, di mana kolom `Status` menggunakan label status yang dapat dibaca manusia (bukan nilai enum mentah).
4. THE RAB_System SHALL memformat kolom `Total Anggaran` pada Sheet 1 sebagai angka dengan pemisah ribuan (format angka Excel, bukan teks).
5. THE RAB_System SHALL memformat kolom `Bulan Pengajuan` pada Sheet 1 sebagai teks dalam format `"Bulan YYYY"` (contoh: `"April 2026"`).
6. THE RAB_System SHALL mengurutkan data pada Sheet 1 berdasarkan `nomor_rab` secara ascending.

---

### Requirement 9: Format Sheet 2 Export Excel (Item RAB)

**User Story:** Sebagai pengguna yang berwenang, saya ingin Sheet 2 file export berisi rincian item-item RAB, agar saya dapat melihat detail kegiatan dan biaya dari setiap RAB.

#### Acceptance Criteria

1. THE RAB_System SHALL menghasilkan Sheet 2 pada Export_RAB dengan nama sheet `"Item RAB"`.
2. THE RAB_System SHALL menampilkan baris header pada Sheet 2 dengan kolom: `No`, `Nomor RAB`, `Divisi`, `Kegiatan`, `Catatan Kegiatan`, `Biaya Anggaran`, `Waktu Pelaksanaan`.
3. THE RAB_System SHALL mengisi setiap baris data pada Sheet 2 dengan nilai yang sesuai dari model `RabItem` beserta data RAB induknya.
4. THE RAB_System SHALL memformat kolom `Biaya Anggaran` pada Sheet 2 sebagai angka dengan pemisah ribuan (format angka Excel, bukan teks).
5. THE RAB_System SHALL mengisi kolom `Catatan Kegiatan` pada Sheet 2 sebagai teks plain (HTML tag dihilangkan menggunakan `strip_tags()`).
6. THE RAB_System SHALL mengurutkan data pada Sheet 2 berdasarkan `nomor_rab` ascending, kemudian berdasarkan urutan item dalam RAB tersebut.
7. THE RAB_System SHALL memastikan data pada Sheet 2 konsisten dengan data pada Sheet 1 — setiap RAB yang muncul di Sheet 1 harus memiliki item-itemnya di Sheet 2.

---

### Requirement 10: Kontrol Akses Export Berbasis Role

**User Story:** Sebagai sistem, saya ingin memastikan export RAB hanya menampilkan data yang sesuai dengan hak akses pengguna, agar kerahasiaan data anggaran per divisi terjaga.

#### Acceptance Criteria

1. WHEN pengguna dengan role `divisi_*` melakukan export, THE RAB_System SHALL memastikan file Export_RAB hanya berisi data RAB dengan `divisi_id` yang sama dengan role pengguna tersebut.
2. WHEN Privileged_User melakukan export dengan filter `divisi_id` tertentu, THE RAB_System SHALL memastikan file Export_RAB hanya berisi data RAB dari divisi yang dipilih.
3. WHEN Privileged_User melakukan export tanpa filter `divisi_id`, THE RAB_System SHALL memastikan file Export_RAB berisi data RAB dari seluruh divisi.
4. THE RAB_System SHALL menerapkan filter periode (tahun/bulan) pada export secara konsisten dengan filter yang diterapkan pada tampilan dashboard.
5. IF tidak ada data RAB yang sesuai dengan filter yang diberikan, THEN THE RAB_System SHALL tetap menghasilkan file Export_RAB yang valid dengan hanya baris header tanpa data.
