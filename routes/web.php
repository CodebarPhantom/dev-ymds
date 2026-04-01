<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\PermissionGroupController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\Web\FormEntryController;
use App\Http\Controllers\Web\User\UserController;
use App\Http\Controllers\Web\SummaryController;
use App\Http\Controllers\Web\LandingController;
use App\Http\Controllers\Web\PaymentConfirmationController;

Route::get('/', [LandingController::class, 'index'])->name('index');
Route::get('/landing/filter/{categoryId}', [LandingController::class, 'filter'])->name('landing.filter');

Route::post('/payment/submit', [LandingController::class, 'submitPayment'])->name('payment.submit');
Route::get('/payment/summary/{confirmation}', [LandingController::class, 'paymentSummary'])->name('payment.summary');
Route::get('/payment/tracking', function (Request $request) {
    $code = $request->get('code');
    return view('payment-tracking', compact('code'));
})->name('payment.tracking');


Route::middleware(['auth'/*, 'verified'*/])->group(function () {


    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('/admin/payment-confirmations')->as('admin.payment-confirmations.')->group(function () {
        Route::get('', [PaymentConfirmationController::class, 'index'])->name('index');
        Route::patch('/{confirmation}/status', [PaymentConfirmationController::class, 'updateStatus'])->name('update-status');
        Route::get('/export', [PaymentConfirmationController::class, 'export'])->name('export');
    });

    Route::prefix("/location")->as("location.")->group(function () {
        Route::get('', [LocationController::class, 'index'])->name('index');
        Route::get('/create', [LocationController::class, 'create'])->name('create');
        Route::post('/', [LocationController::class, 'store'])->name('store');
        Route::get('/{location}', [LocationController::class, 'show'])->name('show');
        Route::get('/{location}/edit', [LocationController::class, 'edit'])->name('edit');
        Route::put('/{location}', [LocationController::class, 'update'])->name('update');
    });

    Route::prefix("/permission-groups")->as("permission-groups.")->group(function () {
        Route::get('', [PermissionGroupController::class, 'index'])->name('index');
        Route::get('/create', [PermissionGroupController::class, 'create'])->name('create');
        Route::post('/', [PermissionGroupController::class, 'store'])->name('store');
        Route::get('/{permissionGroups}', [PermissionGroupController::class, 'show'])->name('show');
        Route::get('/{permissionGroups}/edit', [PermissionGroupController::class, 'edit'])->name('edit');
        Route::put('/{permissionGroups}', [PermissionGroupController::class, 'update'])->name('update');
    });

    Route::prefix("/permissions")->as("permissions.")->group(function () {
        Route::get('', [PermissionController::class, 'index'])->name('index');
        Route::get('/create', [PermissionController::class, 'create'])->name('create');
        Route::post('/', [PermissionController::class, 'store'])->name('store');
        Route::get('/{permissions}', [PermissionController::class, 'show'])->name('show');
        Route::get('/{permissions}/edit', [PermissionController::class, 'edit'])->name('edit');
        Route::put('/{permissions}', [PermissionController::class, 'update'])->name('update');
    });

    Route::prefix("/roles")->as("roles.")->group(function () {
        Route::get('', [RoleController::class, 'index'])->name('index');
        Route::get('/create', [RoleController::class, 'create'])->name('create');
        Route::post('/', [RoleController::class, 'store'])->name('store');
        Route::get('/{roles}', [RoleController::class, 'show'])->name('show');
        Route::get('/{roles}/edit', [RoleController::class, 'edit'])->name('edit');
        Route::put('/{roles}', [RoleController::class, 'update'])->name('update');
    });
    //});

    Route::prefix("/users")->as("users.")->group(function () {
        Route::get('', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{users}', [UserController::class, 'show'])->name('show');
        Route::get('/{users}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{users}', [UserController::class, 'update'])->name('update');
    });

    Route::prefix("/forms")->as("forms.")->group(function () {
        Route::get('/{formCode}/create', [FormEntryController::class, 'create'])
            ->name('create');
        Route::post('/{formCode}/store', [FormEntryController::class, 'store'])
            ->name('store');

        // Alias khusus untuk form tertentu
        Route::get('/tahsin-tilawah/create', [FormEntryController::class, 'create'])
            ->defaults('formCode', 'tahsin-tilawah')
            ->name('create.tahsin-tilawah');

        Route::get('/tahsin-tilawah/store', [FormEntryController::class, 'store'])
            ->defaults('formCode', 'tahsin-tilawah')
            ->name('store.tahsin-tilawah');
    });

    Route::prefix("/summaries")->as("summaries.")->group(function () {
        Route::get('{formCode}', [SummaryController::class, 'index'])->name('index');
        Route::get('{formCode}/{entryId}', [SummaryController::class, 'show'])->name('show');

    });

    Route::get('/uploads/{path}', UploadsController::class)->where('path', '.*');
});
