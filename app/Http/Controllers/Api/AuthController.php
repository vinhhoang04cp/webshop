<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\Request;

/**
 * AuthController - xử lý xác thực user qua API
 */
class AuthController extends Controller
{
    protected $authService;

    // khai báo AuthService trong constructor
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Đăng nhập và tạo token
     */
    public function login(LoginRequest $request)
    {
        $user = $this->authService->authenticate($request->email, $request->password);
        // goi den ham authenticate trong AuthService voi tham so email va password

        if (! $user) { // neu khong co user tra ve loi
            return ErrorResource::unauthorized('The provided credentials are incorrect.');
        }

        // Load roles
        $user->load('roles');

        $token = $this->authService->createApiToken($user);

        return UserResource::retrieved($user, 'Login successful', ['token' => $token]);
    }

    /**
     * Đăng ký user mới
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Sử dụng phương thức register chung, gán role customer mặc định
            $user = $this->authService->register($request->validated(), true);

            // Load roles
            $user->load('roles');

            $token = $this->authService->createApiToken($user);

            return UserResource::created($user, 'Registration successful', ['token' => $token]);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Registration failed: '.$e->getMessage());
        }
    }

    /**
     * Đăng xuất và xóa token
     */
    public function logout(Request $request)
    {
        $this->authService->revokeCurrentToken($request->user());

        return SuccessResource::message('Logout successful');
    }

    /**
     * Lấy thông tin profile user hiện tại
     */
    public function profile(Request $request)
    {
        $user = $request->user()->load('roles');

        return UserResource::retrieved($user, 'Profile retrieved successfully');
    }

    /**
     * Lấy dữ liệu dashboard (chỉ dành cho admin/manager)
     */
    public function dashboard(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        // Kiểm tra quyền truy cập dashboard
        if (! $this->authService->canAccessDashboard($user)) {
            return ErrorResource::forbidden('Unauthorized. Only admin or manager can access dashboard.');
        }

        $dashboardData = $this->authService->getDashboardData();

        return SuccessResource::withData($dashboardData, 'Dashboard data retrieved successfully');
    }

    /**
     * Kiểm tra trạng thái xác thực
     */
    public function checkAuth(Request $request)
    {
        $user = $request->user();

        if (! $this->authService->isAuthenticated($user)) {
            return ErrorResource::unauthorized('Not authenticated', ['authenticated' => false]);
        }

        $user->load('roles');

        return UserResource::retrieved($user, 'Authenticated', ['authenticated' => true]);
    }
}
