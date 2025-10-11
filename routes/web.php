<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserManagementController;
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

    // Permissions và Statistics - manager và admin có thể xem  
    Route::get('/dashboard/permissions', [UserManagementController::class, 'permissions'])
        ->name('dashboard.permissions'); // Tạm thời bỏ middleware để debug

    // Alternative route không cần middleware
    Route::get('/dashboard/permissions-debug', [UserManagementController::class, 'permissions'])
        ->name('dashboard.permissions.debug');

    // Debug route - tạm thời để kiểm tra user info
    Route::get('/debug/user-info', function() {
        $user = Auth::user();
        return response()->json([
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'roles' => $user->roles->map(function($role) {
                return [
                    'role_id' => $role->role_id,
                    'role_name' => $role->role_name,
                    'role_display_name' => $role->role_display_name
                ];
            }),
            'is_admin' => $user->isAdmin(),
            'is_manager' => $user->isManager(),
            'can_access_dashboard' => $user->canAccessDashboard(),
            'permissions' => $user->getAllPermissions()
        ]);
    });

    // Debug route - tạm thời để gán role admin cho user hiện tại
    Route::get('/debug/make-admin', function() {
        $user = Auth::user();
        
        // Tạo role admin nếu chưa có
        $adminRole = \App\Models\Role::firstOrCreate(
            ['role_name' => 'admin'],
            [
                'role_display_name' => 'Administrator',
                'role_created_at' => now(),
                'role_updated_at' => now()
            ]
        );
        
        // Gán role admin cho user hiện tại
        \App\Models\UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $adminRole->role_id
        ], [
            'assigned_at' => now()
        ]);
        
        return "User {$user->name} đã được gán role Admin!";
    });

    // Debug route - tạm thời để gán role manager cho user hiện tại  
    Route::get('/debug/make-manager', function() {
        $user = Auth::user();
        
        // Tạo role manager nếu chưa có
        $managerRole = \App\Models\Role::firstOrCreate(
            ['role_name' => 'manager'],
            [
                'role_display_name' => 'Manager', 
                'role_created_at' => now(),
                'role_updated_at' => now()
            ]
        );
        
        // Gán role manager cho user hiện tại
        \App\Models\UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role_id' => $managerRole->role_id
        ], [
            'assigned_at' => now()
        ]);
        
        return "User {$user->name} đã được gán role Manager!";
    });
});
