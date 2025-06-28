<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Product\BrandController;
use App\Http\Controllers\Api\Product\CategorieController;
use App\Http\Controllers\Api\Supplier\SupplierController;
use App\Http\Controllers\Api\Warehouse\WarehouseController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Purchase\PurchaseController;
use App\Http\Controllers\Api\Purchase\PurchaseItemController;
use App\Http\Controllers\Api\Stock\StockController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('brands', BrandController::class);
Route::apiResource('caregoties', CategorieController::class);
Route::apiResource('suppliers', SupplierController::class);
Route::apiResource('warehouses', WarehouseController::class);
Route::apiResource('products', ProductController::class);
Route::apiResource('purchases', PurchaseController::class);
Route::apiResource('purchasesitems', PurchaseItemController::class);
Route::apiResource('stocks', StockController::class);
Route::put('purchases/{id}/status', [PurchaseController::class, 'updateStatus']);

