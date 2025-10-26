<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        $user = Auth::user();

        return view('profile.index', compact('user'));
    }

    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = Auth::user();
        $avatarFile = $request->hasFile('avatar') ? $request->file('avatar') : null;

        $this->profileService->updateProfile($user, $request->validated(), $avatarFile);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        try {
            $this->profileService->changePassword(
                $user,
                $request->current_password,
                $request->new_password
            );

            return back()->with('success', 'Đổi mật khẩu thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
    }
}
