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
     */
    public function sendResetLink($email)
    {
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
        $resetLink = route('password.reset', ['token' => $token, 'email' => $email]);

        // Gửi email
        Mail::send('emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Yêu cầu đặt lại mật khẩu');
        });

        return true;
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
