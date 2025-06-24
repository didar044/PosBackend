<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Product\BrandController;
use App\Http\Controllers\Api\Product\CategorieController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::apiResource('brands', BrandController::class);
Route::apiResource('caregoties', CategorieController::class);
