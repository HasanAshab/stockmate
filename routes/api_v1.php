<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Enums\Role;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::apiResource('categories', CategoryController::class);
    Route::get(
        'categories/trashed',
        [CategoryController::class, 'trashed']
    )->name('categories.trashed');
    Route::post(
        'categories/{category}/restore',
        [CategoryController::class, 'restore']
    )->withTrashed()->name('categories.restore');

    Route::apiResource('suppliers', SupplierController::class);
    Route::get(
        'suppliers/trashed',
        [SupplierController::class, 'trashed']
    )->name('suppliers.trashed');
    Route::post(
        'suppliers/{supplier}/restore',
        [SupplierController::class, 'restore']
    )->withTrashed()->name('suppliers.restore');

    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-logs', StockLogController::class)->only(['index', 'store']);

    Route::middleware('role:' . Role::Admin->value)->group(function () {
        Route::apiResource('users', UserController::class)->except(['destroy']);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    });

    Route::apiSingleton('profile', ProfileController::class);
});
