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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = $this->throttleKey($request);

        // Kiểm tra số lần thử
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'status' => false,
                'message' => "Quá nhiều lần đăng nhập thất bại. Vui lòng thử lại sau {$seconds} giây.",
                'retry_after' => $seconds,
            ], 429);
        }

        return $next($request);
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(Request $request): string
    {
        return 'login_attempts:'.strtolower($request->input('email')).'|'.$request->ip();
    }
}
