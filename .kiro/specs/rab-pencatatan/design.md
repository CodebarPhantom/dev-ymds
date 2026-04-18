# Design Document: RAB Pencatatan

## Overview

Fitur RAB (Rencana Anggaran Biaya) adalah modul pencatatan dan persetujuan anggaran kegiatan per divisi. Setiap divisi membuat RAB yang berisi daftar item kegiatan beserta biaya yang direncanakan. RAB melewati alur approval dua tingkat: Bendahara Umum → Ketua Yayasan.

Modul ini dibangun di atas pola arsitektur yang sudah ada: `MasterController` + `callFunction()`, `MasterService`, Spatie Permission untuk otorisasi berbasis role, Sanctum untuk API auth, dan Fortify untuk web auth.

### Tujuan Utama

- Divisi dapat membuat, mengedit, dan mengajukan RAB
- Bendahara Umum dapat menyetujui/menolak RAB pada tahap pertama
- Ketua Yayasan dapat menyetujui/menolak RAB pada tahap kedua
- Sistem menghasilkan nomor RAB unik berformat `RAB/YYYYMM/NNN`
- Audit trail lengkap via tabel `rab_approval_logs`

---

## Architecture

### Komponen Utama

```
┌─────────────────────────────────────────────────────────────┐
│                        Browser / Client                      │
└──────────────┬──────────────────────────┬───────────────────┘
               │ Web (Fortify session)     │ API (Sanctum token)
               ▼                           ▼
┌──────────────────────┐    ┌──────────────────────────────────┐
│  Web\Rab\RabController│    │  Api\V1\Rab\RabController        │
│  (render views only)  │    │  Api\V1\Rab\RabApprovalController│
└──────────┬───────────┘    └──────────────┬───────────────────┘
           │                               │
           └──────────────┬────────────────┘
                          ▼
               ┌──────────────────┐
               │    RabService    │
               │ (extend Master)  │
               └──────────┬───────┘
                          ▼
               ┌──────────────────┐
               │  Models: Rab,    │
               │  RabItem,        │
               │  RabApprovalLog  │
               └──────────┬───────┘
                          ▼
               ┌──────────────────┐
               │   PostgreSQL DB  │
               └──────────────────┘
```

### State Machine RAB

```
                    ┌─────────┐
                    │  DRAFT  │◄──────────────────────────┐
                    └────┬────┘                           │
                         │ submit()                       │
                         ▼                                │
              ┌──────────────────────┐                   │
              │  PENDING_BENDAHARA   │                   │
              └──────┬───────┬───────┘                   │
           approve() │       │ reject()                  │
                     ▼       ▼                           │
           ┌──────────────┐  ┌────────────────────┐     │
           │ PENDING_KETUA│  │ REJECTED_BENDAHARA  │─────┤ submit()
           └──────┬───┬───┘  └────────────────────┘     │
        approve() │   │ reject()                         │
                  ▼   ▼                                  │
           ┌──────────┐  ┌────────────────┐             │
           │ APPROVED │  │ REJECTED_KETUA │─────────────┘
           └──────────┘  └────────────────┘
                  ▲
                  │  cancel() tidak bisa dari APPROVED
                  │
           ┌──────────────┐
           │  CANCELLED   │ ← dari status apapun kecuali APPROVED & CANCELLED
           └──────────────┘
```

### Alur Generate Nomor RAB

```
store() dipanggil
    │
    ▼
DB::transaction() {
    SELECT MAX(nomor_rab) WHERE bulan = YYYYMM FOR UPDATE
    │
    ├── Tidak ada → sequence = 1
    └── Ada       → sequence = MAX_sequence + 1
    │
    ▼
    nomor_rab = "RAB/" + YYYYMM + "/" + str_pad(sequence, 3, '0', STR_PAD_LEFT)
    │
    ▼
    INSERT rabs (nomor_rab, ...)
}
```

---

## Components and Interfaces

### Directory Structure (file baru)

```
app/
├── Enums/
│   ├── RabStatus.php
│   └── RabAksiLog.php
├── Http/Controllers/
│   ├── Api/V1/Rab/
│   │   ├── RabController.php          (dataTable, submit, cancel)
│   │   └── RabApprovalController.php  (approve, reject)
│   └── Web/Rab/
│       └── RabController.php          (index, create, store, show, edit, update)
├── Models/
│   ├── Rab.php
│   ├── RabItem.php
│   └── RabApprovalLog.php
├── Policies/
│   └── RabPolicy.php
└── Services/
    └── RabService.php

database/migrations/
├── xxxx_create_rabs_table.php
├── xxxx_create_rab_items_table.php
└── xxxx_create_rab_approval_logs_table.php

resources/views/rab/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
└── show.blade.php
```

