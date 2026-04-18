# Design Document: Pengajuan Pembelian Barang

## Overview

Fitur Pengajuan Pembelian Barang adalah modul yang memungkinkan semua divisi mengajukan kebutuhan pembelian barang. Setiap pengajuan berisi header (judul, keperluan, tanggal dibutuhkan) dan daftar item barang. Pengajuan melewati alur approval dua tingkat: Bendahara Umum → Ketua Yayasan. Setelah disetujui, Divisi Sarpras mengelola proses pembelian fisik dengan menandai item yang sudah datang beserta harga aktualnya.

Modul ini dibangun mengikuti pola arsitektur yang sudah ada: `MasterController` + `callFunction()`, `MasterService`, Spatie Permission untuk otorisasi berbasis role, Sanctum untuk API auth, dan Fortify untuk web auth. Implementasinya mengacu pada modul RAB yang sudah ada sebagai referensi utama.

### Tujuan Utama

- Divisi dapat membuat, mengedit, dan mengajukan PurchaseRequest
- Bendahara Umum dapat menyetujui/menolak pada tahap pertama
- Ketua Yayasan dapat menyetujui/menolak pada tahap kedua
- Divisi Sarpras mengelola proses pembelian fisik setelah disetujui
- Sistem menghasilkan nomor pengajuan unik berformat `PB/YYYYMM/NNN`
- Audit trail lengkap via tabel `purchase_request_logs`
- Dashboard ringkasan dan export Excel

---

## Architecture

### Komponen Utama

```
┌─────────────────────────────────────────────────────────────────┐
│                         Browser / Client                         │
└──────────────┬──────────────────────────┬───────────────────────┘
               │ Web (Fortify session)     │ API (Sanctum token)
               ▼                           ▼
┌──────────────────────────┐  ┌────────────────────────────────────────┐
│  Web\PurchaseRequest\    │  │  Api\V1\PurchaseRequest\               │
│  PurchaseRequestController│  │  PurchaseRequestController             │
│  PurchaseRequestDashboard │  │  PurchaseRequestApprovalController     │
│  Controller               │  │  PurchaseRequestPurchasingController   │
│  PurchaseRequestExport    │  │  PurchaseRequestDashboardController    │
│  Controller               │  │                                        │
└──────────┬────────────────┘  └──────────────────┬─────────────────────┘
           │                                       │
           └──────────────────┬────────────────────┘
                              ▼
               ┌──────────────────────────────┐
               │    PurchaseRequestService    │
               │    DashboardPurchaseRequest  │
               │    Service                   │
               │    (extend MasterService)    │
               └──────────────┬───────────────┘
                              ▼
               ┌──────────────────────────────┐
               │  Models: PurchaseRequest,    │
               │  PurchaseRequestItem,        │
               │  PurchaseRequestLog          │
               └──────────────┬───────────────┘
                              ▼
               ┌──────────────────────────────┐
               │        PostgreSQL DB         │
               └──────────────────────────────┘
```

### State Machine PurchaseRequest

```
                         ┌─────────┐
                         │  DRAFT  │◄──────────────────────────────┐
                         └────┬────┘                               │
                              │ submit()                           │
                              ▼                                    │
                 ┌──────────────────────┐                         │
                 │  PENDING_BENDAHARA   │                         │
                 └──────┬───────┬───────┘                         │
              approve() │       │ reject()                        │
                        ▼       ▼                                 │
            ┌──────────────┐  ┌────────────────────┐             │
            │ PENDING_KETUA│  │ REJECTED_BENDAHARA  │─────────── ┤ submit()
            └──────┬───┬───┘  └────────────────────┘             │
         approve() │   │ reject()                                 │
                   ▼   ▼                                          │
            ┌──────────┐  ┌────────────────┐                     │
            │ APPROVED │  │ REJECTED_KETUA │─────────────────────┘
            └────┬─────┘  └────────────────┘
                 │ startPurchasing()
                 ▼
            ┌────────────┐
            │ PURCHASING │
            └─────┬──────┘
                  │ markItemPurchased() — masih ada item belum dibeli
                  ▼
            ┌──────────────────────┐
            │  PARTIALLY_PURCHASED │
            └──────────┬───────────┘
                       │ markItemPurchased() — semua item sudah dibeli
                       ▼
                  ┌───────────┐
                  │ COMPLETED │
                  └───────────┘

  cancel() tersedia dari semua status kecuali APPROVED, CANCELLED, COMPLETED
                  ▼
            ┌───────────┐
            │ CANCELLED │ (terminal state)
            └───────────┘
```

