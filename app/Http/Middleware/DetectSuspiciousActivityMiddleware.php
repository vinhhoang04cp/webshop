<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * DetectSuspiciousActivityMiddleware - Phát hiện hoạt động đáng ngờ
 *
 * Middleware này phát hiện:
 * - SQL Injection patterns
 * - XSS attempts
 * - Path traversal attempts
 * - Command injection
 */
class DetectSuspiciousActivityMiddleware
{
    /**
     * Các pattern để phát hiện SQL Injection
     * Kiểm tra các từ khóa SQL nguy hiểm và cú pháp tấn công phổ biến
     */
    protected array $sqlPatterns = [
        '/(\b(union|select|insert|update|delete|drop|create|alter|exec|execute)\b)/i', // Các lệnh SQL
        '/(\bor\b\s*\d+\s*=\s*\d+)/i', // Pattern OR 1=1
        '/(--|\#|\/\*|\*\/)/i', // SQL comments
    ];

    /**
     * Các pattern để phát hiện XSS (Cross-Site Scripting)
     * Kiểm tra các thẻ HTML và JavaScript nguy hiểm
     */
    protected array $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is', // Thẻ script
        '/javascript:/i', // JavaScript protocol
        '/on\w+\s*=\s*["\'][^"\']*["\']/i', // Event handlers (onclick, onload, etc)
        '/<iframe/i', // Thẻ iframe
        '/<object/i', // Thẻ object
        '/<embed/i', // Thẻ embed
    ];

    /**
     * Các pattern để phát hiện Path Traversal
     * Ngăn chặn việc truy cập file ngoài thư mục cho phép
     */
    protected array $pathTraversalPatterns = [
        '/\.\.\//', // ../
        '/\.\.\\\\/', // ..\
        '/%2e%2e%2f/i', // URL encoded ../
        '/%2e%2e\\\\/i', // URL encoded ..\
    ];

    /**
     * Các pattern để phát hiện Command Injection
     * Ngăn chặn thực thi lệnh hệ thống không mong muốn
     */
    protected array $commandPatterns = [
        '/;.*\b(ls|cat|wget|curl|chmod|rm|mv)\b/i', // Lệnh sau dấu ;
        '/\|\s*\b(ls|cat|wget|curl|chmod|rm|mv)\b/i', // Lệnh sau pipe |
        '/&&\s*\b(ls|cat|wget|curl|chmod|rm|mv)\b/i', // Lệnh sau &&
    ];

    /**
     * Xử lý request đến
     *
     * Kiểm tra tất cả input để phát hiện các loại tấn công phổ biến
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chuyển tất cả input thành chuỗi JSON để dễ kiểm tra
        $input = json_encode($request->all());
        $suspicious = false;
        $attackType = '';

        // Kiểm tra SQL Injection
        // Duyệt qua tất cả pattern SQL để tìm dấu hiệu tấn công
        foreach ($this->sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $suspicious = true;
                $attackType = 'SQL Injection';
                break;
            }
        }

        // Kiểm tra XSS
        // Chỉ kiểm tra nếu chưa phát hiện tấn công khác
        if (! $suspicious) {
            foreach ($this->xssPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    $suspicious = true;
                    $attackType = 'XSS Attack';
                    break;
                }
            }
        }

        // Kiểm tra Path Traversal
        // Tìm dấu hiệu cố gắng truy cập file ngoài thư mục
        if (! $suspicious) {
            foreach ($this->pathTraversalPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    $suspicious = true;
                    $attackType = 'Path Traversal';
                    break;
                }
            }
        }

        // Kiểm tra Command Injection
        // Phát hiện cố gắng thực thi lệnh hệ thống
        if (! $suspicious) {
            foreach ($this->commandPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    $suspicious = true;
                    $attackType = 'Command Injection';
                    break;
                }
            }
        }

        // Xử lý khi phát hiện hoạt động đáng ngờ
        if ($suspicious) {
            // Log chi tiết về cuộc tấn công để phân tích
            Log::channel('security')->warning('Suspicious activity detected', [
                'attack_type' => $attackType,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->except(['password', 'password_confirmation', 'current_password']),
                'timestamp' => now(),
            ]);

            // Có thể chặn request hoặc chỉ ghi log
            // Mở comment dòng dưới để chặn request:
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Suspicious activity detected.',
            // ], 403);
        }

        // Cho phép request tiếp tục nếu không phát hiện vấn đề
        return $next($request);
    }
}
