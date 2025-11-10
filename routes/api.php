<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CartItemController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderItemController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductDetailController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SocialAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatController; // <--- CÓ "Api"

// Authentication Routes với rate limiting nghiêm ngặt
Route::middleware(['throttle:auth', 'login.attempts'])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Password Reset Routes với rate limiting
Route::middleware(['throttle:sensitive'])->group(function () {
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    Route::post('/validate-reset-token', [PasswordResetController::class, 'validateToken']);
});

// Social Authentication Routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect']);
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback']);
Route::post('/auth/social/token', [SocialAuthController::class, 'loginWithToken']); // For mobile/SPA

// Public product routes - Khách có thể xem sản phẩm mà không cần đăng nhập
Route::prefix('products')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/stats', [ProductController::class, 'stats']);
    Route::get('/{id}', [ProductController::class, 'show']);

    // Public rating routes - Khách có thể xem ratings
    Route::get('/{id}/ratings', [ProductController::class, 'getRatings']);
});

// Public category routes - Khách có thể xem danh mục
Route::prefix('categories')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [CategoryController::class, 'index']);
    Route::get('/{id}', [CategoryController::class, 'show']);
});

// Payment callback routes - VNPay IPN (không cần auth)
Route::prefix('payment')->middleware('throttle:60,1')->group(function () {
    Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn']);
    Route::post('/vnpay-ipn', [PaymentController::class, 'vnpayIPN']);
});

// Các route cần authentication với token expiration check và rate limiting cao hơn

Route::middleware(['auth:sanctum', 'token.expiration', 'throttle:api-authenticated'])->group(function () { // boc cac route can authentication vao day
    Route::get('/chat/user/{userId}/history', [ChatController::class, 'getHistory']);
    Route::post('/chat/user/{userId}/message', [ChatController::class, 'sendMessage']);

    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Get user profile with additional info (alternative route)
    Route::get('/profile', [AuthController::class, 'profile']);

    // Check authentication status
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);

    // Dashboard data (Admin/Manager only)
    Route::get('/dashboard', [AuthController::class, 'dashboard']);

    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);

    // Profile Management Routes với rate limiting cho sensitive operations
    Route::prefix('profile')->middleware('throttle:sensitive')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::put('/password', [ProfileController::class, 'changePassword']);
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar']);
    });

    // Product Rating Routes (cần đăng nhập)
    Route::prefix('products/{id}')->group(function () {
        Route::post('/ratings', [ProductController::class, 'addRating']);
        Route::put('/ratings/{ratingId}', [ProductController::class, 'updateRating']);
        Route::delete('/ratings/{ratingId}', [ProductController::class, 'deleteRating']);
    });

    // Category management (Admin only)
    Route::prefix('categories')->middleware('admin')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // Product management (Admin only)
    Route::prefix('products')->middleware('admin')->group(function () {
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    // Order management (User có thể xem order của mình, Admin xem tất cả)
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/statuses', [OrderController::class, 'getStatuses']);
        Route::get('/stats', [OrderController::class, 'stats']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::put('/{id}', [OrderController::class, 'update']);
        Route::delete('/{id}', [OrderController::class, 'destroy']);
        Route::post('/{id}/change-status', [OrderController::class, 'changeStatus'])->middleware('admin');
    });

    // Payment routes - Thanh toán (cần đăng nhập)
    Route::prefix('payment')->group(function () {
        Route::post('/create', [PaymentController::class, 'createPayment']);
        Route::get('/status/{orderId}', [PaymentController::class, 'getPaymentStatus']);
        Route::get('/success/{orderId}', [PaymentController::class, 'getPaymentSuccess']);
        Route::get('/failed/{orderId}', [PaymentController::class, 'getPaymentFailed']);
    });

    // Order items management (Internal use)
    Route::prefix('order-items')->group(function () {
        Route::get('/', [OrderItemController::class, 'index']);
        Route::post('/', [OrderItemController::class, 'store']);
        Route::get('/{id}', [OrderItemController::class, 'show']);
        Route::put('/{id}', [OrderItemController::class, 'update']);
        Route::delete('/{id}', [OrderItemController::class, 'destroy']);
    });

    // Product details management (Admin only)
    Route::prefix('product-details')->middleware('admin')->group(function () {
        Route::get('/', [ProductDetailController::class, 'index']);
        Route::post('/', [ProductDetailController::class, 'store']);
        Route::get('/{id}', [ProductDetailController::class, 'show']);
        Route::put('/{id}', [ProductDetailController::class, 'update']);
        Route::patch('/{id}', [ProductDetailController::class, 'update']);
        Route::delete('/{id}', [ProductDetailController::class, 'destroy']);
    });

    // Cart management (User chỉ quản lý cart của mình)
    Route::prefix('cart')->group(function () {
        // Get current user's cart
        Route::get('/', [CartController::class, 'current']);

        // Add product to cart (simple version)
        Route::post('/add/{productId}', [CartController::class, 'addProduct']);

        // Update cart item quantity
        Route::put('/items/{cartItemId}', [CartController::class, 'updateItem']);

        // Remove item from cart
        Route::delete('/items/{cartItemId}', [CartController::class, 'removeItem']);

        // Clear entire cart
        Route::delete('/clear', [CartController::class, 'clear']);

        // Validate coupon before checkout
        Route::post('/validate-coupon', [CartController::class, 'validateCoupon']);

        // Checkout
        Route::post('/checkout', [CartController::class, 'checkout']);
    });

    // Advanced cart management (Admin có thể xem tất cả)
    Route::prefix('carts')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::get('/{id}', [CartController::class, 'show']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
    });

    // Cart items management (User chỉ quản lý cart items của mình)
    Route::prefix('cart-items')->group(function () {
        Route::get('/', [CartItemController::class, 'index']);
        Route::post('/', [CartItemController::class, 'store']);
        Route::get('/{id}', [CartItemController::class, 'show']);
        Route::put('/{id}', [CartItemController::class, 'update']);
        Route::delete('/{id}', [CartItemController::class, 'destroy']);
    });

    // Inventory management (Admin only)
    Route::prefix('inventories')->middleware('admin')->group(function () {
        Route::get('/', [InventoryController::class, 'index']);
        Route::post('/', [InventoryController::class, 'store']);
        Route::get('/{id}', [InventoryController::class, 'show']);
        Route::put('/{id}', [InventoryController::class, 'update']);
        Route::delete('/{id}', [InventoryController::class, 'destroy']);

        // Additional inventory routes
        Route::post('/upsert', [InventoryController::class, 'upsert']);
        Route::put('/{id}/update-stock', [InventoryController::class, 'updateStock']);
        Route::get('/low-stock/list', [InventoryController::class, 'lowStock']);
        Route::get('/out-of-stock/list', [InventoryController::class, 'outOfStock']);
        Route::get('/stats', [InventoryController::class, 'stats']);
    });

    // Coupon management (Admin only)
    Route::prefix('coupons')->middleware('admin')->group(function () {
        Route::post('/', [CouponController::class, 'store']);
        Route::put('/{id}', [CouponController::class, 'update']);
        Route::delete('/{id}', [CouponController::class, 'destroy']);
        Route::post('/{id}/toggle-status', [CouponController::class, 'toggleStatus']);
    });
});

// Coupon routes - Public access to view and validate coupons
Route::prefix('coupons')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [CouponController::class, 'index']);
    Route::get('/{id}', [CouponController::class, 'show']);
    Route::post('/validate', [CouponController::class, 'validate']);
});
