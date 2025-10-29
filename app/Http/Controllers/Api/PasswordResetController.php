<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetLinkRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Services\PasswordResetService;

/**
 * PasswordResetController - xử lý reset mật khẩu qua API
 */
class PasswordResetController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Gửi link reset password qua email
     * Request body:
     * {
     *   "email": "user@example.com",
     *   "reset_url": "https://yourfrontend.com/reset-password" (optional, for SPA/mobile)
     * }
     */
    public function forgotPassword(PasswordResetLinkRequest $request)
    {
        try {
            // Lấy reset_url từ request (nếu có) - dùng cho SPA/mobile apps
            $resetUrl = $request->input('reset_url', null);

            $result = $this->passwordResetService->sendResetLink($request->email, $resetUrl);

            return SuccessResource::message('Link đặt lại mật khẩu đã được gửi đến email của bạn. Vui lòng kiểm tra email.');
        } catch (\Exception $e) {
            return ErrorResource::badRequest('Không thể gửi email reset password', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset password với token
     */
    public function resetPassword(PasswordResetRequest $request)
    {
        try {
            $this->passwordResetService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );

            return SuccessResource::message('Mật khẩu đã được đặt lại thành công. Bạn có thể đăng nhập với mật khẩu mới.');
        } catch (\Exception $e) {
            return ErrorResource::badRequest('Không thể reset password', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Xác thực token reset password
     */
    public function validateToken(PasswordResetRequest $request)
    {
        try {
            $isValid = $this->passwordResetService->validateToken(
                $request->email,
                $request->token
            );

            if ($isValid) {
                return SuccessResource::withData(['valid' => true], 'Token hợp lệ');
            }

            return ErrorResource::badRequest('Token không hợp lệ hoặc đã hết hạn', [
                'valid' => false,
            ]);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Lỗi khi xác thực token', [
                'error' => $e->getMessage(),
                'valid' => false,
            ]);
        }
    }
}
