<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Category CRUD routes
Route::resource('categories', CategoryController::class);

// Product CRUD routes
Route::resource('products', ProductController::class);
