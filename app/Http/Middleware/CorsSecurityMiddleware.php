<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CorsSecurityMiddleware - Tăng cường CORS security
 *
 * Middleware này:
 * - Validate origin requests
 * - Set proper CORS headers
 * - Prevent unauthorized cross-origin requests
 */
class CorsSecurityMiddleware
{
    /**
     * Allowed origins
     */
    protected array $allowedOrigins = [
        'http://localhost:3000',
        'http://localhost:8000',
        'https://yourdomain.com',
    ];

    /**
     * Allowed methods
     */
    protected array $allowedMethods = [
        'GET',
        'POST',
        'PUT',
        'PATCH',
        'DELETE',
        'OPTIONS',
    ];

    /**
     * Allowed headers
     */
    protected array $allowedHeaders = [
        'Accept',
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-CSRF-TOKEN',
    ];

    /**
     * Exposed headers
     */
    protected array $exposedHeaders = [
        'X-Total-Count',
        'X-Page-Count',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Handle preflight OPTIONS request
        if ($request->isMethod('OPTIONS')) {
            return $this->preflightResponse($request);
        }

        $response = $next($request);

        // Add CORS headers
        $this->addCorsHeaders($request, $response);

        return $response;
    }

    /**
     * Handle preflight OPTIONS request
     */
    protected function preflightResponse(Request $request): Response
    {
        $response = response('', 204);
        $this->addCorsHeaders($request, $response);

        return $response;
    }

    /**
     * Add CORS headers to response
     */
    protected function addCorsHeaders(Request $request, Response $response): void
    {
        $origin = $request->header('Origin');

        // Validate origin
        if ($this->isAllowedOrigin($origin)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // Set allowed methods
        $response->headers->set(
            'Access-Control-Allow-Methods',
            implode(', ', $this->allowedMethods)
        );

        // Set allowed headers
        $response->headers->set(
            'Access-Control-Allow-Headers',
            implode(', ', $this->allowedHeaders)
        );

        // Set exposed headers
        if (! empty($this->exposedHeaders)) {
            $response->headers->set(
                'Access-Control-Expose-Headers',
                implode(', ', $this->exposedHeaders)
            );
        }

        // Set max age for preflight cache
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
    }

    /**
     * Check if origin is allowed
     */
    protected function isAllowedOrigin(?string $origin): bool
    {
        if (! $origin) {
            return false;
        }

        // Allow from .env configuration
        $envAllowedOrigins = config('cors.allowed_origins', []);
        if (in_array($origin, $envAllowedOrigins)) {
            return true;
        }

        // Check against hardcoded list
        return in_array($origin, $this->allowedOrigins);
    }
}
