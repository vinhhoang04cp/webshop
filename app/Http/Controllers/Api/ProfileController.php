<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ProfileService;
use Illuminate\Http\Request;

/**
 * ProfileController - quản lý profile user qua API
 */
class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Lấy thông tin profile
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'avatar' => $user->avatar,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 200);
    }

    /**
     * Cập nhật thông tin profile
     */
    public function update(ProfileUpdateRequest $request)
    {
        try {
            $user = $request->user();
            $avatarFile = $request->hasFile('avatar') ? $request->file('avatar') : null;

            $updatedUser = $this->profileService->updateProfile(
                $user,
                $request->validated(),
                $avatarFile
            );

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully',
                'data' => [
                    'id' => $updatedUser->id,
                    'name' => $updatedUser->name,
                    'email' => $updatedUser->email,
                    'phone' => $updatedUser->phone,
                    'address' => $updatedUser->address,
                    'avatar' => $updatedUser->avatar,
                    'updated_at' => $updatedUser->updated_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = $request->user();

            $this->profileService->changePassword(
                $user,
                $request->current_password,
                $request->new_password
            );

            return response()->json([
                'status' => true,
                'message' => 'Password changed successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Xóa avatar
     */
    public function deleteAvatar(Request $request)
    {
        try {
            $user = $request->user();
            $updatedUser = $this->profileService->deleteAvatar($user);

            return response()->json([
                'status' => true,
                'message' => 'Avatar deleted successfully',
                'data' => [
                    'avatar' => $updatedUser->avatar,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
