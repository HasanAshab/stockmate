<?php

use App\Enums\Role;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Categories
    Route::apiResource('categories', CategoryController::class);
    Route::get(
        'categories/trashed',
        [CategoryController::class, 'trashed']
    )->name('categories.trashed');
    Route::post(
        'categories/{category}/restore',
        [CategoryController::class, 'restore']
    )->withTrashed()->name('categories.restore');

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class);
    Route::get(
        'suppliers/trashed',
        [SupplierController::class, 'trashed']
    )->name('suppliers.trashed');
    Route::post(
        'suppliers/{supplier}/restore',
        [SupplierController::class, 'restore']
    )->withTrashed()->name('suppliers.restore');

    // Products
    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-logs', StockLogController::class)->only(['index', 'store']);

    Route::get('stock-logs/export/{format}', [StockLogController::class, 'export'])
        ->name('stock-logs.export');

    Route::post('stock-logs/transfer', [StockLogController::class, 'transfer'])
        ->name('stock-logs.transfer');

    // Warehouses
    Route::apiResource('warehouses', WarehouseController::class);
    Route::get('warehouses/{warehouse}/stock', [WarehouseController::class, 'stock'])
        ->name('warehouses.stock');

    // Sales Orders (Admin and Staff)
    Route::apiResource('sales-orders', SalesOrderController::class)->only(['index', 'store', 'show']);
    Route::post('sales-orders/{salesOrder}/initiate-payment', [SalesOrderController::class, 'initiatePayment'])
        ->name('sales-orders.initiate-payment');

    Route::middleware('role:'.Role::Admin->value)->group(function () {
        // Users
        Route::apiResource('users', UserController::class)->except(['destroy']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Purchase Orders (Admin only)
        Route::apiResource('purchase-orders', PurchaseOrderController::class)->except(['destroy']);
        Route::patch('purchase-orders/{purchaseOrder}/mark-ordered', [PurchaseOrderController::class, 'markOrdered'])
            ->name('purchase-orders.mark-ordered');
        Route::patch('purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])
            ->name('purchase-orders.cancel');

        // Sales Order Cancel (Admin only)
        Route::patch('sales-orders/{salesOrder}/cancel', [SalesOrderController::class, 'cancel'])
            ->name('sales-orders.cancel');
    });

    // Purchase Order Receiving (Admin and Staff)
    Route::post('purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');

    // Profile
    Route::apiSingleton('profile', ProfileController::class);
});

// Payment Callbacks (No auth required)
Route::post('payment/success', [PaymentCallbackController::class, 'success'])->name('payment.success');
Route::post('payment/fail', [PaymentCallbackController::class, 'fail'])->name('payment.fail');
Route::post('payment/cancel', [PaymentCallbackController::class, 'cancel'])->name('payment.cancel');
