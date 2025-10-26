<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    public function redirect($provider)
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            return redirect()->route('login')->withErrors([
                'error' => 'Provider không hợp lệ.',
            ]);
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'error' => 'Không thể kết nối đến '.ucfirst($provider).'. Vui lòng thử lại sau.',
            ]);
        }
    }

    public function callback($provider)
    {
        if (! $this->socialAuthService->isValidProvider($provider)) {
            return redirect()->route('login')->withErrors([
                'error' => 'Provider không hợp lệ.',
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

            Auth::login($user);

            $redirectRoute = $this->socialAuthService->getRedirectRoute($user);

            return redirect()->route($redirectRoute)
                ->with('success', 'Đăng nhập thành công qua '.ucfirst($provider).'!');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'error' => 'Đăng nhập thất bại. Vui lòng thử lại. Lỗi: '.$e->getMessage(),
            ]);
        }
    }
}
