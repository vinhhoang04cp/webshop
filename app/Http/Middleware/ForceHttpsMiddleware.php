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
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ enforce HTTPS trong production
        if (! app()->environment('local', 'testing')) {
            // Redirect HTTP to HTTPS
            if (! $request->secure()) {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        $response = $next($request);

        // Set HSTS header nếu đang dùng HTTPS
        if ($request->secure()) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload'
            );
        }

        return $response;
    }
}
