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
use App\Http\Controllers\Api\Stock\StocktransferController;
use App\Http\Controllers\Api\Customer\CustomerController;
use App\Http\Controllers\Api\Expense\ExpenseCategorieController;
use App\Http\Controllers\Api\Expense\ExpenseController;
use App\Http\Controllers\Api\Sale\SaleController;
use App\Http\Controllers\Api\Sale\SaleItemController;
use App\Http\Controllers\Api\DashboardController;

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
Route::apiResource('stocktransfers', StocktransferController::class);
Route::patch('/stocktransfers/{id}/status', [StocktransferController::class, 'updateStatus']);
Route::apiResource('customers', CustomerController::class);
Route::apiResource('expensecategories', ExpenseCategorieController::class);
Route::apiResource('expenses', ExpenseController::class);
Route::apiResource('sales', SaleController::class);
Route::apiResource('saleitems', SaleItemController::class);
Route::put('sales/{id}/status', [SaleController::class, 'updateStatus']);
Route::apiResource('dashboards', DashboardController::class);


