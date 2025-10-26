<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Gửi link reset password qua email
     *
     * @param  string  $email  Email người dùng
     * @param  string|null  $resetUrl  URL tùy chỉnh (cho API/SPA), nếu null sẽ dùng route Web mặc định
     * @return array Trả về token và link (hữu ích cho API testing)
     */
    public function sendResetLink($email, $resetUrl = null)
    {
        // Kiểm tra email có tồn tại không
        $user = User::where('email', $email)->first();
        if (! $user) {
            throw new \Exception('Email không tồn tại trong hệ thống.');
        }

        $token = Str::random(64);

        // Lưu token vào database
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        // Tạo link reset password
        if ($resetUrl) {
            // API/SPA: dùng URL tùy chỉnh
            $resetLink = $resetUrl.'?token='.$token.'&email='.urlencode($email);
        } else {
            // Web: dùng route Laravel
            $resetLink = route('password.reset', ['token' => $token, 'email' => $email]);
        }

        // Gửi email
        Mail::send('emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Yêu cầu đặt lại mật khẩu');
        });

        return [
            'success' => true,
            'token' => $token, // Chỉ dùng cho testing, không nên trả về trong production
            'reset_link' => $resetLink,
        ];
    }

    /**
     * Xác thực và reset password
     */
    public function resetPassword($email, $token, $newPassword)
    {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $passwordReset) {
            throw new \Exception('Token không hợp lệ.');
        }

        // Kiểm tra token có khớp không
        if (! Hash::check($token, $passwordReset->token)) {
            throw new \Exception('Token không hợp lệ.');
        }

        // Kiểm tra token có hết hạn chưa (24 giờ)
        $created = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($created->addHours(24)->isPast()) {
            throw new \Exception('Token đã hết hạn. Vui lòng yêu cầu lại.');
        }

        // Cập nhật mật khẩu mới
        $user = User::where('email', $email)->first();
        $user->password = Hash::make($newPassword);
        $user->save();

        // Xóa token đã sử dụng
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }

    /**
     * Xác thực token
     */
    public function validateToken($email, $token)
    {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (! $passwordReset) {
            return false;
        }

        // Kiểm tra token
        if (! Hash::check($token, $passwordReset->token)) {
            return false;
        }

        // Kiểm tra hết hạn
        $created = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($created->addHours(24)->isPast()) {
            return false;
        }

        return true;
    }
}
