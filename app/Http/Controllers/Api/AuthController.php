<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * AuthController - xử lý xác thực user qua API
 */
class AuthController extends Controller
{
    protected $authService;

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

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $this->authService->createApiToken($user);

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    /**
     * Đăng ký user mới
     */
    public function register(RegisterRequest $request)
    {
        try {
            // Sử dụng phương thức register chung, gán role customer mặc định
            $user = $this->authService->register($request->validated(), true);
            $token = $this->authService->createApiToken($user);

            // Lấy thông tin user kèm roles
            $userWithRoles = $this->authService->getUserWithRoles($user);

            return response()->json([
                'status' => true,
                'message' => 'Registration successful',
                'user' => $userWithRoles,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đăng xuất và xóa token
     */
    public function logout(Request $request)
    {
        $this->authService->revokeCurrentToken($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ], 200);
    }

    /**
     * Lấy thông tin profile user hiện tại
     */
    public function profile(Request $request)
    {
        $userWithRoles = $this->authService->getUserWithRoles($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => $userWithRoles,
            ],
        ], 200);
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
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized. Only admin or manager can access dashboard.',
            ], 403);
        }

        $dashboardData = $this->authService->getDashboardData();

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data retrieved successfully',
            'data' => $dashboardData,
        ], 200);
    }

    /**
     * Kiểm tra trạng thái xác thực
     */
    public function checkAuth(Request $request)
    {
        $user = $request->user();

        if (! $this->authService->isAuthenticated($user)) {
            return response()->json([
                'status' => false,
                'message' => 'Not authenticated',
                'authenticated' => false,
            ], 401);
        }

        $userWithRoles = $this->authService->getUserWithRoles($user);

        return response()->json([
            'status' => true,
            'message' => 'Authenticated',
            'authenticated' => true,
            'user' => $userWithRoles,
        ], 200);
    }
}
