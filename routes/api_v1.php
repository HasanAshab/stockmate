<?php

use App\Enums\Role;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

// Auth
Route::post('auth/token/login', [AuthController::class, 'tokenLogin'])->name('auth.token-login');

// Config
Route::get('config/enums', [ConfigController::class, 'enums'])->name('config.enums');

Route::middleware('auth:sanctum')->group(function () {
    // Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::patch('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::patch('/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-as-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

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
    Route::patch('warehouses/{warehouse}/stock/{warehouseStock}', [WarehouseController::class, 'updateStock'])
        ->name('warehouses.update-stock');

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

// Payment Callbacks
Route::controller(PaymentCallbackController::class)
    ->prefix('payment')
    ->name('payment.')
    ->withoutMiddleware(PreventRequestForgery::class)
    ->group(function () {
        Route::post('success', 'success')->name('success');
        Route::post('fail', 'fail')->name('fail');
        Route::post('cancel', 'cancel')->name('cancel');
        Route::post('ipn', 'ipn')->name('ipn');
    });
