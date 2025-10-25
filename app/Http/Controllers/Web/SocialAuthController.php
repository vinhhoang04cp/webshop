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
    public function redirect($provider)
    {
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

    public function callback($provider)
    {
        if (! in_array($provider, ['google', 'facebook', 'github'])) {
            return redirect()->route('login')->withErrors([
                'error' => 'Provider không hợp lệ.',
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->findOrCreateUser($socialUser, $provider);
            Auth::login($user);

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

    private function findOrCreateUser($socialUser, $provider)
    {
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            $user->update([
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $user;
        }

        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $existingUser;
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'password' => null,
            ]);

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
