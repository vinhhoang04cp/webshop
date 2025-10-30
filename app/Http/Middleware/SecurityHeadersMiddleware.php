<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SecurityHeadersMiddleware - Thêm security headers vào response
 *
 * Middleware này thêm các HTTP security headers để bảo vệ ứng dụng khỏi:
 * - XSS (Cross-Site Scripting)
 * - Clickjacking
 * - MIME type sniffing
 * - Information disclosure
 */
class SecurityHeadersMiddleware
{
    /**
     * Xử lý request đến
     *
     * Thêm các security headers vào response để tăng cường bảo mật
     * Các headers này được browser sử dụng để bảo vệ user
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cho phép request đi tiếp và nhận response
        $response = $next($request);

        // Ngăn chặn MIME type sniffing
        // Browser sẽ không cố gắng "đoán" content type, chỉ dùng type từ server
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Ngăn chặn clickjacking
        // Trang web không thể được nhúng trong iframe
        $response->headers->set('X-Frame-Options', 'DENY');

        // Bật XSS protection trên browser
        // Browser sẽ block trang nếu phát hiện XSS
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        // Chỉ gửi origin (không gửi full URL) khi chuyển từ HTTPS sang HTTPS
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Content Security Policy (CSP)
        // Định nghĩa nguồn tài nguyên nào được phép load
        $csp = implode('; ', [
            "default-src 'self'", // Mặc định chỉ cho phép từ cùng origin
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com", // Nguồn script
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://fonts.googleapis.com", // Nguồn CSS
            "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com", // Nguồn font
            "img-src 'self' data: https: blob:", // Nguồn hình ảnh
            "connect-src 'self'", // API endpoints cho AJAX/fetch
            "frame-ancestors 'none'", // Không cho phép nhúng trong iframe
        ]);
        $response->headers->set('Content-Security-Policy', $csp);

        // Permissions Policy
        // Tắt các tính năng browser không cần thiết để tăng bảo mật
        $permissionsPolicy = implode(', ', [
            'geolocation=()', // Tắt truy cập vị trí
            'microphone=()', // Tắt truy cập microphone
            'camera=()', // Tắt truy cập camera
            'payment=()', // Tắt Payment API
            'usb=()', // Tắt USB API
            'magnetometer=()', // Tắt magnetometer
        ]);
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // Strict Transport Security (HSTS)
        // Chỉ bật khi đang dùng HTTPS
        // Yêu cầu browser luôn dùng HTTPS cho domain này
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains'); // 1 năm
        }

        return $response;
    }
}
