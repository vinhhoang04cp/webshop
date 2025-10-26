<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\DB;

class SocialAuthService
{
    /**
     * Tìm hoặc tạo user từ thông tin social
     */
    public function findOrCreateUser($socialUser, $provider)
    {
        // Tìm user theo provider và provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            // Cập nhật avatar nếu user đã tồn tại
            $user->update([
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $user;
        }

        // Tìm user theo email
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            // Link social provider vào user hiện có
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
                'password' => null,
            ]);

            // Gán role customer mặc định
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

    /**
     * Kiểm tra provider hợp lệ
     */
    public function isValidProvider($provider)
    {
        return in_array($provider, ['google', 'facebook', 'github']);
    }

    /**
     * Lấy redirect route dựa trên role của user
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
}
