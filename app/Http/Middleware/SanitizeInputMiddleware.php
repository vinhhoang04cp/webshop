<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * SanitizeInputMiddleware - Làm sạch input để ngăn chặn XSS attacks
 *
 * Middleware này sẽ:
 * - Loại bỏ HTML tags khỏi input
 * - Escape các ký tự đặc biệt HTML
 * - Bảo vệ khỏi XSS (Cross-Site Scripting) attacks
 */
class SanitizeInputMiddleware
{
    /**
     * Xử lý request đến
     *
     * Làm sạch tất cả input để ngăn chặn XSS attack
     * bằng cách loại bỏ HTML tags và escape ký tự đặc biệt
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lấy tất cả input từ request
        $input = $request->all();


        // Danh sách các field không nên sanitize
        // Các field này cần giữ nguyên giá trị gốc
        $excludedFields = ['password', 'password_confirmation', 'current_password', 'new_password', 'token'];

        // Duyệt đệ quy qua tất cả input (bao gồm cả mảng lồng nhau)
        array_walk_recursive($input, function (&$value, $key) use ($excludedFields) {
            // Chỉ xử lý string và bỏ qua các field trong whitelist
            if (is_string($value) && ! in_array($key, $excludedFields)) {
                // Loại bỏ tất cả HTML tags (như <script>, <iframe>, etc)
                $value = strip_tags($value);

                // Escape các ký tự đặc biệt HTML (< > & " ')
                // Ngăn chặn XSS bằng cách chuyển < thành &lt;
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                // Loại bỏ khoảng trắng thừa ở đầu/cuối
                $value = trim($value);
            }
        });

        // Cập nhật input đã sanitize vào request
        $request->merge($input);

        // Cho phép request tiếp tục với input đã được làm sạch
        return $next($request);
    }
}