### Alur Generate Nomor Pengajuan

```
store() dipanggil
    │
    ▼
DB::transaction() {
    SELECT nomor_pengajuan WHERE LIKE 'PB/YYYYMM/%' ORDER BY DESC LIMIT 1 FOR UPDATE
    │
    ├── Tidak ada → sequence = 1
    └── Ada       → sequence = MAX_sequence + 1
    │
    ▼
    nomor_pengajuan = "PB/" + YYYYMM + "/" + str_pad(sequence, 3, '0', STR_PAD_LEFT)
    │
    ▼
    INSERT purchase_requests (nomor_pengajuan, ...)
}
```

---

## Components and Interfaces

### Directory Structure (file baru)

```
app/
├── Enums/
│   ├── PurchaseRequestStatus.php
│   └── PurchaseRequestAksiLog.php
├── Exports/
│   ├── PurchaseRequestExport.php
│   ├── PurchaseRequestHeaderSheet.php
│   └── PurchaseRequestItemSheet.php
├── Http/Controllers/
│   ├── Api/V1/PurchaseRequest/
│   │   ├── PurchaseRequestController.php          (dataTable, store, show, update)
│   │   ├── PurchaseRequestApprovalController.php  (submit, cancel, approve, reject)
│   │   ├── PurchaseRequestPurchasingController.php (startPurchasing, markItemPurchased)
│   │   └── PurchaseRequestDashboardController.php  (summary, chart)
│   └── Web/PurchaseRequest/
│       ├── PurchaseRequestController.php           (index, create, store, show, edit, update)
│       ├── PurchaseRequestDashboardController.php  (index)
│       └── PurchaseRequestExportController.php     (export)
├── Models/
│   ├── PurchaseRequest.php
│   ├── PurchaseRequestItem.php
│   └── PurchaseRequestLog.php
├── Policies/
│   └── PurchaseRequestPolicy.php
└── Services/
    ├── PurchaseRequestService.php
    └── Dashboard/
        └── DashboardPurchaseRequestService.php

database/migrations/
├── xxxx_create_purchase_requests_table.php
├── xxxx_create_purchase_request_items_table.php
└── xxxx_create_purchase_request_logs_table.php

resources/views/purchase-request/
├── index.blade.php
├── create.blade.php
├── edit.blade.php
├── show.blade.php
└── dashboard.blade.php
```

### Enums

**`App\Enums\PurchaseRequestStatus`**
```php
enum PurchaseRequestStatus: string {
    case DRAFT                = 'DRAFT';
    case PENDING_BENDAHARA    = 'PENDING_BENDAHARA';
    case REJECTED_BENDAHARA   = 'REJECTED_BENDAHARA';
    case PENDING_KETUA        = 'PENDING_KETUA';
    case REJECTED_KETUA       = 'REJECTED_KETUA';
    case APPROVED             = 'APPROVED';
    case CANCELLED            = 'CANCELLED';
    case PURCHASING           = 'PURCHASING';
    case PARTIALLY_PURCHASED  = 'PARTIALLY_PURCHASED';
    case COMPLETED            = 'COMPLETED';
}
```

**`App\Enums\PurchaseRequestAksiLog`**
```php
enum PurchaseRequestAksiLog: string {
    case SUBMITTED           = 'SUBMITTED';
    case APPROVED_BENDAHARA  = 'APPROVED_BENDAHARA';
    case REJECTED_BENDAHARA  = 'REJECTED_BENDAHARA';
    case APPROVED_KETUA      = 'APPROVED_KETUA';
    case REJECTED_KETUA      = 'REJECTED_KETUA';
    case CANCELLED           = 'CANCELLED';
    case PURCHASING_STARTED  = 'PURCHASING_STARTED';
    case ITEM_PURCHASED      = 'ITEM_PURCHASED';
    case COMPLETED           = 'COMPLETED';
}
```

### PurchaseRequestPolicy

| Method | Permission | Keterangan |
|---|---|---|
| `readPolicy` | `purchase-request-read` | Semua role yang punya permission ini |
| `createPolicy` | `purchase-request-create` | Hanya role `divisi_*` |
| `updatePolicy` | `purchase-request-update` | Hanya role `divisi_*`, PR milik divisi sendiri, status editable |
| `cancelPolicy` | `purchase-request-cancel` | Hanya Divisi_Pengaju, status bukan APPROVED/CANCELLED/COMPLETED |
| `approvePolicy` | `purchase-request-approve` | `bendahara_umum` atau `ketua_yayasan` |
| `purchasingPolicy` | `purchase-request-approve` | Hanya `divisi_sarpras` |

