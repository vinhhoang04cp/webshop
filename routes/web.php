<?php

use App\Http\Controllers\Web\AuthController;
use Illuminate\Support\Facades\Route;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Dashboard Routes
Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    
    // Categories CRUD (using Web Controller with Http facade to call API)
    Route::get('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'index'])
        ->name('dashboard.categories.index');
    Route::get('/dashboard/categories/create', [\App\Http\Controllers\Web\CategoryController::class, 'create'])
        ->name('dashboard.categories.create')->middleware('role:admin');
    Route::post('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'store'])
        ->name('dashboard.categories.store')->middleware('role:admin');
    Route::get('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'show'])
        ->name('dashboard.categories.show');
    Route::get('/dashboard/categories/{id}/edit', [\App\Http\Controllers\Web\CategoryController::class, 'edit'])
        ->name('dashboard.categories.edit')->middleware('role:admin');
    Route::put('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'update'])
        ->name('dashboard.categories.update')->middleware('role:admin');
    Route::delete('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'destroy'])
        ->name('dashboard.categories.destroy')->middleware('role:admin');
    
    // Products CRUD (using Web Controller with Http facade to call API)
    Route::get('/dashboard/products', [\App\Http\Controllers\Web\ProductController::class, 'index'])
        ->name('dashboard.products.index');
    Route::get('/dashboard/products/create', [\App\Http\Controllers\Web\ProductController::class, 'create'])
        ->name('dashboard.products.create')->middleware('role:admin');
    Route::post('/dashboard/products', [\App\Http\Controllers\Web\ProductController::class, 'store'])
        ->name('dashboard.products.store')->middleware('role:admin');
    Route::get('/dashboard/products/{id}', [\App\Http\Controllers\Web\ProductController::class, 'show'])
        ->name('dashboard.products.show');
    Route::get('/dashboard/products/{id}/edit', [\App\Http\Controllers\Web\ProductController::class, 'edit'])
        ->name('dashboard.products.edit')->middleware('role:admin');
    Route::put('/dashboard/products/{id}', [\App\Http\Controllers\Web\ProductController::class, 'update'])
        ->name('dashboard.products.update')->middleware('role:admin');
    Route::delete('/dashboard/products/{id}', [\App\Http\Controllers\Web\ProductController::class, 'destroy'])
        ->name('dashboard.products.destroy')->middleware('role:admin');
    
    // Các route dashboard khác sẽ được thêm vào đây
    // Route::get('/dashboard/orders', [OrderController::class, 'index'])->name('dashboard.orders');
    // Route::get('/dashboard/users', [UserController::class, 'index'])->name('dashboard.users');
});
