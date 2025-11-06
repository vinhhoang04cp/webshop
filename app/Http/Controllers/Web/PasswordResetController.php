<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetLinkRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Hiển thị form yêu cầu reset password
     *
     * @return \Illuminate\View\View
     */
    public function showForgotForm() // Hiển thị form yêu cầu reset password
    {
        return view('auth.forgot-password');
    }

    /**
     * Xử lý yêu cầu gửi email reset password
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendResetLink(PasswordResetLinkRequest $request) // Xử lý yêu cầu gửi email reset password
    {
        try {
            // thu gọi service để gửi link reset password
            $this->passwordResetService->sendResetLink($request->email); // thuc hien gui email

            return back()->with('success', 'Link đặt lại mật khẩu đã được gửi đến email của bạn!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Không thể gửi email. Vui lòng thử lại sau.']);
        }
    }

    /**
     * Hiển thị form reset password
     *
     * @param  string  $token
     * @return \Illuminate\View\View
     */
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Xử lý đặt lại mật khẩu
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resetPassword(PasswordResetRequest $request)
    {
        try {
            $this->passwordResetService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );

            return redirect()->route('login')->with('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
