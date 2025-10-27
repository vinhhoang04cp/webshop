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
    public function findOrCreateUser($socialUser, $provider) // $socialUser: instance của Laravel Socialite User, $provider: 'google', 'facebook', 'github'
    // instance la User tu Socialite, co che khac voi App\Models\User
    // &$socialUser co cac phuong thuc: getId(), getName(), getEmail(), getAvatar(), getNickname()
    {
        // Tìm user theo provider và provider_id
        $user = User::where('provider', $provider) // User la App\Models\User truy van csdl den bang users, co provider va provider_id
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            // Cập nhật avatar nếu user đã tồn tại
            $user->update([
                'avatar' => $socialUser->getAvatar(), // cap nhat lai avatar moi nhat tu social
            ]);

            return $user;
        }

        // Tìm user theo email
        $existingUser = User::where('email', $socialUser->getEmail())->first();
        // $existingUser la user da ton tai trong he thong voi email giong voi email tu social

        if ($existingUser) { // neu ton tai user voi email do
            // Link social provider vào user hiện có
            $existingUser->update([
                'provider' => $provider, // cap nhat provider va provider_id
                'provider_id' => $socialUser->getId(), // cap nhat provider_id
                'avatar' => $socialUser->getAvatar(), // cap nhat avatar moi nhat tu social
            ]);

            return $existingUser; // tra ve user da ton tai sau khi cap nhat
        }

        // Tạo user mới
        DB::beginTransaction(); // bat dau giao dich de tao user moi

        try {
            $user = User::create([ // tao user moi voi thong tin tu social
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User', // neu khong co ten thi lay nickname, neu khong co nickname thi dat la 'User'
                'email' => $socialUser->getEmail(), // email tu social
                'provider' => $provider, // provider
                'provider_id' => $socialUser->getId(), // provider_id
                'avatar' => $socialUser->getAvatar(), // avatar tu social
                'password' => null,
            ]);

            // Gán role customer mặc định
            $customerRole = Role::where('role_name', 'customer')->first(); // lay role customer tu bang roles
            if ($customerRole) { // neu co role customer
                UserRole::create([ // gan role customer cho user moi tao
                    'user_id' => $user->id, // user_id la id cua user moi tao
                    'role_id' => $customerRole->role_id, // role_id la id cua role customer
                    'assigned_at' => now(), // thoi gian gan role
                ]);
            }

            DB::commit(); // ket thuc giao dich

            return $user;
        } catch (Exception $e) {
            DB::rollBack(); // neu co loi thi hoan tac giao dich
            throw $e;
        }
    }

    /**
     * Kiểm tra provider hợp lệ
     */
    public function isValidProvider($provider) // kiem tra provider co hop le khong, voi tham so truyen vao la provider
    {
        return in_array($provider, ['google', 'facebook', 'github']); // tra ve true neu provider nam trong mang hop le
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