### Enums

**`App\Enums\RabStatus`**
```php
enum RabStatus: string {
    case DRAFT               = 'DRAFT';
    case PENDING_BENDAHARA   = 'PENDING_BENDAHARA';
    case REJECTED_BENDAHARA  = 'REJECTED_BENDAHARA';
    case PENDING_KETUA       = 'PENDING_KETUA';
    case REJECTED_KETUA      = 'REJECTED_KETUA';
    case APPROVED            = 'APPROVED';
    case CANCELLED           = 'CANCELLED';
}
```

**`App\Enums\RabAksiLog`**
```php
enum RabAksiLog: string {
    case SUBMITTED           = 'SUBMITTED';
    case APPROVED_BENDAHARA  = 'APPROVED_BENDAHARA';
    case REJECTED_BENDAHARA  = 'REJECTED_BENDAHARA';
    case APPROVED_KETUA      = 'APPROVED_KETUA';
    case REJECTED_KETUA      = 'REJECTED_KETUA';
    case CANCELLED           = 'CANCELLED';
}
```

### RabPolicy

Method yang disediakan:

| Method | Permission | Keterangan |
|---|---|---|
| `readPolicy` | `rab-read` | Semua role yang punya permission ini |
| `createPolicy` | `rab-create` | Hanya role `divisi_*` |
| `updatePolicy` | `rab-update` | Hanya role `divisi_*`, RAB milik divisi sendiri, status editable |
| `cancelPolicy` | `rab-cancel` | Hanya Divisi pengaju, status bukan APPROVED/CANCELLED |
| `approvePolicy` | `rab-approve` | `bendahara_umum` atau `ketua_yayasan` |

### RabService (extend MasterService)

Method utama:

| Method | Deskripsi |
|---|---|
| `generateNomorRab(string $bulanPengajuan): string` | Generate nomor RAB dalam DB transaction |
| `storeRab(array $data, User $user): Rab` | Buat RAB + items + generate nomor |
| `updateRab(Rab $rab, array $data): Rab` | Update RAB + sync items + update computed fields |
| `submitRab(Rab $rab, User $user): Rab` | Ubah status ke PENDING_BENDAHARA + catat log |
| `cancelRab(Rab $rab, User $user): Rab` | Ubah status ke CANCELLED + catat log |
| `approveRab(Rab $rab, User $user, ?string $catatan): Rab` | Approve sesuai role user |
| `rejectRab(Rab $rab, User $user, string $catatan): Rab` | Reject sesuai role user |
| `updateComputedFields(Rab $rab): void` | Hitung ulang total_kegiatan & total_biaya_anggaran |
| `getDatatableQuery(array $filters, User $user): Builder` | Query builder untuk datatable dengan filter role |

### Web Controller: `Web\Rab\RabController`

Hanya render view, tidak ada logic bisnis langsung.

| Method | Route | View |
|---|---|---|
| `index()` | GET `/rab` | `rab.index` |
| `create()` | GET `/rab/create` | `rab.create` |
| `store(Request)` | POST `/rab` | redirect `rab.index` |
| `show(Rab)` | GET `/rab/{rab}` | `rab.show` |
| `edit(Rab)` | GET `/rab/{rab}/edit` | `rab.edit` |
| `update(Request, Rab)` | PUT `/rab/{rab}` | redirect `rab.index` |

### API Controller: `Api\V1\Rab\RabController`

| Method | Route | Keterangan |
|---|---|---|
| `dataTable(Request)` | GET `/api/v1/rab/datatable` | Paginated list dengan filter |
| `submit(Rab)` | POST `/api/v1/rab/{rab}/submit` | Submit RAB |
| `cancel(Rab)` | POST `/api/v1/rab/{rab}/cancel` | Cancel RAB |

### API Controller: `Api\V1\Rab\RabApprovalController`

| Method | Route | Keterangan |
|---|---|---|
| `approve(Request, Rab)` | POST `/api/v1/rab/{rab}/approve` | Approve (deteksi role otomatis) |
| `reject(Request, Rab)` | POST `/api/v1/rab/{rab}/reject` | Reject (deteksi role otomatis) |

---

## Data Models

