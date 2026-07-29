<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentCallbackController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseStockController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

// Auth
Route::controller(AuthController::class)
    ->prefix('auth')
    ->name('auth.')
    ->group(function () {
        Route::post('register', 'register')->name('register')->middleware('throttle:register');
        Route::post('login', 'login')->name('login')->middleware('throttle:login');
        Route::post('social', 'socialLogin')->name('social');
        Route::post('verify', 'verify')->name('verification.verify')->middleware('throttle:verification');
        Route::post('verification-notification', 'resendVerification')->name('verification.send')->middleware('throttle:verification');
        Route::post('forgot-password', 'forgotPassword')->name('forgot-password')->middleware('throttle:password-reset');
        Route::post('reset-password', 'resetPassword')->name('reset-password')->middleware('throttle:password-reset');
    });

// Config
Route::get('config/enums', [ConfigController::class, 'enums'])->name('config.enums');

Route::middleware('auth:sanctum')->scopeBindings()->group(function () {
    // Auth
    Route::controller(AuthController::class)
        ->prefix('auth')
        ->name('auth.')
        ->group(function () {
            Route::post('logout', 'logout')->name('logout');
            Route::post('change-password', 'changePassword')->name('change-password');
        });

    // Dashboard
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Notifications
    Route::controller(NotificationController::class)
        ->prefix('notifications')
        ->name('notifications.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/unread', 'unread')->name('unread');
            Route::get('/unread/count', 'unreadCount')->name('unread.count');
            Route::patch('/{id}/mark-as-read', 'markAsRead')->name('mark-as-read');
            Route::patch('/mark-all-as-read', 'markAllAsRead')->name('mark-all-as-read');
            Route::delete('/{id}', 'destroy')->name('destroy');
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
        ->name('stock-logs.export')
        ->middleware('throttle:export');

    Route::post('stock-logs/transfer', [StockLogController::class, 'transfer'])
        ->name('stock-logs.transfer');

    // Warehouses
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('warehouses.stocks', WarehouseStockController::class)
        ->only(['index', 'show', 'update']);

    // Sales Orders
    Route::apiResource('sales-orders', SalesOrderController::class)->only(['index', 'store', 'show']);
    Route::post('sales-orders/{sales_order}/initiate-payment', [SalesOrderController::class, 'initiatePayment'])
        ->name('sales-orders.initiate-payment');

    // Roles and Permissions
    Route::apiResource('roles', RoleController::class)->only('index');
    Route::apiResource('permissions', PermissionController::class)->only('index');

    // Users
    Route::apiResource('users', UserController::class)->except(['destroy']);
    Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::put('users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.assign-roles');
    Route::put('users/{user}/permissions', [UserController::class, 'assignPermissions'])->name('users.assign-permissions');

    // Activity Logs
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Purchase Orders
    Route::apiResource('purchase-orders', PurchaseOrderController::class)->except(['destroy']);
    Route::patch('purchase-orders/{purchase_order}/mark-ordered', [PurchaseOrderController::class, 'markOrdered'])
        ->name('purchase-orders.mark-ordered');
    Route::patch('purchase-orders/{purchase_order}/cancel', [PurchaseOrderController::class, 'cancel'])
        ->name('purchase-orders.cancel');

    // Sales Order Cancel
    Route::patch('sales-orders/{sales_order}/cancel', [SalesOrderController::class, 'cancel'])
        ->name('sales-orders.cancel');

    // Purchase Order Receiving
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])
        ->name('purchase-orders.receive');

    // Profile
    Route::apiSingleton('profile', ProfileController::class);

    // Search
    Route::get('search', [SearchController::class, 'searchAll'])->middleware('throttle:search');

    Route::get('search/{scope}', [SearchController::class, 'searchScope'])
        ->whereIn('scope', array_keys(config('search.scopes')))
        ->middleware('throttle:search');
});

// Payment Callbacks
Route::controller(PaymentCallbackController::class)
    ->prefix('payment')
    ->name('payment.')
    ->withoutMiddleware(PreventRequestForgery::class)
    ->middleware('throttle:webhooks')
    ->group(function () {
        Route::post('success', 'success')->name('success');
        Route::post('fail', 'fail')->name('fail');
        Route::post('cancel', 'cancel')->name('cancel');
        Route::post('ipn', 'ipn')->name('ipn');
    });
