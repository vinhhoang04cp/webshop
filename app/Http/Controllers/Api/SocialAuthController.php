<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\SocialAuthService;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

/**
 * SocialAuthController - xử lý đăng nhập qua mạng xã hội cho API
 * Luồng hoạt động chính:
 * Sử dụng Socialite để tương tác với các nhà cung cấp mạng xã hội
 * Sử dụng AuthService để quản lý người dùng và tạo token API
 * Đầu tiên khởi tạo các dịch vụ cần thiết trong constructor
 * Hàm redirect kiểm tra provider hợp lệ, tạo url chuyển hướng và trả về cho client
 * Hàm callback kiểm tra provider hợp lệ, lấy thông tin user từ provider, tìm hoặc tạo user trong database, tạo token API và trả về cho client
 * Hàm loginWithToken kiểm tra provider hợp lệ, lấy thông tin user từ provider bằng access token, tìm hoặc tạo user trong database, tạo token API và trả về cho client
 */
class SocialAuthController extends Controller
{
    protected $socialAuthService; // dịch vụ xử lý xác thực social

    protected $authService; // dịch vụ xử lý xác thực chung

    public function __construct(SocialAuthService $socialAuthService, AuthService $authService) // khởi tạo dịch vụ
    {
        $this->socialAuthService = $socialAuthService; // gán dịch vụ xử lý xác thực social
        $this->authService = $authService; // gán dịch vụ xử lý xác thực chung
    }

    /**
     * Chuyển hướng đến provider (cho luồng web-based)
     */
    public function redirect($provider) // chuyển hướng đến provider
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            // điều kiện nếu provider không hợp lệ
            return ErrorResource::badRequest('Provider không hợp lệ'); // badRequest trả về lỗi 400
        }

        try { // thu thập thông tin từ provider
            $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
            // Socialite::driver($provider) --- lấy driver từ Socialite theo provider
            // ->stateless() --- không sử dụng session
            // ->redirect() --- chuyển hướng đến provider
            // ->getTargetUrl() --- lấy url chuyển hướng
            // nếu driver hợp lệ thì chuyển hướng đến provider và lấy url chuyển hướng

            return response()->json([
                'status' => true, // trạng thái thành công
                'message' => 'Redirect URL generated successfully', // thông điệp thành công
                'redirect_url' => $redirectUrl, // url chuyển hướng đến provider
            ], 200);
        } catch (Exception $e) { // nếu có lỗi trong quá trình chuyển hướng
            return ErrorResource::serverError('Không thể tạo redirect URL', $e->getMessage()); // trả về lỗi 500
        }
    }

    /**
     * Xử lý callback từ provider (cho luồng web-based)
     */
    public function callback($provider) // xử lý callback từ provider
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            // điều kiện nếu provider không hợp lệ
            return ErrorResource::badRequest('Provider không hợp lệ'); // badRequest trả về lỗi 400
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            // Lấy thông tin user từ provider
            // Socialite::driver($provider) --- lấy driver từ Socialite theo provider
            // ->stateless() --- không sử dụng session
            // ->user() --- lấy thông tin user từ provider
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);
            // Tìm hoặc tạo user trong database

            // Load roles
            $user->load('roles');

            // Tạo API token
            $token = $this->authService->createApiToken($user);

            return (new UserResource($user))->additional([ // trả về user resource kèm token
                'status' => true, // trạng thái thành công
                'message' => 'Social authentication successful', // thông điệp thành công
                'token' => $token, // token API
            ]);
        } catch (Exception $e) {
            return ErrorResource::serverError('Social authentication failed', $e->getMessage()); // trả về lỗi 500
        }
    }

    /**
     * Đăng nhập bằng access token từ social provider (cho mobile/SPA)
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
            'provider' => 'required|string|in:google,facebook,github', // kiểm tra provider hợp lệ
            'access_token' => 'required|string', // kiểm tra access token
        ]);

        $provider = $request->provider; // $provider là provider từ request
        $accessToken = $request->access_token; // $accessToken là access token từ request

        if (! $this->socialAuthService->isValidProvider($provider)) {
            // nếu provider không hợp lệ, thực hiện bằng cách gọi đến hàm isValidProvider trong SocialAuthService
            return ErrorResource::badRequest('Provider không hợp lệ'); // trả về lỗi 400 nếu provider không hợp lệ
        }

        try {
            // Lấy thông tin user từ provider bằng access token
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);
            // $socialUser là thông tin user lấy về từ provider
            // stateless() --- không sử dụng session
            // userFromToken($accessToken) --- lấy thông tin user từ provider bằng access token

            // Tìm hoặc tạo user trong database
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);
            // Tìm hoặc tạo user trong database bằng cách gọi đến hàm findOrCreateUser trong SocialAuthService

            // Load roles
            $user->load('roles'); // tải roles cho user

            // Tạo API token
            $token = $this->authService->createApiToken($user); // tạo token API bằng cách gọi đến hàm createApiToken trong AuthService

            return (new UserResource($user))->additional([ // trả về user resource kèm token
                'status' => true,
                'message' => 'Social authentication successful',
                'token' => $token,
            ]);
        } catch (Exception $e) { // nếu có lỗi trong quá trình xác thực
            return ErrorResource::unauthorized('Social authentication failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
