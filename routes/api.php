<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\StockEntryController;
use App\Http\Controllers\Api\ProductController;
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::middleware('auth.token')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);

    // ── Menu ──────────────────────────────────────────────────────────
    Route::get('/menu',            [MenuController::class, 'index']);
    Route::get('/menu/create',     [MenuController::class, 'create']);
    Route::get('/menu/{id}',       [MenuController::class, 'show']);

    Route::middleware('role:admin')->group(function () {
        Route::post  ('/menu',             [MenuController::class, 'store']);
        Route::put   ('/menu/{id}',        [MenuController::class, 'update']);
        Route::delete('/menu/{id}',        [MenuController::class, 'destroy']);
        Route::patch ('/menu/{id}/toggle', [MenuController::class, 'toggle']);
    });

    // ── Categories ────────────────────────────────────────────────────
    Route::get('/categories',      [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::middleware('role:admin')->group(function () {
        Route::post  ('/categories',        [CategoryController::class, 'store']);
        Route::put   ('/categories/{id}',   [CategoryController::class, 'update']);
        Route::delete('/categories/{id}',   [CategoryController::class, 'destroy']);
    });

    // ── Orders ────────────────────────────────────────────────────────
    Route::get ('/orders/my',               [OrderController::class, 'myOrders']);
    Route::post('/orders',                  [OrderController::class, 'store']);

    Route::middleware('role:admin,cashier')->group(function () {
        Route::get  ('/orders',               [OrderController::class, 'index']);
        Route::get  ('/orders/{id}',          [OrderController::class, 'show']);
        Route::patch('/orders/{id}/status',   [OrderController::class, 'updateStatus']);
    });

    // ── Inventory ─────────────────────────────────────────────────────
    Route::middleware('role:admin,cashier')->group(function () {
        Route::get('/inventory',               [InventoryController::class, 'index']);
        Route::get('/inventory/low-stock',     [InventoryController::class, 'lowStock']);
        Route::get('/inventory/{id}/logs',     [InventoryController::class, 'logs']);
        Route::patch('/inventory/{id}/adjust', [InventoryController::class, 'adjust']);
    });

    Route::middleware('role:admin')->group(function () {
        Route::post('/inventory/bulk-restock', [InventoryController::class, 'bulkRestock']);
    });

    // ── Reports ───────────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/reports/summary',            [ReportController::class, 'summary']);
        Route::get('/reports/sales',              [ReportController::class, 'sales']);
        Route::get('/reports/top-items',          [ReportController::class, 'topItems']);
        Route::get('/reports/category-breakdown', [ReportController::class, 'categoryBreakdown']);
        Route::get('/reports/order-trends',       [ReportController::class, 'orderTrends']);
    });

    // ── Suppliers & Stock Entries ─────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create']);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('stock',     StockEntryController::class);

        Route::get('/products/create',  [ProductController::class, 'create']);
        Route::apiResource('products',  ProductController::class);
    });

});