# Project Overview

Aplikasi manajemen lokasi dan konfirmasi pembayaran berbasis Laravel 12. Sistem ini memiliki dua sisi: landing page publik untuk submit pembayaran, dan admin panel untuk manajemen data.

## Tech Stack

- PHP 8.2+, Laravel 12
- Laravel Fortify (auth web)
- Laravel Sanctum (auth API token)
- Spatie Laravel Permission (roles & permissions)
- Maatwebsite Excel (export)
- Database: PostgreSQL (production) — query pakai `ILIKE` bukan `LIKE`
- Frontend: Blade + Vite

## Active Modules

Hanya modul berikut yang aktif. Jangan buat atau referensikan modul lain.

| Modul | Model | Service | Web Controller | API Controller |
|---|---|---|---|---|
| User | `User` | `UserService` | `Web\User\UserController` | `Api\V1\UserController` |
| Location | `Location`, `LocationCategory` | `LocationService` | `Web\LocationController` | `Api\V1\LocationController` |
| Payment Confirmation | `PaymentConfirmation` | - | `Web\PaymentConfirmationController` | `Api\V1\PaymentConfirmationController` |
| Permission | `Permission` | `PermissionService` | `Web\PermissionController` | `Api\V1\PermissionController` |
| Permission Group | `PermissionGroup` | `PermissionGroupService` | `Web\PermissionGroupController` | `Api\V1\PermissionGroupController` |
| Role | `Role` | `RoleService` | `Web\RoleController` | `Api\V1\RoleController` |
| Auth | - | `Auth\AuthService` | (Fortify) | `Api\V1\Auth\AuthController` |
| Landing | - | - | `Web\LandingController` | - |

## Directory Structure

```
app/
├── Enums/GlobalParam.php
├── Exceptions/CustomException.php
├── Helpers/MenuHelper.php
├── Http/Controllers/
│   ├── Controller.php
│   ├── MasterController.php          ← base controller semua controller extend ini
│   ├── UploadsController.php
│   ├── Api/V1/
│   │   ├── Auth/AuthController.php
│   │   ├── LocationController.php
│   │   ├── PaymentConfirmationController.php
│   │   ├── PermissionController.php
│   │   ├── PermissionGroupController.php
│   │   ├── RoleController.php
│   │   └── UserController.php
│   └── Web/
│       ├── LandingController.php
│       ├── LocationController.php
│       ├── PaymentConfirmationController.php
│       ├── PermissionController.php
│       ├── PermissionGroupController.php
│       ├── RoleController.php
│       └── User/UserController.php
├── Models/
│   ├── Location.php
│   ├── LocationCategory.php
│   ├── PaymentConfirmation.php
│   ├── Permission.php
│   ├── PermissionGroup.php
│   ├── Role.php
│   └── User.php
├── Policies/
│   ├── LocationPolicy.php
│   ├── PermissionGroupPolicy.php
│   ├── PermissionPolicy.php
│   ├── RolePolicy.php
│   └── UserPolicy.php
├── Providers/AppServiceProvider.php
└── Services/
    ├── Auth/AuthService.php
    ├── LocationService.php
    ├── MasterService.php             ← base service semua service extend ini
    ├── PermissionGroupService.php
    ├── PermissionService.php
    ├── RoleService.php
    └── UserService.php
```

## Architecture Patterns

### MasterController Pattern
Semua controller extend `MasterController`. Logic bisnis dibungkus dalam closure yang dipassing ke `callFunction()`. Pattern ini handle DB transaction, exception, dan response format secara otomatis.

```php
// API controller
public function destroy($id)
{
    $func = function () use ($id) {
        Gate::authorize('deletePolicy', Location::class);
        $this->locationService->deleteLocation($id);
    };
    return $this->callFunction($func, null, null);
}

// Web controller — passing view object
public function index()
{
    $func = function () {
        Gate::authorize('readPolicy', Location::class);
        $this->data = compact('breadcrumbs', 'pageTitle');
    };
    return $this->callFunction($func, view('location.index'));
}

// Web controller — redirect setelah store/update
public function store(Request $request)
{
    $func = function () use ($request) {
        // ...
        $this->messages = ['Berhasil ditambahkan!'];
    };
    return $this->callFunction($func, null, 'location.index');
}
```

