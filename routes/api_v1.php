<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockLogController;
use Illuminate\Support\Facades\Route;

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