### PurchaseRequestService (extend MasterService)

| Method | Deskripsi |
|---|---|
| `generateNomorPengajuan(): string` | Generate nomor PB/YYYYMM/NNN dalam DB transaction |
| `store(array $data, User $user): PurchaseRequest` | Buat PR + items + generate nomor |
| `update(PurchaseRequest $pr, array $data): PurchaseRequest` | Update PR + sync items + update computed fields |
| `submit(PurchaseRequest $pr, User $user): PurchaseRequest` | Ubah status ke PENDING_BENDAHARA + catat log |
| `cancel(PurchaseRequest $pr, User $user): PurchaseRequest` | Ubah status ke CANCELLED + catat log |
| `approve(PurchaseRequest $pr, User $user, ?string $catatan): PurchaseRequest` | Approve sesuai role user |
| `reject(PurchaseRequest $pr, User $user, string $catatan): PurchaseRequest` | Reject sesuai role user |
| `startPurchasing(PurchaseRequest $pr, User $user): PurchaseRequest` | Ubah status ke PURCHASING + catat log |
| `markItemPurchased(PurchaseRequest $pr, PurchaseRequestItem $item, array $data, User $user): PurchaseRequest` | Tandai item sudah dibeli, update status PR, catat log |
| `updateComputedFields(PurchaseRequest $pr): void` | Hitung ulang total_item & total_biaya_estimasi |
| `updateActualFields(PurchaseRequest $pr): void` | Hitung ulang total_biaya_aktual |
| `getDatatableQuery(array $filters, User $user): Builder` | Query builder untuk datatable dengan filter role |

### Web Controller: `Web\PurchaseRequest\PurchaseRequestController`

| Method | Route | View |
|---|---|---|
| `index()` | GET `/purchase-requests` | `purchase-request.index` |
| `create()` | GET `/purchase-requests/create` | `purchase-request.create` |
| `store(Request)` | POST `/purchase-requests` | redirect `purchase-requests.index` |
| `show(PurchaseRequest)` | GET `/purchase-requests/{pr}` | `purchase-request.show` |
| `edit(PurchaseRequest)` | GET `/purchase-requests/{pr}/edit` | `purchase-request.edit` |
| `update(Request, PurchaseRequest)` | PUT `/purchase-requests/{pr}` | redirect `purchase-requests.index` |

### Web Controller: `Web\PurchaseRequest\PurchaseRequestDashboardController`

| Method | Route | View |
|---|---|---|
| `index()` | GET `/purchase-requests/dashboard` | `purchase-request.dashboard` |

### Web Controller: `Web\PurchaseRequest\PurchaseRequestExportController`

| Method | Route | Keterangan |
|---|---|---|
| `export(Request)` | GET `/purchase-requests/export` | Download file .xlsx |

### API Controller: `Api\V1\PurchaseRequest\PurchaseRequestController`

| Method | Route | Keterangan |
|---|---|---|
| `dataTable(Request)` | GET `/api/v1/purchase-requests/datatable` | Paginated list dengan filter |

### API Controller: `Api\V1\PurchaseRequest\PurchaseRequestApprovalController`

| Method | Route | Keterangan |
|---|---|---|
| `submit(PurchaseRequest)` | POST `/api/v1/purchase-requests/{pr}/submit` | Submit PR |
| `cancel(PurchaseRequest)` | POST `/api/v1/purchase-requests/{pr}/cancel` | Cancel PR |
| `approve(Request, PurchaseRequest)` | POST `/api/v1/purchase-requests/{pr}/approve` | Approve (deteksi role otomatis) |
| `reject(Request, PurchaseRequest)` | POST `/api/v1/purchase-requests/{pr}/reject` | Reject (deteksi role otomatis) |

### API Controller: `Api\V1\PurchaseRequest\PurchaseRequestPurchasingController`

| Method | Route | Keterangan |
|---|---|---|
| `startPurchasing(PurchaseRequest)` | POST `/api/v1/purchase-requests/{pr}/start-purchasing` | Mulai proses pembelian |
| `markItemPurchased(Request, PurchaseRequest, PurchaseRequestItem)` | POST `/api/v1/purchase-requests/{pr}/items/{item}/mark-purchased` | Tandai item sudah dibeli |

### API Controller: `Api\V1\PurchaseRequest\PurchaseRequestDashboardController`

