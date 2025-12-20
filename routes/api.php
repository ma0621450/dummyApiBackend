<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ApiKeyController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    // Public: Generate Key
    Route::post('/keys', [ApiKeyController::class, 'store']);

    // Protected Routes
    Route::middleware('auth.apikey')->group(function () {
        Route::get('/brands', [BrandController::class, 'index']);
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product:slug}', [ProductController::class, 'show']);
        Route::get('/products/{id}/variants', [ProductController::class, 'variants']);
        Route::get('/products/{id}/variants/{sku}', [ProductController::class, 'variantBySku']);
    });
});