### Tabel `rabs`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `nomor_rab` | varchar(20) | UNIQUE, NOT NULL | Format: RAB/YYYYMM/NNN |
| `divisi_id` | varchar(100) | NOT NULL | role_name dari Spatie Role (bukan FK) |
| `bulan_pengajuan` | date | NOT NULL | Disimpan sebagai tanggal (hari = 01) |
| `status` | varchar(30) | NOT NULL, default 'DRAFT' | Enum RabStatus |
| `total_kegiatan` | integer | NOT NULL, default 0 | Computed: jumlah rab_items |
| `total_biaya_anggaran` | decimal(15,2) | NOT NULL, default 0 | Computed: SUM biaya_anggaran |
| `dibuat_oleh` | bigint | FK → users.id | |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

Index: `(divisi_id)`, `(status)`, `(bulan_pengajuan)`, `(nomor_rab)`

### Tabel `rab_items`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `rab_id` | bigint | FK → rabs.id, CASCADE DELETE | |
| `kegiatan` | varchar(255) | NOT NULL | Nama kegiatan |
| `catatan_kegiatan` | text | NULLABLE | HTML dari Summernote |
| `biaya_anggaran` | decimal(15,2) | NOT NULL, ≥ 0 | |
| `waktu_pelaksanaan` | varchar(100) | NOT NULL | Contoh: "Januari 2025", "Minggu ke-2" |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

Index: `(rab_id)`

### Tabel `rab_approval_logs`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `rab_id` | bigint | FK → rabs.id, CASCADE DELETE | |
| `aksi` | varchar(30) | NOT NULL | Enum RabAksiLog |
| `dilakukan_oleh` | bigint | FK → users.id | |
| `catatan` | text | NULLABLE | Wajib diisi saat reject |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

Index: `(rab_id)`, `(dilakukan_oleh)`

### Model Relationships

```
Rab
 ├── hasMany RabItem (cascade delete)
 ├── hasMany RabApprovalLog (cascade delete)
 └── belongsTo User (dibuat_oleh)

RabItem
 └── belongsTo Rab

RabApprovalLog
 ├── belongsTo Rab
 └── belongsTo User (dilakukan_oleh)
```

### Model `Rab` — Accessors & Methods

```php
// Accessor: label status untuk UI
public function getStatusLabelAttribute(): string

// Accessor: warna badge status
public function getStatusColorAttribute(): string

// Helper: apakah RAB bisa diedit (DRAFT | REJECTED_*)
public function isEditable(): bool

// Helper: apakah RAB bisa disubmit
public function isSubmittable(): bool

// Helper: apakah RAB bisa dicancel
public function isCancellable(): bool
```

### API Datatable Request Parameters

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `page` | int | 1 | Halaman |
| `size` | int | 10 | Jumlah per halaman |
| `search` | string | '' | Search nomor_rab atau divisi_id |
| `sortField` | string | 'created_at' | Field sort |
| `sortOrder` | string | 'desc' | asc / desc |
| `status` | string | null | Filter by RabStatus |
| `divisi_id` | string | null | Filter by divisi (hanya untuk bendahara/ketua) |
| `bulan` | string | null | Filter by bulan (format: YYYY-MM) |

### API Datatable Response

```json
{
    "page": 1,
    "pageCount": 5,
    "sortField": "created_at",
    "sortOrder": "desc",
    "totalCount": 50,
    "data": [
        {
            "id": 1,
            "nomor_rab": "RAB/202501/001",
            "divisi_id": "divisi_it",
            "bulan_pengajuan": "2025-01-01",
            "bulan_pengajuan_label": "Januari 2025",
            "total_kegiatan": 3,
            "total_biaya_anggaran": 5000000,
            "total_biaya_anggaran_formatted": "5.000.000",
            "status": "PENDING_BENDAHARA",
            "status_label": "Menunggu Bendahara",
            "status_color": "warning",
            "can_edit": false,
            "can_submit": false,
            "can_cancel": true,
            "can_approve": true
        }
    ]
}
```

### Summernote Integration

Field `catatan_kegiatan` menggunakan Summernote via CDN. Dimuat per-view (bukan di layout global) pada halaman `create.blade.php` dan `edit.blade.php`. Fallback: jika CDN gagal dimuat, field tetap tampil sebagai `<textarea>` biasa. Konten disimpan sebagai HTML dan dirender dengan `{!! $item->catatan_kegiatan !!}` di halaman show.

### Menu Entry

Tambahkan di `config/menus.php`:

```php
[
    'title' => 'RAB',
    'icon' => 'ki-filled ki-document',
    'permission' => ['rab-read'],
    'route' => 'rab.index',
    'pathUrl' => ['rab*']
]
```


---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: New RAB Auto-Fill Invariant

*For any* user dengan role `divisi_*` yang membuat RAB baru dengan data valid, RAB yang tersimpan harus memiliki: `divisi_id` sama dengan `role_name` user tersebut, `dibuat_oleh` sama dengan `id` user tersebut, dan `status` sama dengan `DRAFT`.

