<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\LocationController;
use App\Http\Controllers\Web\PermissionController;
use App\Http\Controllers\Web\PermissionGroupController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\UploadsController;
use App\Http\Controllers\Web\User\UserController;
use App\Http\Controllers\Web\PaymentConfirmationController;
use App\Http\Controllers\Web\Rab\RabController as WebRabController;
use App\Http\Controllers\Web\Rab\RabDashboardController as WebRabDashboardController;
use App\Http\Controllers\Web\Rab\RabExportController as WebRabExportController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('rab.index')
        : redirect()->route('login');
})->name('index');


Route::middleware(['auth'/*, 'verified'*/])->group(function () {


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

    Route::get('/uploads/{path}', UploadsController::class)->where('path', '.*');

    Route::prefix("/rab")->as("rab.")->group(function () {
        Route::get('', [WebRabController::class, 'index'])->name('index');
        Route::get('/create', [WebRabController::class, 'create'])->name('create');
        Route::post('/', [WebRabController::class, 'store'])->name('store');
        // Dashboard & export must be before /{rab} to avoid route parameter conflict
        Route::get('/dashboard', [WebRabDashboardController::class, 'index'])->name('dashboard');
        Route::get('/export', [WebRabExportController::class, 'export'])->name('export');
        Route::get('/{rab}', [WebRabController::class, 'show'])->name('show');
        Route::get('/{rab}/edit', [WebRabController::class, 'edit'])->name('edit');
        Route::put('/{rab}', [WebRabController::class, 'update'])->name('update');
    });
});
