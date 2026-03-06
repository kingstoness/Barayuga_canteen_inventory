<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\StockEntryController;

/*
|--------------------------------------------------------------------------
| Public Routes (no token required)
|--------------------------------------------------------------------------
*/
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| Protected Routes (token required in Authorization: Bearer header)
|--------------------------------------------------------------------------
| NOTE: /products/create and /suppliers/create must come BEFORE
| apiResource so Laravel doesn't treat "create" as an {id}.
|--------------------------------------------------------------------------
*/
Route::middleware('auth.token')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);

    // Product routes
    Route::get('/products/create', [ProductController::class, 'create']);
    Route::apiResource('products',  ProductController::class);

    // Supplier routes
    Route::get('/suppliers/create', [SupplierController::class, 'create']);
    Route::apiResource('suppliers', SupplierController::class);

    // Stock routes
    Route::apiResource('stock', StockEntryController::class);
});