| Method | Route | Keterangan |
|---|---|---|
| `summary(Request)` | GET `/api/v1/purchase-requests/dashboard/summary` | Data summary cards |
| `chart(Request)` | GET `/api/v1/purchase-requests/dashboard/chart` | Data chart |

---

## Data Models

### Tabel `purchase_requests`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `nomor_pengajuan` | varchar(20) | UNIQUE, NOT NULL | Format: PB/YYYYMM/NNN |
| `divisi_id` | varchar(100) | NOT NULL | role_name dari Spatie Role (bukan FK) |
| `judul_pengajuan` | varchar(255) | NOT NULL | Judul pengajuan |
| `keperluan` | text | NULLABLE | Deskripsi keperluan (HTML dari Summernote) |
| `tanggal_dibutuhkan` | date | NOT NULL | Tanggal barang dibutuhkan |
| `status` | varchar(30) | NOT NULL, default 'DRAFT' | Enum PurchaseRequestStatus |
| `total_item` | integer | NOT NULL, default 0 | Computed: COUNT(purchase_request_items) |
| `total_biaya_estimasi` | decimal(15,2) | NOT NULL, default 0 | Computed: SUM(biaya_estimasi * jumlah) |
| `total_biaya_aktual` | decimal(15,2) | NOT NULL, default 0 | Computed: SUM(harga_aktual) dari item sudah_dibeli=true |
| `dibuat_oleh` | bigint | FK → users.id | |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

Index: `(divisi_id)`, `(status)`, `(tanggal_dibutuhkan)`, `(nomor_pengajuan)`

### Tabel `purchase_request_items`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `purchase_request_id` | bigint | FK → purchase_requests.id, CASCADE DELETE | |
| `nama_barang` | varchar(255) | NOT NULL | Nama barang |
| `satuan` | varchar(50) | NOT NULL | Contoh: "pcs", "kg", "unit" |
| `jumlah` | integer | NOT NULL, > 0 | Jumlah barang |
| `biaya_estimasi` | decimal(15,2) | NOT NULL, ≥ 0 | Estimasi harga per satuan |
| `catatan_item` | text | NULLABLE | Catatan tambahan item |
| `sudah_dibeli` | boolean | NOT NULL, default false | Flag apakah item sudah dibeli |
| `harga_aktual` | decimal(15,2) | NULLABLE | Harga total aktual item yang sudah dibeli |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

Index: `(purchase_request_id)`, `(sudah_dibeli)`

### Tabel `purchase_request_logs`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| `id` | bigint | PK, auto-increment | |
| `purchase_request_id` | bigint | FK → purchase_requests.id, CASCADE DELETE | |
| `aksi` | varchar(30) | NOT NULL | Enum PurchaseRequestAksiLog |
| `dilakukan_oleh` | bigint | FK → users.id | |
| `catatan` | text | NULLABLE | Wajib diisi saat reject |
| `created_at` | timestamp | | |
| `updated_at` | timestamp | | |

Index: `(purchase_request_id)`, `(dilakukan_oleh)`

### Model Relationships

```
PurchaseRequest
 ├── hasMany PurchaseRequestItem (cascade delete)
 ├── hasMany PurchaseRequestLog (cascade delete)
 └── belongsTo User (dibuat_oleh)

PurchaseRequestItem
 └── belongsTo PurchaseRequest

PurchaseRequestLog
 ├── belongsTo PurchaseRequest
 └── belongsTo User (dilakukan_oleh)
```

### Model `PurchaseRequest` — Accessors & Methods

```php
// Accessor: label status untuk UI
public function getStatusLabelAttribute(): string

// Accessor: warna badge status
public function getStatusColorAttribute(): string

// Helper: apakah PR bisa diedit (DRAFT | REJECTED_*)
public function isEditable(): bool

// Helper: apakah PR bisa disubmit
public function isSubmittable(): bool

// Helper: apakah PR bisa dicancel (bukan APPROVED, CANCELLED, COMPLETED)
public function isCancellable(): bool

// Helper: apakah PR bisa dimulai proses pembelian (hanya APPROVED)
public function isPurchasable(): bool

// Helper: apakah PR sedang dalam proses pembelian (PURCHASING | PARTIALLY_PURCHASED)
public function isInPurchasing(): bool
```

### API Datatable Request Parameters