### Service Pattern
Semua service extend `MasterService`. Service berisi logic bisnis dan query Eloquent, controller tidak boleh langsung query model kecuali untuk datatable.

### API Response Format
```json
{
    "data": {},
    "messages": ["Success"],
    "error": false
}
```

### Datatable API Response Format
```json
{
    "page": 1,
    "pageCount": 5,
    "sortField": "name",
    "sortOrder": "asc",
    "totalCount": 50,
    "data": []
}
```

### Authorization (Policy)
Semua policy method menggunakan naming convention: `readPolicy`, `createPolicy`, `updatePolicy`, `deletePolicy`. Dipanggil via `Gate::authorize('readPolicy', ModelClass::class)`.

Permission naming convention: `{module}-{action}`, contoh: `location-read`, `user-create`, `role-update`.

## Routes

### Web Routes (`routes/web.php`)
- Public: `/`, `/landing/filter/{categoryId}`, `/payment/submit`, `/payment/summary/{confirmation}`, `/payment/tracking`
- Auth protected: `/admin/payment-confirmations/*`, `/location/*`, `/permission-groups/*`, `/permissions/*`, `/roles/*`, `/users/*`

### API Routes (`routes/api.php`)
- Prefix: `/api/v1/`
- Public: `/auth/signin`, `/server-time`, `/locations/block/{blockId}`, `/payment/track/{confirmationCode}`
- Auth (Sanctum): semua route lainnya

## Models

### User
- Fields: `name`, `email`, `password`, `location_id`, `url_image`, `role_id`, `is_active`
- Relations: `belongsTo Location`, `belongsTo Role`
- Traits: `HasRoles` (Spatie), `HasApiTokens` (Sanctum)
- Accessors: `is_active_color`, `is_active_name`
- Scope: `active()`

### Location
- Fields: `name`, `address`, `phone`, `status`, `latitude`, `longitude`, `radius`, `location_category_id`
- Relations: `belongsTo LocationCategory`
- Accessors: `status_color`, `status_name`
- Scope: `active()`

### LocationCategory
- Fields: `name`, `type`, `description`, `url_icon`, `sort_order`
- `type` bisa `block` atau lainnya

### PaymentConfirmation
- Fields: `confirmation_code`, `location_id`, `location_category_id`, `payer_name`, `month`, `year`, `amount`, `proof_file`, `notes`, `status`
- Relations: `belongsTo Location`, `belongsTo LocationCategory`
- Status values: `butuh_pengecekan`, `sudah_dicek`
- Accessor: `status_label`
- `confirmation_code` format: `PAY-XXXXXXXX` (random 8 char uppercase)

### Permission
- Extend `Spatie\Permission\Models\Permission`
- Fields tambahan: `slug`, `permission_group_id`
- Relations: `belongsTo PermissionGroup`

### PermissionGroup
- Fields: `name`, `slug`, `is_active`
- Relations: `hasMany Permission`
- Scope: `active()`

### Role
- Extend `Spatie\Permission\Models\Role`
- Fields tambahan: `slug`, `is_active`
- Scope: `active()`

## Menu Config
Menu dikonfigurasi di `config/menus.php` dan dirender via `MenuHelper`. Setiap menu item bisa punya `permission`, `route`, `pathUrl`, dan `children`.

## Auth
- Web auth: Laravel Fortify (session-based)
- API auth: Laravel Sanctum (token-based)
- Login API: `POST /api/v1/auth/signin` dengan field `email`, `password`, `access_token_type`
- Token dibuat dengan abilities dari permissions user via roles

## Coding Conventions
- Bahasa Indonesia untuk pesan sukses/error di controller (`$this->messages`)
- Gunakan `ILIKE` bukan `LIKE` untuk case-insensitive search (PostgreSQL)
- Breadcrumbs selalu di-set di setiap web controller method
- File upload disimpan ke `storage/app/public/` via `Storage::disk('public')`
- Proof file payment disimpan di `payment-proofs/`
