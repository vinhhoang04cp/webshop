<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Cập nhật thông tin profile
     */
    public function updateProfile($user, $data, $avatarFile = null)
    {
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->address = $data['address'];

        if ($avatarFile) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Lưu avatar mới
            $avatarPath = $avatarFile->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return $user;
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword($user, $currentPassword, $newPassword)
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Mật khẩu hiện tại không đúng.');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }

    /**
     * Xóa avatar
     */
    public function deleteAvatar($user)
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();
        }

        return $user;
    }
}
