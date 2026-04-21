<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\PermissionGroupController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\PaymentConfirmationController;
use App\Http\Controllers\Api\V1\Rab\RabController as ApiRabController;
use App\Http\Controllers\Api\V1\Rab\RabApprovalController;
use App\Http\Controllers\Api\V1\Rab\RabDashboardController as ApiRabDashboardController;
use App\Http\Controllers\Api\V1\PurchaseRequest\PurchaseRequestController as ApiPurchaseRequestController;
use App\Http\Controllers\Api\V1\PurchaseRequest\PurchaseRequestApprovalController as ApiPurchaseRequestApprovalController;
use App\Http\Controllers\Api\V1\PurchaseRequest\PurchaseRequestPurchasingController as ApiPurchaseRequestPurchasingController;
use App\Http\Controllers\Api\V1\PurchaseRequest\PurchaseRequestDashboardController as ApiPurchaseRequestDashboardController;

// Route::post('/signin', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::as("api.")->group(function () {
    Route::prefix("/v1")->as("v1.")->group(function () {

        Route::prefix("/auth")->as("auth.")->group(function () {
            Route::post('/signin', [AuthController::class, 'login']);
        });

        // Route::prefix("/user")->as("user.")->group(function () {
        //     Route::middleware(['auth:sanctum', 'ability:users-create'])->post('', [UserController::class, 'create']);
        // });

        Route::get('/server-time', function () {
            return response()->json([
                'time' => now()->format('H:i:s'),
            ]);
        })->name('server-time');

        Route::get('/locations/block/{blockId}', [LocationController::class, 'getLocationsByBlock'])->name('locations.block');
        Route::get('/payment/track/{confirmationCode}', [LocationController::class, 'trackPayment'])->name('payment.track');

        Route::middleware(['auth:sanctum'])->group(function () {

            //Route::post('/notifications/mark-all-read', [BaseNotificationController::class, 'markAllAsRead'])->name('notifications.markAllRead');

            Route::prefix('/location')->as('location.')->group(function () {
                Route::get('/datatable', [LocationController::class, 'dataTable'])->name('datatable');
                Route::get('/get-combobox', [LocationController::class, 'getCombobox'])->name('get-combobox');
                Route::delete('/{location}', [LocationController::class, 'destroy'])->name('destroy');
            });


            Route::prefix('/permission-groups')->as('permission-groups.')->group(function () {
                Route::get('/datatable', [PermissionGroupController::class, 'dataTable'])->name('datatable');
                Route::delete('/{permissionGroups}', [PermissionGroupController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('/permissions')->as('permissions.')->group(function () {
                Route::get('/datatable', [PermissionController::class, 'dataTable'])->name('datatable');
            });

            Route::prefix('/roles')->as('roles.')->group(function () {
                Route::get('/datatable', [RoleController::class, 'dataTable'])->name('datatable');
                Route::get('/get-all-role', [RoleController::class, 'getAllRole'])->name('get-all-role');
            });

            Route::prefix('/users')->as('users.')->group(function () {
                Route::get('/datatable', [UserController::class, 'dataTable'])->name('datatable');
                Route::get('/get-combobox', [UserController::class, 'getCombobox'])->name('get-combobox');

            });

            Route::prefix('/payment-confirmations')->as('payment-confirmations.')->group(function () {
                Route::get('/datatable', [PaymentConfirmationController::class, 'dataTable'])->name('datatable');
            });

            Route::prefix('/rab')->as('rab.')->group(function () {
                Route::get('/datatable', [ApiRabController::class, 'dataTable'])->name('datatable');
                Route::post('/{rab}/submit', [ApiRabController::class, 'submit'])->name('submit');
                Route::post('/{rab}/cancel', [ApiRabController::class, 'cancel'])->name('cancel');
                Route::post('/{rab}/approve', [RabApprovalController::class, 'approve'])->name('approve');
                Route::post('/{rab}/reject', [RabApprovalController::class, 'reject'])->name('reject');
                Route::get('/dashboard/summary', [ApiRabDashboardController::class, 'summary'])->name('dashboard.summary');
                Route::get('/dashboard/chart', [ApiRabDashboardController::class, 'chart'])->name('dashboard.chart');
            });

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
        });
    });
});
