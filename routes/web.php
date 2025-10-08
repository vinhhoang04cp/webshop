<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;

// Redirect root to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/ajax/login', [AuthController::class, 'ajaxLogin'])->name('ajax.login');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/ajax/register', [AuthController::class, 'ajaxRegister'])->name('ajax.register');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Dashboard Routes
Route::middleware(['auth', 'role:admin,manager'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
        // Categories CRUD (web)
        Route::get('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'index'])
            ->name('dashboard.categories');
        // Proxy admin actions to API controller but protected by web session + role middleware
        Route::post('/dashboard/categories', [\App\Http\Controllers\Api\CategoryController::class, 'store'])
            ->name('dashboard.categories.store')->middleware('role:admin');
        Route::put('/dashboard/categories/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'update'])
            ->name('dashboard.categories.update')->middleware('role:admin');
        Route::delete('/dashboard/categories/{id}', [\App\Http\Controllers\Api\CategoryController::class, 'destroy'])
            ->name('dashboard.categories.destroy')->middleware('role:admin');
    // Các route dashboard khác sẽ được thêm vào đây
    // Route::get('/dashboard/products', [ProductController::class, 'index'])->name('dashboard.products');
    // Route::get('/dashboard/categories', [CategoryController::class, 'index'])->name('dashboard.categories');
    // Route::get('/dashboard/orders', [OrderController::class, 'index'])->name('dashboard.orders');
    // Route::get('/dashboard/users', [UserController::class, 'index'])->name('dashboard.users');
});
