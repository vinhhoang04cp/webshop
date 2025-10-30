<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * LoginAttemptMiddleware - Giới hạn số lần đăng nhập thất bại
 *
 * Middleware này:
 * - Giới hạn 5 lần đăng nhập thất bại trong 5 phút
 * - Block IP sau khi vượt quá giới hạn
 * - Tự động clear sau khi đăng nhập thành công
 */
class LoginAttemptMiddleware
{
    /**
     * Xử lý request đến
     *
     * Giới hạn số lần đăng nhập thất bại để chống brute force attack
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tạo key duy nhất cho mỗi email + IP để track số lần thử
        $key = $this->throttleKey($request);

        // Kiểm tra xem đã vượt quá giới hạn (5 lần) chưa
        if (RateLimiter::tooManyAttempts($key, 5)) {
            // Lấy số giây còn lại phải chờ
            $seconds = RateLimiter::availableIn($key);

            // Trả về lỗi 429 (Too Many Requests)
            return response()->json([
                'status' => false,
                'message' => "Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau {$seconds} giây.",
                'retry_after' => $seconds,
            ], 429);
        }

        // Cho phép request tiếp tục nếu chưa vượt quá giới hạn
        return $next($request);
    }

    /**
     * Tạo throttle key cho request
     *
     * Kết hợp email và IP để tạo key duy nhất
     * Ngăn chặn cùng một email bị brute force từ nhiều IP
     */
    protected function throttleKey(Request $request): string
    {
        // Format: login_attempts:email@example.com|192.168.1.1
        return 'login_attempts:'.strtolower($request->input('email')).'|'.$request->ip();
    }
}
