<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;

Route::get('/', function () {
    return redirect()->route('categories.index');
});

// Category CRUD routes
Route::resource('categories', CategoryController::class);