**Validates: Requirements 1.2, 1.3, 1.4**

---

### Property 2: Nomor RAB Format dan Keunikan

*For any* kumpulan RAB yang dibuat (termasuk RAB dari bulan yang sama maupun berbeda), setiap `nomor_rab` harus: (a) match pattern `RAB/\d{6}/\d{3}`, (b) unik secara global, (c) sequence-nya berurutan mulai dari `001` per bulan, dan (d) sequence reset ke `001` saat bulan berganti.

**Validates: Requirements 1.5, 10.1, 10.2, 10.3, 10.4**

---

### Property 3: Validasi Biaya Anggaran

*For any* request pembuatan atau update RAB_Item dengan `biaya_anggaran` bernilai negatif atau non-numerik, sistem harus menolak request tersebut dengan response validasi error (HTTP 422).

**Validates: Requirements 1.8**

---

### Property 4: Computed Fields Invariant

*For any* RAB setelah operasi tambah, ubah, atau hapus RAB_Item, nilai `total_kegiatan` pada header RAB harus selalu sama dengan `COUNT(rab_items)` dan `total_biaya_anggaran` harus selalu sama dengan `SUM(biaya_anggaran)` dari seluruh RAB_Item milik RAB tersebut.

**Validates: Requirements 2.2, 2.3**

---

### Property 5: Edit Permission Berdasarkan Status

*For any* RAB, operasi tambah/ubah/hapus RAB_Item hanya boleh berhasil jika status RAB adalah `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`. Untuk status lainnya, sistem harus mengembalikan HTTP 403.

**Validates: Requirements 2.4, 2.5**

---

### Property 6: Submit State Transition

*For any* RAB dengan status `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA` yang disubmit oleh Divisi pengaju, status RAB harus berubah menjadi `PENDING_BENDAHARA`. Untuk status lainnya, sistem harus mengembalikan HTTP 422.

**Validates: Requirements 3.1, 3.2, 3.5**

---

### Property 7: Approval Log Invariant

*For any* aksi yang mengubah status RAB (submit, approve, reject, cancel), sistem harus selalu mencatat satu entri `rab_approval_logs` dengan `aksi` yang sesuai, `dilakukan_oleh` yang benar, dan `created_at` yang valid. Untuk aksi reject, field `catatan` harus terisi.

**Validates: Requirements 3.3, 4.3, 4.4, 5.3, 5.4, 6.3**

---

### Property 8: Bendahara Approval State Transition

*For any* RAB berstatus `PENDING_BENDAHARA`: jika di-approve oleh `bendahara_umum` maka status berubah ke `PENDING_KETUA`; jika di-reject maka status berubah ke `REJECTED_BENDAHARA`. Aksi oleh user non-`bendahara_umum` harus mengembalikan HTTP 403. Aksi pada RAB dengan status selain `PENDING_BENDAHARA` harus mengembalikan HTTP 422.

**Validates: Requirements 4.1, 4.2, 4.5, 4.6**

---

### Property 9: Ketua Approval State Transition

*For any* RAB berstatus `PENDING_KETUA`: jika di-approve oleh `ketua_yayasan` maka status berubah ke `APPROVED`; jika di-reject maka status berubah ke `REJECTED_KETUA`. Aksi oleh user non-`ketua_yayasan` harus mengembalikan HTTP 403. Aksi pada RAB dengan status selain `PENDING_KETUA` harus mengembalikan HTTP 422.

**Validates: Requirements 5.1, 5.2, 5.5, 5.6**

---

### Property 10: Cancel Terminal State

*For any* RAB berstatus `CANCELLED`, tidak ada aksi apapun (submit, approve, reject, cancel) yang boleh mengubah statusnya. Semua aksi tersebut harus mengembalikan HTTP 422.

**Validates: Requirements 6.4**

---

### Property 11: Cancel State Transition dan Otorisasi

*For any* RAB dengan status selain `APPROVED` dan `CANCELLED`, jika Divisi pengaju melakukan cancel maka status berubah ke `CANCELLED`. User yang bukan Divisi pengaju harus mendapat HTTP 403. Cancel pada RAB berstatus `APPROVED` atau `CANCELLED` harus mengembalikan HTTP 422.

**Validates: Requirements 6.2, 6.5, 6.6**

---

### Property 12: Role-Based Data Visibility

