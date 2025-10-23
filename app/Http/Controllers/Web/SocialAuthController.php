<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Chuyển hướng đến provider để đăng nhập
     *
     * @param  string  $provider  (google, facebook, github, etc.)
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect($provider)
    {
        // Validate provider
        if (! in_array($provider, ['google', 'facebook', 'github'])) {
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

    /**
     * Xử lý callback từ provider sau khi đăng nhập
     *
     * @param  string  $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback($provider)
    {
        // Validate provider
        if (! in_array($provider, ['google', 'facebook', 'github'])) {
            return redirect()->route('login')->withErrors([
                'error' => 'Provider không hợp lệ.',
            ]);
        }

        try {
            // Lấy thông tin user từ provider
            $socialUser = Socialite::driver($provider)->user();

            // Tìm hoặc tạo user trong database
            $user = $this->findOrCreateUser($socialUser, $provider);

            // Đăng nhập user
            Auth::login($user);

            // Chuyển hướng dựa trên vai trò của user
            if ($user->hasRole('admin') || $user->hasRole('manager')) {
                return redirect()->route('dashboard')->with('success', 'Đăng nhập thành công qua '.ucfirst($provider).'!');
            } elseif ($user->hasRole('customer')) {
                return redirect()->route('products.index')->with('success', 'Đăng nhập thành công qua '.ucfirst($provider).'!');
            } else {
                return redirect()->route('home')->with('success', 'Đăng nhập thành công qua '.ucfirst($provider).'!');
            }

        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'error' => 'Đăng nhập thất bại. Vui lòng thử lại. Lỗi: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Tìm hoặc tạo user từ thông tin social
     *
     * @param  mixed  $socialUser
     * @param  string  $provider
     * @return User
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // Tìm user theo provider và provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        // Nếu tìm thấy user, cập nhật avatar và trả về
        if ($user) {
            $user->update([
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $user;
        }

        // Kiểm tra xem email đã tồn tại chưa (từ đăng ký thông thường)
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Cập nhật thông tin social cho user đã tồn tại
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $existingUser;
        }

        // Tạo user mới
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'password' => null, // Không cần password cho social login
            ]);

            // Tự động gán role customer cho user mới
            $customerRole = Role::where('role_name', 'customer')->first();
            if ($customerRole) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $customerRole->role_id,
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
