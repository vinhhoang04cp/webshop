<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CustomerCartController;
use App\Http\Controllers\Web\CustomerProductController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\UserManagementController;
use Illuminate\Support\Facades\Route;

// Home page - Trang chủ cho khách hàng
Route::get('/', [HomeController::class, 'index'])->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Customer facing routes - Các route dành cho khách hàng
Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [CustomerProductController::class, 'search'])->name('products.search');
Route::get('/product/{id}', [CustomerProductController::class, 'show'])->name('product.show');
Route::get('/category/{id}', [CustomerProductController::class, 'category'])->name('category.show');

// Cart routes - Giỏ hàng
Route::get('/cart', [CustomerCartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{productId}', [CustomerCartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{cartItemId}', [CustomerCartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cartItemId}', [CustomerCartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CustomerCartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/checkout', [CustomerCartController::class, 'checkout'])->name('cart.checkout');

// Protected Dashboard Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard chính - cần quyền dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')
        ->middleware('role:dashboard');

    // Categories create/edit/delete - chỉ admin (PHẢI ĐẶT TRƯỚC {id})
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/categories/create', [\App\Http\Controllers\Web\CategoryController::class, 'create'])
            ->name('dashboard.categories.create');
        Route::post('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'store'])
            ->name('dashboard.categories.store');
        Route::get('/dashboard/categories/{id}/edit', [\App\Http\Controllers\Web\CategoryController::class, 'edit'])
            ->name('dashboard.categories.edit');
        Route::put('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'update'])
            ->name('dashboard.categories.update');
        Route::delete('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'destroy'])
            ->name('dashboard.categories.destroy');
    });

    // Categories CRUD - cần quyền manager trở lên (ĐẶT SAU create)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/categories', [\App\Http\Controllers\Web\CategoryController::class, 'index'])
            ->name('dashboard.categories.index');
        Route::get('/dashboard/categories/{id}', [\App\Http\Controllers\Web\CategoryController::class, 'show'])
            ->name('dashboard.categories.show');
    });

    // Products CRUD - cần quyền manager trở lên
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/products', [\App\Http\Controllers\Web\ProductController::class, 'index'])
            ->name('dashboard.products.index');
    });

    // Products create/edit/delete - chỉ admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/products/create', [\App\Http\Controllers\Web\ProductController::class, 'create'])
            ->name('dashboard.products.create');
        Route::post('/dashboard/products', [\App\Http\Controllers\Web\ProductController::class, 'store'])
            ->name('dashboard.products.store');
        Route::get('/dashboard/products/{id}/edit', [\App\Http\Controllers\Web\ProductController::class, 'edit'])
            ->name('dashboard.products.edit');
        Route::put('/dashboard/products/{id}', [\App\Http\Controllers\Web\ProductController::class, 'update'])
            ->name('dashboard.products.update');
        Route::delete('/dashboard/products/{id}', [\App\Http\Controllers\Web\ProductController::class, 'destroy'])
            ->name('dashboard.products.destroy');
    });

    // Products show - manager có thể xem
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/products/{id}', [\App\Http\Controllers\Web\ProductController::class, 'show'])
            ->name('dashboard.products.show');
    });

    // Coupons Management - cần quyền manager trở lên để xem
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/coupons', [\App\Http\Controllers\Web\CouponController::class, 'index'])
            ->name('dashboard.coupons.index');
    });

    // Coupons create/edit/delete - chỉ admin (ĐẶT TRƯỚC {id} để tránh conflict)
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/coupons/create', [\App\Http\Controllers\Web\CouponController::class, 'create'])
            ->name('dashboard.coupons.create');
        Route::post('/dashboard/coupons', [\App\Http\Controllers\Web\CouponController::class, 'store'])
            ->name('dashboard.coupons.store');
        Route::get('/dashboard/coupons/{id}/edit', [\App\Http\Controllers\Web\CouponController::class, 'edit'])
            ->name('dashboard.coupons.edit');
        Route::put('/dashboard/coupons/{id}', [\App\Http\Controllers\Web\CouponController::class, 'update'])
            ->name('dashboard.coupons.update');
        Route::delete('/dashboard/coupons/{id}', [\App\Http\Controllers\Web\CouponController::class, 'destroy'])
            ->name('dashboard.coupons.destroy');
        Route::patch('/dashboard/coupons/{id}/toggle-status', [\App\Http\Controllers\Web\CouponController::class, 'toggleStatus'])
            ->name('dashboard.coupons.toggle-status');
    });

    // Coupons show - manager có thể xem (ĐẶT SAU các route cụ thể)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/coupons/{id}', [\App\Http\Controllers\Web\CouponController::class, 'show'])
            ->name('dashboard.coupons.show');
    });

    // Orders Management - cần quyền manager trở lên
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/orders', [\App\Http\Controllers\Web\OrderController::class, 'index'])
            ->name('dashboard.orders.index');
        Route::get('/dashboard/orders/{id}', [\App\Http\Controllers\Web\OrderController::class, 'show'])
            ->name('dashboard.orders.show');
        Route::get('/dashboard/orders/{id}/edit', [\App\Http\Controllers\Web\OrderController::class, 'edit'])
            ->name('dashboard.orders.edit');
        Route::put('/dashboard/orders/{id}', [\App\Http\Controllers\Web\OrderController::class, 'update'])
            ->name('dashboard.orders.update');
    });

    // Orders delete - chỉ admin
    Route::delete('/dashboard/orders/{id}', [\App\Http\Controllers\Web\OrderController::class, 'destroy'])
        ->name('dashboard.orders.destroy')->middleware('role:admin');

    // Inventory Management - cần quyền manager trở lên
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/inventory', [\App\Http\Controllers\Web\InventoryController::class, 'index'])
            ->name('dashboard.inventory.index');
        Route::get('/dashboard/inventory/{id}', [\App\Http\Controllers\Web\InventoryController::class, 'show'])
            ->name('dashboard.inventory.show');
        Route::get('/dashboard/inventory/{id}/edit', [\App\Http\Controllers\Web\InventoryController::class, 'edit'])
            ->name('dashboard.inventory.edit');
        Route::put('/dashboard/inventory/{id}', [\App\Http\Controllers\Web\InventoryController::class, 'update'])
            ->name('dashboard.inventory.update');
        Route::post('/dashboard/inventory/{id}/adjust', [\App\Http\Controllers\Web\InventoryController::class, 'adjustStock'])
            ->name('dashboard.inventory.adjust');
    });

    // User Management Routes - chỉ admin
    Route::middleware('role:admin')->prefix('dashboard')->name('dashboard.')->group(function () {
        // Users Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserManagementController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

        // Role Assignment
        Route::post('/users/{user}/assign-role', [UserManagementController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('/users/{user}/remove-role/{role}', [UserManagementController::class, 'removeRole'])->name('users.remove-role');

        // Roles Management
        Route::get('/roles', [UserManagementController::class, 'roles'])->name('roles.index');
        Route::post('/roles', [UserManagementController::class, 'createRole'])->name('roles.create');
        Route::delete('/roles/{role}', [UserManagementController::class, 'deleteRole'])->name('roles.delete');
    });

    // Permissions - manager và admin có thể xem
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/permissions', [UserManagementController::class, 'permissions'])
            ->name('dashboard.permissions');
    });

    // Reports - Báo cáo thống kê (manager và admin có thể xem)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/reports', [\App\Http\Controllers\Web\ReportController::class, 'index'])
            ->name('dashboard.reports.index');
        Route::get('/dashboard/reports/revenue', [\App\Http\Controllers\Web\ReportController::class, 'revenue'])
            ->name('dashboard.reports.revenue');
        Route::get('/dashboard/reports/products', [\App\Http\Controllers\Web\ReportController::class, 'products'])
            ->name('dashboard.reports.products');
        Route::get('/dashboard/reports/customers', [\App\Http\Controllers\Web\ReportController::class, 'customers'])
            ->name('dashboard.reports.customers');
        Route::get('/dashboard/reports/export', [\App\Http\Controllers\Web\ReportController::class, 'export'])
            ->name('dashboard.reports.export');
    });
});

// Rating routes - Đánh giá sản phẩm (cần đăng nhập)
Route::middleware(['auth'])->group(function () {
    Route::post('/product/{productId}/rating', [CustomerProductController::class, 'addRating'])->name('product.rating.add');
});
