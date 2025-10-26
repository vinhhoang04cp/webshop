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
            $user = $this->authService->registerForApi($request->validated());
            $token = $this->authService->createApiToken($user);

            return response()->json([
                'status' => true,
                'message' => 'Registration successful',
                'user' => $user,
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
        $userProfile = $this->authService->getUserProfile($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'user' => $userProfile,
            ],
        ], 200);
    }
}
