<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    /**
     * Xác thực thông tin đăng nhập với rate limiting
     */
    public function authenticate($email, $password, $request = null) // cac tham so truyen vao la email, password va request (mac dinh la null)
    {
        $user = User::where('email', $email)->first(); // tim user theo email

        if (! $user || ! Hash::check($password, $user->password)) { // user khong ton tai hoac password khong dung
            // Increment failed login attempts
            if ($request) { // neu co request thi tang so lan dang nhap that bai
                $this->incrementLoginAttempts($request, $email); // tang so lan dang nhap that bai
            }

            return null;
        }

        // Clear login attempts on successful login
        if ($request) { // neu co request thi xoa so lan dang nhap that bai
            $this->clearLoginAttempts($request, $email); // xoa so lan dang nhap that bai
        }

        return $user; // tra ve user neu dang nhap thanh cong
    }

    /**
     * Increment login attempts
     */
    protected function incrementLoginAttempts($request, $email)
    {
        $key = $this->throttleKey($request, $email);
        RateLimiter::hit($key, 300); // Block for 5 minutes
    }

    /**
     * Clear login attempts
     */
    public function clearLoginAttempts($request, $email)
    {
        $key = $this->throttleKey($request, $email);
        RateLimiter::clear($key);
    }

    /**
     * Get throttle key
     */
    protected function throttleKey($request, $email)
    {
        return 'login_attempts:'.strtolower($email).'|'.$request->ip();
    }

    /**
     * Giới hạn số lượng tokens của user
     */
    protected function limitUserTokens($user, $maxTokens = 5)
    {
        $tokenCount = $user->tokens()->count();

        if ($tokenCount >= $maxTokens) {
            // Xóa token cũ nhất
            $user->tokens()
                ->orderBy('created_at', 'asc')
                ->limit($tokenCount - $maxTokens + 1)
                ->delete();
        }
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
     * Tạo token cho API với giới hạn số lượng
     */
    public function createApiToken($user, $tokenName = 'api-token')
    {
        // Giới hạn tối đa 5 tokens cho mỗi user
        $this->limitUserTokens($user, 5);

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
