<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CategoryController;
use App\Http\Controllers\Web\CouponController;
use App\Http\Controllers\Web\CustomerCartController;
use App\Http\Controllers\Web\CustomerProductController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InventoryController;
use App\Http\Controllers\Web\ChatViewController;
use App\Http\Controllers\Web\OrderController;
use App\Http\Controllers\Web\PageController;
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\PaymentController;
use App\Http\Controllers\Web\ProductController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\SocialAuthController;
use App\Http\Controllers\Web\UserManagementController;
use Illuminate\Support\Facades\Route;

// Home page - Trang chủ cho khách hàng
Route::get('/', [HomeController::class, 'index'])->name('home');

// Static Pages - Các trang tĩnh
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Social Authentication Routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

// Customer facing routes - Các route dành cho khách hàng
Route::get('/products', [CustomerProductController::class, 'index'])->name('products.index');
Route::get('/products/search', [CustomerProductController::class, 'search'])->name('products.search');
Route::get('/products/promotions', [CustomerProductController::class, 'promotions'])->name('products.promotions');
Route::get('/product/{id}', [CustomerProductController::class, 'show'])->name('product.show');
Route::get('/category/{id}', [CustomerProductController::class, 'category'])->name('category.show');

// Cart routes - Giỏ hàng
Route::get('/cart', [CustomerCartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{productId}', [CustomerCartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{cartItemId}', [CustomerCartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{cartItemId}', [CustomerCartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [CustomerCartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/checkout', [CustomerCartController::class, 'checkout'])->name('cart.checkout');

// Payment routes - Thanh toán
Route::get('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create.get');
Route::post('/payment/create', [PaymentController::class, 'createPayment'])->name('payment.create');
Route::get('/payment/vnpay-return', [PaymentController::class, 'vnpayReturn'])->name('payment.vnpay.return');
Route::post('/payment/vnpay-ipn', [PaymentController::class, 'vnpayIPN'])->name('payment.vnpay.ipn');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');

// Protected Dashboard Routes
Route::middleware(['auth'])->group(function () {
    // Profile Management - Quản lý tài khoản cá nhân
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

    // Dashboard chính - cần quyền dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard')
        ->middleware('role:dashboard');

    // Categories create/edit/delete - chỉ admin (PHẢI ĐẶT TRƯỚC {id})
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/categories/create', [CategoryController::class, 'create'])
            ->name('dashboard.categories.create');
        Route::post('/dashboard/categories', [CategoryController::class, 'store'])
            ->name('dashboard.categories.store');
        Route::get('/dashboard/categories/{id}/edit', [CategoryController::class, 'edit'])
            ->name('dashboard.categories.edit');
        Route::put('/dashboard/categories/{id}', [CategoryController::class, 'update'])
            ->name('dashboard.categories.update');
        Route::delete('/dashboard/categories/{id}', [CategoryController::class, 'destroy'])
            ->name('dashboard.categories.destroy');
    });

    // Categories CRUD - cần quyền manager trở lên (ĐẶT SAU create)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/categories', [CategoryController::class, 'index'])
            ->name('dashboard.categories.index');
        Route::get('/dashboard/categories/{id}', [CategoryController::class, 'show'])
            ->name('dashboard.categories.show');
    });

    // Products CRUD - cần quyền manager trở lên
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/products', [ProductController::class, 'index'])
            ->name('dashboard.products.index');
    });

    // Products create/edit/delete - chỉ admin
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/products/create', [ProductController::class, 'create'])
            ->name('dashboard.products.create');
        Route::post('/dashboard/products', [ProductController::class, 'store'])
            ->name('dashboard.products.store');
        Route::get('/dashboard/products/{id}/edit', [ProductController::class, 'edit'])
            ->name('dashboard.products.edit');
        Route::put('/dashboard/products/{id}', [ProductController::class, 'update'])
            ->name('dashboard.products.update');
        Route::delete('/dashboard/products/{id}', [ProductController::class, 'destroy'])
            ->name('dashboard.products.destroy');
    });

    // Products show - manager có thể xem
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/products/{id}', [ProductController::class, 'show'])
            ->name('dashboard.products.show');
    });

    // Coupons Management - cần quyền manager trở lên để xem
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/coupons', [CouponController::class, 'index'])
            ->name('dashboard.coupons.index');
    });

    // Coupons create/edit/delete - chỉ admin (ĐẶT TRƯỚC {id} để tránh conflict)
    Route::middleware('role:admin')->group(function () {
        Route::get('/dashboard/coupons/create', [CouponController::class, 'create'])
            ->name('dashboard.coupons.create');
        Route::post('/dashboard/coupons', [CouponController::class, 'store'])
            ->name('dashboard.coupons.store');
        Route::get('/dashboard/coupons/{id}/edit', [CouponController::class, 'edit'])
            ->name('dashboard.coupons.edit');
        Route::put('/dashboard/coupons/{id}', [CouponController::class, 'update'])
            ->name('dashboard.coupons.update');
        Route::delete('/dashboard/coupons/{id}', [CouponController::class, 'destroy'])
            ->name('dashboard.coupons.destroy');
        Route::patch('/dashboard/coupons/{id}/toggle-status', [CouponController::class, 'toggleStatus'])
            ->name('dashboard.coupons.toggle-status');
    });

    // Coupons show - manager có thể xem (ĐẶT SAU các route cụ thể)
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/coupons/{id}', [CouponController::class, 'show'])
            ->name('dashboard.coupons.show');
    });

    // Orders Management - cần quyền manager trở lên
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/orders', [OrderController::class, 'index'])
            ->name('dashboard.orders.index');
        Route::get('/dashboard/orders/{id}', [OrderController::class, 'show'])
            ->name('dashboard.orders.show');
        Route::get('/dashboard/orders/{id}/edit', [OrderController::class, 'edit'])
            ->name('dashboard.orders.edit');
        Route::put('/dashboard/orders/{id}', [OrderController::class, 'update'])
            ->name('dashboard.orders.update');
    });

    // Orders delete - chỉ admin
    Route::delete('/dashboard/orders/{id}', [OrderController::class, 'destroy'])
        ->name('dashboard.orders.destroy')->middleware('role:admin');

    // Inventory Management - cần quyền manager trở lên
    Route::middleware('role:manager')->group(function () {
        Route::get('/dashboard/inventory', [InventoryController::class, 'index'])
            ->name('dashboard.inventory.index');
        Route::get('/dashboard/inventory/{id}', [InventoryController::class, 'show'])
            ->name('dashboard.inventory.show');
        Route::get('/dashboard/inventory/{id}/edit', [InventoryController::class, 'edit'])
            ->name('dashboard.inventory.edit');
        Route::put('/dashboard/inventory/{id}', [InventoryController::class, 'update'])
            ->name('dashboard.inventory.update');
        Route::post('/dashboard/inventory/{id}/adjust', [InventoryController::class, 'adjustStock'])
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
        Route::get('/dashboard/reports', [ReportController::class, 'index'])
            ->name('dashboard.reports.index');
        Route::get('/dashboard/reports/revenue', [ReportController::class, 'revenue'])
            ->name('dashboard.reports.revenue');
        Route::get('/dashboard/reports/products', [ReportController::class, 'products'])
            ->name('dashboard.reports.products');
        Route::get('/dashboard/reports/customers', [ReportController::class, 'customers'])
            ->name('dashboard.reports.customers');
        Route::get('/dashboard/reports/export', [ReportController::class, 'export'])
            ->name('dashboard.reports.export');
    });

    // Chat UI - Trang chat (Customer/Admin/Manager)
    // - Customer: /chat -> phòng của chính mình
    // - Admin/Manager: /chat/{userId} hoặc /chat?user_id={id} để mở phòng của customer cụ thể
    Route::get('/chat/{userId?}', [ChatViewController::class, 'show'])
        ->where('userId', '[0-9]+')
        ->name('chat.show');
});

// Rating routes - Đánh giá sản phẩm (cần đăng nhập)
Route::middleware(['auth'])->group(function () {
    Route::post('/product/{productId}/rating', [CustomerProductController::class, 'storeRating'])->name('product.rating.add');
});