*For any* user dengan role `divisi_*` yang mengakses datatable RAB, semua data yang dikembalikan harus memiliki `divisi_id` sama dengan `role_name` user tersebut — tidak ada RAB dari divisi lain yang boleh muncul. User dengan role `bendahara_umum` atau `ketua_yayasan` harus mendapatkan semua RAB dari semua divisi.

**Validates: Requirements 7.1, 8.1, 9.3, 9.4**

---

### Property 13: Filter Datatable Konsistensi

*For any* request datatable dengan filter `status`, `divisi_id`, atau `bulan_pengajuan`, semua item dalam response harus memenuhi semua kriteria filter yang diberikan — tidak ada item yang lolos filter yang tidak sesuai.

**Validates: Requirements 8.2**

---

### Property 14: Catatan Kegiatan Round Trip

*For any* konten HTML yang dimasukkan ke field `catatan_kegiatan` melalui Summernote, konten yang tersimpan di database dan dikembalikan saat read harus identik dengan konten yang dikirim (round trip).

**Validates: Requirements 11.3**

---

## Error Handling

### Kategori Error

| Kode HTTP | Kondisi | Contoh |
|---|---|---|
| 403 | Otorisasi gagal | User non-divisi coba buat RAB; user lain coba edit RAB divisi lain |
| 404 | Resource tidak ditemukan | RAB dengan ID tidak ada |
| 422 | State machine violation atau validasi | Submit RAB yang sudah APPROVED; reject tanpa catatan |
| 500 | Error server / DB transaction gagal | Race condition generate nomor RAB |

### Penanganan di MasterController

Semua error ditangani oleh `callFunction()` di `MasterController`:
- `ValidationException` → HTTP 422 + pesan validasi
- `ModelNotFoundException` → HTTP 404
- `CustomException` → HTTP sesuai code di exception
- `QueryException` → HTTP 500 + pesan DB error
- `AuthorizationException` (dari `Gate::authorize`) → HTTP 403

### Validasi Reject

Saat `reject()` dipanggil, service harus memvalidasi bahwa `catatan` tidak kosong sebelum mengubah status. Gunakan `throw new ValidationException` jika kosong agar ditangkap `callFunction()`.

### Race Condition Nomor RAB

Generate nomor RAB menggunakan `SELECT ... FOR UPDATE` dalam `DB::transaction()` untuk mencegah race condition. Jika terjadi `DeadlockException`, `callFunction()` akan rollback dan mengembalikan pesan error deskriptif.

---

## Testing Strategy

### Dual Testing Approach

Pengujian menggunakan dua pendekatan komplementer:
- **Unit/Feature tests**: Verifikasi contoh spesifik, edge case, dan kondisi error
- **Property-based tests**: Verifikasi properti universal di berbagai input yang di-generate secara acak

### Library Property-Based Testing

Gunakan **[Eris](https://github.com/giorgiosironi/eris)** (PHP property-based testing library) yang kompatibel dengan PHPUnit.

```bash
composer require --dev giorgiosironi/eris
```

### Unit / Feature Tests

Fokus pada:
- Contoh spesifik alur happy path (buat RAB → submit → approve bendahara → approve ketua)
- Edge case: RAB tanpa items, biaya negatif, reject tanpa catatan
- Integrasi antar komponen: service + model + policy
- Setiap endpoint API mengembalikan format response yang benar

### Property-Based Tests

Setiap property di atas diimplementasikan sebagai satu property-based test dengan minimum **100 iterasi**.

Format tag komentar:
```
// Feature: rab-pencatatan, Property {N}: {property_text}
```

Contoh implementasi:

```php
// Feature: rab-pencatatan, Property 4: Computed Fields Invariant
public function testComputedFieldsInvariant()
{
    $this->forAll(
        Generator\choose(1, 10), // jumlah items
        Generator\float(0, 10000000) // biaya per item
    )->then(function ($itemCount, $biaya) {
        $rab = Rab::factory()->create();
        RabItem::factory()->count($itemCount)->create([
            'rab_id' => $rab->id,
            'biaya_anggaran' => $biaya,
        ]);
        $rab->refresh();
        $this->assertEquals($itemCount, $rab->total_kegiatan);
        $this->assertEquals($itemCount * $biaya, $rab->total_biaya_anggaran);
    });
}
```

### Test Coverage Target

| Layer | Tipe Test | Target |
|---|---|---|
| RabService | Unit + Property | Semua method, semua property |
| RabPolicy | Unit | Semua kombinasi role × aksi |
| API Controllers | Feature | Semua endpoint, semua status code |
| State Machine | Property | Semua transisi valid dan invalid |
| Nomor RAB | Property | Format, keunikan, sequence reset |
| Computed Fields | Property | Konsistensi setelah setiap operasi item |
