<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ForceHttpsMiddleware - Bắt buộc sử dụng HTTPS trong production
 *
 * Middleware này:
 * - Redirect HTTP sang HTTPS trong production
 * - Set Strict-Transport-Security header
 * - Secure all URLs
 */
class ForceHttpsMiddleware
{
    /**
     * Xử lý request đến
     *
     * Đảm bảo tất cả request sử dụng HTTPS trong môi trường production
     * để bảo vệ dữ liệu khi truyền tải
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ bắt buộc HTTPS trong production
        // Môi trường local và testing được phép dùng HTTP
        if (! app()->environment('local', 'testing')) {
            // Nếu request không dùng HTTPS thì redirect sang HTTPS
            if (! $request->secure()) {
                // 301 = Permanent Redirect
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        // Cho phép request đi tiếp
        $response = $next($request);

        // Thêm HSTS header khi đang dùng HTTPS
        // Header này yêu cầu browser luôn dùng HTTPS cho domain này
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload' // 1 năm, bao gồm subdomain, cho phép preload
            );
        }

        return $response;
    }
}
