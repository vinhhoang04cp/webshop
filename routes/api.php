<?php

use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']); // Route dang nhap
Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']); // Route dang ky

// Password Reset Routes
Route::post('/forgot-password', [\App\Http\Controllers\Api\PasswordResetController::class, 'forgotPassword']);
Route::post('/reset-password', [\App\Http\Controllers\Api\PasswordResetController::class, 'resetPassword']);
Route::post('/validate-reset-token', [\App\Http\Controllers\Api\PasswordResetController::class, 'validateToken']);

// Social Authentication Routes
Route::get('/auth/{provider}/redirect', [\App\Http\Controllers\Api\SocialAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [\App\Http\Controllers\Api\SocialAuthController::class, 'callback']);
Route::post('/auth/social/token', [\App\Http\Controllers\Api\SocialAuthController::class, 'loginWithToken']); // For mobile/SPA

// Public product routes - Khách có thể xem sản phẩm mà không cần đăng nhập
Route::prefix('products')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\ProductController::class, 'index']);
    Route::get('/{id}', [\App\Http\Controllers\Api\ProductController::class, 'show']);

    // Public rating routes - Khách có thể xem ratings
    Route::get('/{id}/ratings', [\App\Http\Controllers\Api\ProductController::class, 'getRatings']);
});

// Public category routes - Khách có thể xem danh mục
Route::prefix('categories')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
});

// Các route cần authentication

Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () { // boc cac route can authentication vao day
    // 'throttle:60,1' gioi han 60 request/phut , gioi han nay co the thay doi theo yeu cau thuc te
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Get user profile with additional info (alternative route)
    Route::get('/profile', [\App\Http\Controllers\Api\AuthController::class, 'profile']);

    // Check authentication status
    Route::get('/check-auth', [\App\Http\Controllers\Api\AuthController::class, 'checkAuth']);

    // Dashboard data (Admin/Manager only)
    Route::get('/dashboard', [\App\Http\Controllers\Api\AuthController::class, 'dashboard']);

    // Logout route
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

    // Profile Management Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ProfileController::class, 'show']);
        Route::put('/', [\App\Http\Controllers\Api\ProfileController::class, 'update']);
        Route::put('/password', [\App\Http\Controllers\Api\ProfileController::class, 'changePassword']);
        Route::delete('/avatar', [\App\Http\Controllers\Api\ProfileController::class, 'deleteAvatar']);
    });

    // Product Rating Routes (cần đăng nhập)
    Route::prefix('products/{id}')->group(function () {
        Route::post('/ratings', [\App\Http\Controllers\Api\ProductController::class, 'addRating']);
        Route::put('/ratings/{ratingId}', [\App\Http\Controllers\Api\ProductController::class, 'updateRating']);
        Route::delete('/ratings/{ratingId}', [\App\Http\Controllers\Api\ProductController::class, 'deleteRating']);
    });

    // Category management (Admin only)
    Route::prefix('categories')->middleware('admin')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // Product management (Admin only)
    Route::prefix('products')->middleware('admin')->group(function () {
        Route::post('/', [\App\Http\Controllers\Api\ProductController::class, 'store']);
        Route::put('/{id}', [\App\Http\Controllers\Api\ProductController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\ProductController::class, 'destroy']);
    });

    // Order management (User có thể xem order của mình, Admin xem tất cả)
    Route::prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\OrderController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\OrderController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\OrderController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\OrderController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\OrderController::class, 'destroy']);
    });

    // Order items management (Internal use)
    Route::prefix('order-items')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\OrderItemController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\OrderItemController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\OrderItemController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\OrderItemController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\OrderItemController::class, 'destroy']);
    });

    // Product details management (Admin only)
    Route::prefix('product-details')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\ProductDetailController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\ProductDetailController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'update']);
        Route::patch('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\ProductDetailController::class, 'destroy']);
    });

    // Cart management (User chỉ quản lý cart của mình)
    Route::prefix('cart')->group(function () {
        // Get current user's cart
        Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'current']);

        // Add product to cart (simple version)
        Route::post('/add/{productId}', [\App\Http\Controllers\Api\CartController::class, 'addProduct']);

        // Update cart item quantity
        Route::put('/items/{cartItemId}', [\App\Http\Controllers\Api\CartController::class, 'updateItem']);

        // Remove item from cart
        Route::delete('/items/{cartItemId}', [\App\Http\Controllers\Api\CartController::class, 'removeItem']);

        // Clear entire cart
        Route::delete('/clear', [\App\Http\Controllers\Api\CartController::class, 'clear']);

        // Validate coupon before checkout
        Route::post('/validate-coupon', [\App\Http\Controllers\Api\CartController::class, 'validateCoupon']);

        // Checkout
        Route::post('/checkout', [\App\Http\Controllers\Api\CartController::class, 'checkout']);
    });

    // Advanced cart management (Admin có thể xem tất cả)
    Route::prefix('carts')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CartController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\CartController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\CartController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\CartController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
    });

    // Cart items management (User chỉ quản lý cart items của mình)
    Route::prefix('cart-items')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\CartItemController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\CartItemController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\CartItemController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\CartItemController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\CartItemController::class, 'destroy']);
    });

    // Inventory management (Admin only)
    Route::prefix('inventories')->middleware('admin')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\InventoryController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\InventoryController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Api\InventoryController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Api\InventoryController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Api\InventoryController::class, 'destroy']);

        // Additional inventory routes
        Route::post('/upsert', [\App\Http\Controllers\Api\InventoryController::class, 'upsert']);
        Route::put('/{id}/update-stock', [\App\Http\Controllers\Api\InventoryController::class, 'updateStock']);
        Route::get('/low-stock/list', [\App\Http\Controllers\Api\InventoryController::class, 'lowStock']);
    });
});

// Coupon routes - Public access to view coupons
Route::prefix('coupons')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\CouponController::class, 'index']);
});
