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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();

        // Danh sách các fields nên bỏ qua việc sanitize (ví dụ: password, token)
        $excludedFields = ['password', 'password_confirmation', 'current_password', 'new_password', 'token'];

        array_walk_recursive($input, function (&$value, $key) use ($excludedFields) {
            // Chỉ sanitize string và không sanitize các field nhạy cảm
            if (is_string($value) && ! in_array($key, $excludedFields)) {
                // Loại bỏ HTML tags
                $value = strip_tags($value);

                // Escape HTML special characters
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

                // Trim whitespace
                $value = trim($value);
            }
        });

        // Merge sanitized input back to request
        $request->merge($input);

        return $next($request);
    }
}
