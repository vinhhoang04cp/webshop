<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\SocialAuthService;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * SocialAuthController - xử lý đăng nhập qua mạng xã hội cho API
 */
class SocialAuthController extends Controller
{
    protected $socialAuthService;

    protected $authService;

    public function __construct(SocialAuthService $socialAuthService, AuthService $authService)
    {
        $this->socialAuthService = $socialAuthService;
        $this->authService = $authService;
    }

    /**
     * Redirect to provider (for web-based flow)
     * Thường không dùng cho mobile apps
     */
    public function redirect($provider)
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            return response()->json([
                'status' => false,
                'message' => 'Provider không hợp lệ',
            ], 400);
        }

        try {
            $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();

            return response()->json([
                'status' => true,
                'message' => 'Redirect URL generated successfully',
                'redirect_url' => $redirectUrl,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Không thể tạo redirect URL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle provider callback (for web-based flow)
     */
    public function callback($provider)
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            return response()->json([
                'status' => false,
                'message' => 'Provider không hợp lệ',
            ], 400);
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

            // Tạo API token
            $token = $this->authService->createApiToken($user);

            // Lấy thông tin user với roles
            $userWithRoles = $this->authService->getUserWithRoles($user);

            return response()->json([
                'status' => true,
                'message' => 'Social authentication successful',
                'user' => $userWithRoles,
                'token' => $token,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Social authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Login with social provider access token (for mobile/SPA)
     * Client lấy access token từ provider và gửi lên server
     *
     * Request body:
     * {
     *   "provider": "google|facebook|github",
     *   "access_token": "provider_access_token"
     * }
     */
    public function loginWithToken(Request $request)
    {
        $request->validate([
            'provider' => 'required|string|in:google,facebook,github',
            'access_token' => 'required|string',
        ]);

        $provider = $request->provider;
        $accessToken = $request->access_token;

        if (! $this->socialAuthService->isValidProvider($provider)) {
            return response()->json([
                'status' => false,
                'message' => 'Provider không hợp lệ',
            ], 400);
        }

        try {
            // Lấy thông tin user từ provider bằng access token
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);

            // Tìm hoặc tạo user trong database
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

            // Tạo API token
            $token = $this->authService->createApiToken($user);

            // Lấy thông tin user với roles
            $userWithRoles = $this->authService->getUserWithRoles($user);

            return response()->json([
                'status' => true,
                'message' => 'Social authentication successful',
                'user' => $userWithRoles,
                'token' => $token,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Social authentication failed',
                'error' => $e->getMessage(),
            ], 401);
        }
    }
}
