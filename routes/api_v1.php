<?php

use App\Enums\Role;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
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

    Route::middleware('role:'.Role::Admin->value)->group(function () {
        // Users
        Route::apiResource('users', UserController::class)->except(['destroy']);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    // Profile
    Route::apiSingleton('profile', ProfileController::class);
});
