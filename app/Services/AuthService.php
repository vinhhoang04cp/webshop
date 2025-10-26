<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Xác thực thông tin đăng nhập
     */
    public function authenticate($email, $password)
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Đăng ký user mới (dùng chung cho Web và API)
     */
    public function register($data, $autoAssignRole = true)
    {
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            // Gán role customer mặc định nếu được yêu cầu
            if ($autoAssignRole) {
                $customerRole = Role::where('role_name', 'customer')->first();
                if ($customerRole) {
                    UserRole::create([
                        'user_id' => $user->id,
                        'role_id' => $customerRole->role_id,
                        'assigned_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lấy route redirect dựa trên role của user
     */
    public function getRedirectRoute($user)
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return 'dashboard';
        } elseif ($user->hasRole('customer')) {
            return 'products.index';
        } else {
            return 'home';
        }
    }

    /**
     * Lấy dữ liệu dashboard
     */
    public function getDashboardData()
    {
        try {
            $productsCount = Product::count();
            $ordersCount = Order::count();
            $usersCount = User::count();
            $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

            $recentOrders = Order::with('user')
                ->orderBy('order_date', 'desc')
                ->limit(5)
                ->get()
                ->toArray();

            return [
                'products_count' => $productsCount,
                'orders_count' => $ordersCount,
                'users_count' => $usersCount,
                'total_revenue' => $totalRevenue,
                'recent_orders' => $recentOrders,
            ];
        } catch (\Exception $e) {
            return [
                'products_count' => 0,
                'orders_count' => 0,
                'users_count' => 0,
                'total_revenue' => 0,
                'recent_orders' => [],
                'error' => 'Không thể tải dữ liệu dashboard: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Kiểm tra quyền truy cập dashboard
     */
    public function canAccessDashboard($user)
    {
        return $user->hasRole('admin') || $user->hasRole('manager');
    }

    /**
     * Tạo token cho API
     */
    public function createApiToken($user, $tokenName = 'api-token')
    {
        // Xóa tất cả token cũ
        $user->tokens()->delete();

        // Tạo token mới
        return $user->createToken($tokenName)->plainTextToken;
    }

    /**
     * Kiểm tra user có được xác thực không
     */
    public function isAuthenticated($user)
    {
        return $user !== null;
    }

    /**
     * Lấy thông tin user kèm roles
     */
    public function getUserWithRoles($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'email_verified_at' => $user->email_verified_at,
            'roles' => $user->roles->pluck('role_name')->toArray(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Revoke token hiện tại
     */
    public function revokeCurrentToken($user)
    {
        if ($user->currentAccessToken()) {
            $user->currentAccessToken()->delete();

            return true;
        }

        return false;
    }

    /**
     * Lấy profile user
     */
    public function getUserProfile($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'email_verified_at' => $user->email_verified_at,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }
}
