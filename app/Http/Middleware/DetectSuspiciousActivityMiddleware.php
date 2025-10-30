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
     * SQL Injection patterns
     */
    protected array $sqlPatterns = [
        '/(\b(union|select|insert|update|delete|drop|create|alter|exec|execute)\b)/i',
        '/(\bor\b\s*\d+\s*=\s*\d+)/i',
        '/(--|\#|\/\*|\*\/)/i',
    ];

    /**
     * XSS patterns
     */
    protected array $xssPatterns = [
        '/<script[^>]*>.*?<\/script>/is',
        '/javascript:/i',
        '/on\w+\s*=\s*["\'][^"\']*["\']/i',
        '/<iframe/i',
        '/<object/i',
        '/<embed/i',
    ];

    /**
     * Path traversal patterns
     */
    protected array $pathTraversalPatterns = [
        '/\.\.\//',
        '/\.\.\\\\/',
        '/%2e%2e%2f/i',
        '/%2e%2e\\\\/i',
    ];

    /**
     * Command injection patterns
     */
    protected array $commandPatterns = [
        '/;.*\b(ls|cat|wget|curl|chmod|rm|mv)\b/i',
        '/\|\s*\b(ls|cat|wget|curl|chmod|rm|mv)\b/i',
        '/&&\s*\b(ls|cat|wget|curl|chmod|rm|mv)\b/i',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = json_encode($request->all());
        $suspicious = false;
        $attackType = '';

        // Kiểm tra SQL Injection
        foreach ($this->sqlPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                $suspicious = true;
                $attackType = 'SQL Injection';
                break;
            }
        }

        // Kiểm tra XSS
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
        if (! $suspicious) {
            foreach ($this->commandPatterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    $suspicious = true;
                    $attackType = 'Command Injection';
                    break;
                }
            }
        }

        if ($suspicious) {
            // Log hoạt động đáng ngờ
            Log::channel('security')->warning('Suspicious activity detected', [
                'attack_type' => $attackType,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'input' => $request->except(['password', 'password_confirmation', 'current_password']),
                'timestamp' => now(),
            ]);

            // Có thể block request hoặc chỉ log
            // Uncomment để block:
            // return response()->json([
            //     'status' => false,
            //     'message' => 'Suspicious activity detected.',
            // ], 403);
        }

        return $next($request);
    }
}
