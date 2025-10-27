<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\UserResource;
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
        $user = $request->user()->load('roles');

        return (new UserResource($user))->additional([
            'status' => true,
            'message' => 'Profile retrieved successfully',
        ]);
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

            return (new UserResource($updatedUser))->additional([
                'status' => true,
                'message' => 'Profile updated successfully',
            ]);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Failed to update profile', $e->getMessage());
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

            return SuccessResource::message('Password changed successfully');
        } catch (\Exception $e) {
            return ErrorResource::badRequest('Failed to change password', [
                'error' => $e->getMessage(),
            ]);
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

            return (new UserResource($updatedUser))->additional([
                'status' => true,
                'message' => 'Avatar deleted successfully',
            ]);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Failed to delete avatar', $e->getMessage());
        }
    }
}