| Parameter | Tipe | Default | Keterangan |
|---|---|---|---|
| `page` | int | 1 | Halaman |
| `size` | int | 10 | Jumlah per halaman |
| `search` | string | '' | Search nomor_pengajuan atau judul_pengajuan |
| `sortField` | string | 'created_at' | Field sort |
| `sortOrder` | string | 'desc' | asc / desc |
| `status` | string | null | Filter by PurchaseRequestStatus |
| `divisi_id` | string | null | Filter by divisi (hanya untuk privileged user) |
| `tanggal_dibutuhkan` | string | null | Filter by tanggal (format: YYYY-MM-DD) |

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
            "nomor_pengajuan": "PB/202501/001",
            "divisi_id": "divisi_it",
            "judul_pengajuan": "Pembelian Laptop",
            "tanggal_dibutuhkan": "2025-02-01",
            "total_item": 3,
            "total_biaya_estimasi": 15000000,
            "total_biaya_estimasi_formatted": "15.000.000",
            "total_biaya_aktual": 0,
            "total_biaya_aktual_formatted": "0",
            "status": "PENDING_BENDAHARA",
            "status_label": "Menunggu Bendahara",
            "status_color": "warning",
            "can_edit": false,
            "can_submit": false,
            "can_cancel": true,
            "can_approve": true,
            "can_start_purchasing": false
        }
    ]
}
```

### API Dashboard Summary Response

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

### Summernote Integration

Field `keperluan` menggunakan Summernote via CDN. Dimuat per-view pada halaman `create.blade.php` dan `edit.blade.php`. Konten disimpan sebagai HTML dan dirender dengan `{!! $purchaseRequest->keperluan !!}` di halaman show.

### Menu Entry

Tambahkan di `config/menus.php`:

```php
[
    'title' => 'Pengajuan Pembelian',
    'icon' => 'ki-filled ki-purchase',
    'permission' => ['purchase-request-read'],
    'route' => 'purchase-requests.index',
    'pathUrl' => ['purchase-requests*'],
    'children' => [
        [
            'title' => 'Dashboard',
            'route' => 'purchase-requests.dashboard',
            'permission' => ['purchase-request-read'],
            'pathUrl' => ['purchase-requests/dashboard*'],
        ],
        [
            'title' => 'Daftar Pengajuan',
            'route' => 'purchase-requests.index',
            'permission' => ['purchase-request-read'],
            'pathUrl' => ['purchase-requests', 'purchase-requests/create', 'purchase-requests/*/edit'],
        ],
    ],
]
```

### Routes

**`routes/web.php`** — tambahkan di dalam middleware `auth`:

```php
Route::prefix('/purchase-requests')->as('purchase-requests.')->group(function () {
    Route::get('', [WebPurchaseRequestController::class, 'index'])->name('index');
    Route::get('/create', [WebPurchaseRequestController::class, 'create'])->name('create');
    Route::post('/', [WebPurchaseRequestController::class, 'store'])->name('store');
    // Dashboard & export harus sebelum /{pr} untuk menghindari konflik route parameter
    Route::get('/dashboard', [WebPurchaseRequestDashboardController::class, 'index'])->name('dashboard');
    Route::get('/export', [WebPurchaseRequestExportController::class, 'export'])->name('export');
    Route::get('/{purchaseRequest}', [WebPurchaseRequestController::class, 'show'])->name('show');
    Route::get('/{purchaseRequest}/edit', [WebPurchaseRequestController::class, 'edit'])->name('edit');
    Route::put('/{purchaseRequest}', [WebPurchaseRequestController::class, 'update'])->name('update');
});
```

**`routes/api.php`** — tambahkan di dalam middleware `auth:sanctum`:

```php
Route::prefix('/purchase-requests')->as('purchase-requests.')->group(function () {
    Route::get('/datatable', [ApiPurchaseRequestController::class, 'dataTable'])->name('datatable');
    Route::get('/dashboard/summary', [ApiPurchaseRequestDashboardController::class, 'summary'])->name('dashboard.summary');
    Route::get('/dashboard/chart', [ApiPurchaseRequestDashboardController::class, 'chart'])->name('dashboard.chart');
    Route::post('/{purchaseRequest}/submit', [ApiPurchaseRequestApprovalController::class, 'submit'])->name('submit');
    Route::post('/{purchaseRequest}/cancel', [ApiPurchaseRequestApprovalController::class, 'cancel'])->name('cancel');
    Route::post('/{purchaseRequest}/approve', [ApiPurchaseRequestApprovalController::class, 'approve'])->name('approve');
    Route::post('/{purchaseRequest}/reject', [ApiPurchaseRequestApprovalController::class, 'reject'])->name('reject');
    Route::post('/{purchaseRequest}/start-purchasing', [ApiPurchaseRequestPurchasingController::class, 'startPurchasing'])->name('start-purchasing');
    Route::post('/{purchaseRequest}/items/{item}/mark-purchased', [ApiPurchaseRequestPurchasingController::class, 'markItemPurchased'])->name('items.mark-purchased');
});
```

---

## Correctness Properties

*A property is a characteristic or behavior that should hold true across all valid executions of a system — essentially, a formal statement about what the system should do. Properties serve as the bridge between human-readable specifications and machine-verifiable correctness guarantees.*

### Property 1: New PurchaseRequest Auto-Fill Invariant

*For any* user dengan role `divisi_*` yang membuat PurchaseRequest baru dengan data valid, PurchaseRequest yang tersimpan harus memiliki: `divisi_id` sama dengan `role_name` user tersebut, `dibuat_oleh` sama dengan `id` user tersebut, dan `status` sama dengan `DRAFT`.

**Validates: Requirements 1.2, 1.3, 1.4**

---

### Property 2: Nomor Pengajuan Format dan Keunikan

*For any* kumpulan PurchaseRequest yang dibuat (termasuk dari bulan yang sama maupun berbeda), setiap `nomor_pengajuan` harus: (a) match pattern `PB/\d{6}/\d{3}`, (b) unik secara global, (c) sequence-nya berurutan mulai dari `001` per bulan, dan (d) sequence reset ke `001` saat bulan berganti.

**Validates: Requirements 1.5, 11.1, 11.2, 11.3, 11.4**

---

### Property 3: Validasi Input Item

*For any* request pembuatan atau update PurchaseRequest_Item dengan `biaya_estimasi` bernilai negatif atau non-numerik, atau `jumlah` bernilai bukan bilangan bulat positif, sistem harus menolak request tersebut dengan response validasi error (HTTP 422).

**Validates: Requirements 1.8, 1.9, 6.10**

---

### Property 4: Computed Fields Invariant

*For any* PurchaseRequest setelah operasi tambah, ubah, atau hapus PurchaseRequest_Item, nilai `total_item` harus selalu sama dengan `COUNT(purchase_request_items)` dan `total_biaya_estimasi` harus selalu sama dengan `SUM(biaya_estimasi * jumlah)` dari seluruh item milik PurchaseRequest tersebut.

**Validates: Requirements 2.2, 2.3**

---

### Property 5: Edit Permission Berdasarkan Status

*For any* PurchaseRequest, operasi tambah/ubah/hapus PurchaseRequest_Item hanya boleh berhasil jika status PurchaseRequest adalah `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA`. Untuk status lainnya, sistem harus mengembalikan HTTP 403.

**Validates: Requirements 2.4, 2.5**

---

### Property 6: Submit State Transition

*For any* PurchaseRequest dengan status `DRAFT`, `REJECTED_BENDAHARA`, atau `REJECTED_KETUA` yang disubmit oleh Divisi_Pengaju, status harus berubah menjadi `PENDING_BENDAHARA`. Untuk status lainnya, sistem harus mengembalikan HTTP 422.

**Validates: Requirements 3.1, 3.2, 3.5**

---

### Property 7: Approval Log Invariant

*For any* aksi yang mengubah status PurchaseRequest (submit, approve, reject, cancel, startPurchasing, markItemPurchased, completed), sistem harus selalu mencatat satu entri `purchase_request_logs` dengan `aksi` yang sesuai, `dilakukan_oleh` yang benar, dan `created_at` yang valid. Untuk aksi reject, field `catatan` harus terisi.

**Validates: Requirements 3.3, 4.3, 4.4, 5.3, 5.4, 6.2, 6.4, 7.3**

---

### Property 8: Bendahara Approval State Transition

*For any* PurchaseRequest berstatus `PENDING_BENDAHARA`: jika di-approve oleh `bendahara_umum` maka status berubah ke `PENDING_KETUA`; jika di-reject maka status berubah ke `REJECTED_BENDAHARA`. Aksi oleh user non-`bendahara_umum` harus mengembalikan HTTP 403. Aksi pada PurchaseRequest dengan status selain `PENDING_BENDAHARA` harus mengembalikan HTTP 422.

**Validates: Requirements 4.1, 4.2, 4.5, 4.6**

---

### Property 9: Ketua Approval State Transition

*For any* PurchaseRequest berstatus `PENDING_KETUA`: jika di-approve oleh `ketua_yayasan` maka status berubah ke `APPROVED`; jika di-reject maka status berubah ke `REJECTED_KETUA`. Aksi oleh user non-`ketua_yayasan` harus mengembalikan HTTP 403. Aksi pada PurchaseRequest dengan status selain `PENDING_KETUA` harus mengembalikan HTTP 422.

**Validates: Requirements 5.1, 5.2, 5.5, 5.6**

---

### Property 10: Purchasing State Transitions

*For any* PurchaseRequest berstatus `APPROVED`, memulai proses pembelian oleh `divisi_sarpras` harus mengubah status ke `PURCHASING`. Menandai sebagian item sebagai sudah dibeli harus mengubah status ke `PARTIALLY_PURCHASED`. Menandai semua item sebagai sudah dibeli harus mengubah status ke `COMPLETED`. Aksi oleh user non-`divisi_sarpras` harus mengembalikan HTTP 403. Memulai pembelian pada status selain `APPROVED` harus mengembalikan HTTP 422.

**Validates: Requirements 6.1, 6.5, 6.6, 6.8, 6.9**

---

### Property 11: Total Biaya Aktual Invariant

*For any* PurchaseRequest setelah operasi `markItemPurchased`, nilai `total_biaya_aktual` harus selalu sama dengan `SUM(harga_aktual)` dari seluruh PurchaseRequest_Item yang memiliki `sudah_dibeli = true`.

**Validates: Requirements 6.7**

---

### Property 12: Cancel State Transition dan Otorisasi

*For any* PurchaseRequest dengan status selain `APPROVED`, `CANCELLED`, dan `COMPLETED`, jika Divisi_Pengaju melakukan cancel maka status berubah ke `CANCELLED`. User yang bukan Divisi_Pengaju harus mendapat HTTP 403. Cancel pada PurchaseRequest berstatus `APPROVED`, `CANCELLED`, atau `COMPLETED` harus mengembalikan HTTP 422.

**Validates: Requirements 7.1, 7.2, 7.4, 7.5, 7.6**

---

### Property 13: CANCELLED adalah Terminal State

*For any* PurchaseRequest berstatus `CANCELLED`, tidak ada aksi apapun (submit, approve, reject, cancel, startPurchasing) yang boleh mengubah statusnya. Semua aksi tersebut harus mengembalikan HTTP 422.

**Validates: Requirements 7.4**

---

### Property 14: Role-Based Data Visibility

*For any* user dengan role `divisi_*` (kecuali `divisi_sarpras` dalam kapasitas privileged) yang mengakses datatable PurchaseRequest, semua data yang dikembalikan harus memiliki `divisi_id` sama dengan `role_name` user tersebut. User dengan role `bendahara_umum`, `ketua_yayasan`, atau `divisi_sarpras` harus mendapatkan semua PurchaseRequest dari semua divisi.

**Validates: Requirements 8.1, 9.1, 12.1, 12.2**

---

### Property 15: Filter Datatable Konsistensi

*For any* request datatable dengan filter `status`, `divisi_id`, atau `tanggal_dibutuhkan`, semua item dalam response harus memenuhi semua kriteria filter yang diberikan — tidak ada item yang lolos filter yang tidak sesuai.

**Validates: Requirements 9.2**

---

### Property 16: Dashboard Summary Konsistensi

*For any* set filter (tahun, bulan, divisi_id), nilai `total_pengajuan`, `total_dalam_approval`, `total_dalam_pembelian`, `total_selesai`, `total_biaya_estimasi`, dan `total_biaya_aktual` yang dikembalikan endpoint summary harus konsisten dengan data aktual di database yang memenuhi filter tersebut.

**Validates: Requirements 13.7, 13.8, 13.9, 13.10, 13.11, 13.12**

---

### Property 17: Export Data Completeness dan Role Isolation

*For any* user dengan role `divisi_*`, file export yang dihasilkan hanya boleh berisi PurchaseRequest dengan `divisi_id` sama dengan role user tersebut. Untuk Privileged_User dengan filter `divisi_id` tertentu, file export hanya boleh berisi data dari divisi tersebut. Semua baris dalam Sheet 1 dan Sheet 2 harus konsisten satu sama lain berdasarkan `nomor_pengajuan`.

**Validates: Requirements 13.36, 13.37, 13.45, 13.46**

---

## Error Handling

### Kategori Error

| Kode HTTP | Kondisi | Contoh |
|---|---|---|
| 403 | Otorisasi gagal | User non-divisi coba buat PR; user lain coba edit PR divisi lain; non-sarpras coba start purchasing |
| 404 | Resource tidak ditemukan | PR dengan ID tidak ada |
| 422 | State machine violation atau validasi | Submit PR yang sudah APPROVED; reject tanpa catatan; biaya_estimasi negatif |
| 500 | Error server / DB transaction gagal | Race condition generate nomor pengajuan |

### Penanganan di MasterController

Semua error ditangani oleh `callFunction()` di `MasterController`:
- `ValidationException` → HTTP 422 + pesan validasi
- `ModelNotFoundException` → HTTP 404
- `CustomException` → HTTP sesuai code di exception
- `QueryException` → HTTP 500 + pesan DB error
- `AuthorizationException` (dari `Gate::authorize`) → HTTP 403

### Validasi Reject

Saat `reject()` dipanggil, service harus memvalidasi bahwa `catatan` tidak kosong sebelum mengubah status. Gunakan `throw new ValidationException` jika kosong agar ditangkap `callFunction()`.

### Race Condition Nomor Pengajuan

Generate nomor pengajuan menggunakan `SELECT ... FOR UPDATE` dalam `DB::transaction()` untuk mencegah race condition. Jika terjadi `DeadlockException`, `callFunction()` akan rollback dan mengembalikan pesan error deskriptif.

### Validasi Purchasing

Saat `markItemPurchased()` dipanggil, service harus memvalidasi:
- `harga_aktual` tidak boleh negatif atau non-numerik
- PurchaseRequest harus berstatus `PURCHASING` atau `PARTIALLY_PURCHASED`
- Item yang ditandai harus milik PurchaseRequest yang bersangkutan

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
- Contoh spesifik alur happy path (buat PR → submit → approve bendahara → approve ketua → start purchasing → mark all items → completed)
- Edge case: PR tanpa items, biaya negatif, reject tanpa catatan, mark item pada status yang salah
- Integrasi antar komponen: service + model + policy
- Setiap endpoint API mengembalikan format response yang benar
- Export menghasilkan file dengan dua sheet dan kolom yang benar

### Property-Based Tests

Setiap property di atas diimplementasikan sebagai satu property-based test dengan minimum **100 iterasi**.

Format tag komentar:
```
// Feature: pengajuan-pembelian-barang, Property {N}: {property_text}
```

Contoh implementasi:

```php
// Feature: pengajuan-pembelian-barang, Property 4: Computed Fields Invariant
public function testComputedFieldsInvariant()
{
    $this->forAll(
        Generator\choose(1, 10),       // jumlah items
        Generator\float(0, 10000000),  // biaya_estimasi per item
        Generator\choose(1, 100)       // jumlah per item
    )->then(function ($itemCount, $biayaEstimasi, $jumlah) {
        $pr = PurchaseRequest::factory()->create();
        PurchaseRequestItem::factory()->count($itemCount)->create([
            'purchase_request_id' => $pr->id,
            'biaya_estimasi'      => $biayaEstimasi,
            'jumlah'              => $jumlah,
        ]);
        $pr->refresh();
        $this->assertEquals($itemCount, $pr->total_item);
        $this->assertEquals($itemCount * $biayaEstimasi * $jumlah, $pr->total_biaya_estimasi);
    });
}
```

```php
// Feature: pengajuan-pembelian-barang, Property 2: Nomor Pengajuan Format dan Keunikan
public function testNomorPengajuanFormatDanKeunikan()
{
    $this->forAll(
        Generator\choose(1, 20) // jumlah PR yang dibuat
    )->then(function ($count) {
        $user = User::factory()->create()->assignRole('divisi_it');
        $nomorList = [];
        for ($i = 0; $i < $count; $i++) {
            $pr = $this->purchaseRequestService->store(
                PurchaseRequest::factory()->raw(),
                $user
            );
            $nomorList[] = $pr->nomor_pengajuan;
            $this->assertMatchesRegularExpression('/^PB\/\d{6}\/\d{3}$/', $pr->nomor_pengajuan);
        }
        $this->assertEquals(count($nomorList), count(array_unique($nomorList)));
    });
}
```

### Test Coverage Target

| Layer | Tipe Test | Target |
|---|---|---|
| PurchaseRequestService | Unit + Property | Semua method, semua property |
| PurchaseRequestPolicy | Unit | Semua kombinasi role × aksi |
| API Controllers | Feature | Semua endpoint, semua status code |
| State Machine | Property | Semua transisi valid dan invalid (termasuk PURCHASING, PARTIALLY_PURCHASED, COMPLETED) |
| Nomor Pengajuan | Property | Format, keunikan, sequence reset per bulan |
| Computed Fields | Property | Konsistensi total_item, total_biaya_estimasi, total_biaya_aktual |
| Dashboard Service | Property | Konsistensi summary dengan data aktual |
| Export | Property | Role isolation dan kelengkapan data |
