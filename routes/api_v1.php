<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;


Route::apiResource('categories', CategoryController::class);

Route::post(
    'categories/{category}/restore',
    [CategoryController::class, 'restore']
)->withTrashed();
