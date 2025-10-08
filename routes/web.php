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
    
    // Categories CRUD (using Web Controller with traditional forms)
    Route::get('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'index'])
        ->name('dashboard.categories.index');
    Route::post('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'store'])
        ->name('dashboard.categories.store')->middleware('role:admin');
    Route::put('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'update'])
        ->name('dashboard.categories.update')->middleware('role:admin');
    Route::delete('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'destroy'])
        ->name('dashboard.categories.destroy')->middleware('role:admin');
    
    // Các route dashboard khác sẽ được thêm vào đây
    // Route::get('/dashboard/products', [ProductController::class, 'index'])->name('dashboard.products');
    // Route::get('/dashboard/orders', [OrderController::class, 'index'])->name('dashboard.orders');
    // Route::get('/dashboard/users', [UserController::class, 'index'])->name('dashboard.users');
});
