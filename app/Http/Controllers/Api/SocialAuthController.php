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
 * Luong hoatdong chinh:
 * Sử dụng Socialite để tương tác với các nhà cung cấp mạng xã hội
 * Sử dụng AuthService để quản lý người dùng và tạo token API
 * Đầu tiên khởi tạo các dịch vụ cần thiết trong constructor
 * ham redirect kiem tra provider hop le, tao url chuyen huong va tra ve cho client
 * ham callback kiem tra provider hop le, lay thong tin user tu provider, tim hoac tao user trong database, tao token API va tra ve cho client
 * ham loginWithToken kiem tra provider hop le, lay thong tin user tu provider bang access token, tim hoac tao user trong database, tao token API va tra ve cho client
 */
class SocialAuthController extends Controller
{
    protected $socialAuthService; // dich vu xu ly xac thuc social

    protected $authService; // dich vu xu ly xac thuc chung

    public function __construct(SocialAuthService $socialAuthService, AuthService $authService) // khoi tao dich vu
    {
        $this->socialAuthService = $socialAuthService; // gan dich vu xu ly xac thuc social
        $this->authService = $authService; // gan dich vu xu ly xac thuc chung
    }

    /**
     * Redirect to provider (for web-based flow)
     */
    public function redirect($provider) // chuyen huong den provider
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            // dieu kien neu provider khong hop le
            return ErrorResource::badRequest('Provider không hợp lệ'); // badRequest tra ve loi 400
        }

        try { // thu thap thong tin tu provider
            $redirectUrl = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
            // Socialite::driver($provider) --- lay driver tu Socialite theo provider
            // ->stateless() --- khong su dung session
            // ->redirect() --- chuyen huong den provider
            // ->getTargetUrl() --- lay url chuyen huong
            // neu driver hop le thi chuyen huong den provider va lay url chuyen huong

            return response()->json([
                'status' => true, // trang thai thanh cong
                'message' => 'Redirect URL generated successfully', // thong diep thanh cong
                'redirect_url' => $redirectUrl, // url chuyen huong den provider
            ], 200);
        } catch (Exception $e) { // neu co loi trong qua trinh chuyen huong
            return ErrorResource::serverError('Không thể tạo redirect URL', $e->getMessage()); // tra ve loi 500
        }
    }

    /**
     * Handle provider callback (for web-based flow)
     */
    public function callback($provider) // xu ly callback tu provider
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            // dieu kien neu provider khong hop le
            return ErrorResource::badRequest('Provider không hợp lệ'); // badRequest tra ve loi 400
        }

        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            // Lay thong tin user tu provider
            // Socialite::driver($provider) --- lay driver tu Socialite theo provider
            // ->stateless() --- khong su dung session
            // ->user() --- lay thong tin user tu provider
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);
            // Tim hoac tao user trong database

            // Load roles
            $user->load('roles');

            // Tạo API token
            $token = $this->authService->createApiToken($user);

            return (new UserResource($user))->additional([ // tra ve user resource kem token
                'status' => true, // trang thai thanh cong
                'message' => 'Social authentication successful', // thong diep thanh cong
                'token' => $token, // token API
            ]);
        } catch (Exception $e) {
            return ErrorResource::serverError('Social authentication failed', $e->getMessage()); // tra ve loi 500
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
            'provider' => 'required|string|in:google,facebook,github', // kiem tra provider hop le
            'access_token' => 'required|string', // kiem tra access token
        ]);

        $provider = $request->provider; // $provider la provider tu request
        $accessToken = $request->access_token; // $accessToken la access token tu request

        if (! $this->socialAuthService->isValidProvider($provider)) {
            // neu provider khong hop le, thuc hien bang cach goi den ham isValidProvider trong SocialAuthService
            return ErrorResource::badRequest('Provider không hợp lệ'); // tra ve loi 400 neu provider khong hop le
        }

        try {
            // Lấy thông tin user từ provider bằng access token
            $socialUser = Socialite::driver($provider)->stateless()->userFromToken($accessToken);
            // $socialUser la thong tin user lay ve tu provider
            // stateless() --- khong su dung session
            // userFromToken($accessToken) --- lay thong tin user tu provider bang access token

            // Tìm hoặc tạo user trong database
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);
            // Tim hoac tao user trong database bang cach goi den ham findOrCreateUser trong SocialAuthService

            // Load roles
            $user->load('roles'); // tai roles cho user

            // Tạo API token
            $token = $this->authService->createApiToken($user); // tao token API bang cach goi den ham createApiToken trong AuthService

            return (new UserResource($user))->additional([ // tra ve user resource kem token
                'status' => true,
                'message' => 'Social authentication successful',
                'token' => $token,
            ]);
        } catch (Exception $e) { // neu co loi trong qua trinh xac thuc
            return ErrorResource::unauthorized('Social authentication failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
