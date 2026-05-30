<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockLogController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Enums\Role;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('categories', CategoryController::class);

    Route::post(
        'categories/{category}/restore',
        [CategoryController::class, 'restore']
    )->withTrashed();

    Route::apiResource('suppliers', SupplierController::class);

    Route::post(
        'suppliers/{supplier}/restore',
        [SupplierController::class, 'restore']
    )->withTrashed();

    Route::apiResource('products', ProductController::class);
    Route::apiResource('stock-logs', StockLogController::class)->only(['index', 'store']);

    Route::middleware('role:' . Role::Admin->value)->group(function () {
        Route::apiResource('users', UserController::class)->except(['destroy']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
    });

    Route::get('profile', [ProfileController::class, 'show']);
    Route::put('profile', [ProfileController::class, 'update']);
});
