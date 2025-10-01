<?php

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Category API routes
Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::post('/', [CategoryController::class, 'store']);
    Route::get('/{id}', [CategoryController::class, 'show']);
    Route::put('/{id}', [CategoryController::class, 'update']);
    Route::delete('/{id}', [CategoryController::class, 'destroy']);
});
// Product API routes
Route::prefix('products')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\ProductController::class, 'store']);
    Route::get('/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);
    Route::put('/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update']);
    Route::delete('/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy']);
});
// Order API routes
Route::prefix('orders')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\OrderController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\OrderController::class, 'store']);
    Route::get('/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
    Route::put('/{id}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
    Route::delete('/{id}', [\App\Http\Controllers\Api\OrderController::class, 'destroy']);
});
// OrderItem API routes
Route::prefix('order-items')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\OrderItemController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\OrderItemController::class, 'store']);
    Route::get('/{id}', [\App\Http\Controllers\Api\OrderItemController::class, 'show']);
    Route::put('/{id}', [\App\Http\Controllers\Api\OrderItemController::class, 'update']);
    Route::delete('/{id}', [\App\Http\Controllers\Api\OrderItemController::class, 'destroy']);
});
// ProductDetail API routes
Route::prefix('product-details')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ProductDetailController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\ProductDetailController::class, 'store']);
    Route::get('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'show']);
    Route::put('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'update']);
    Route::delete('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'destroy']);
});
Route::prefix('carts')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\CartController::class, 'store']);
    Route::get('/{id}', [\App\Http\Controllers\Api\CartController::class, 'show']);
    Route::put('/{id}', [\App\Http\Controllers\Api\CartController::class, 'update']);
    Route::delete('/{id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
});