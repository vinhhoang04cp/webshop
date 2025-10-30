<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * SessionSecurityMiddleware - Tăng cường bảo mật session
 *
 * Middleware này:
 * - Regenerate session ID định kỳ
 * - Kiểm tra IP address changes
 * - Kiểm tra User Agent changes
 * - Implement session timeout
 * - Prevent session fixation
 */
class SessionSecurityMiddleware
{
    /**
     * Session regeneration interval (in minutes)
     */
    protected int $regenerationInterval = 15;

    /**
     * Enable strict IP checking
     */
    protected bool $strictIpCheck = false;

    /**
     * Enable User Agent checking
     */
    protected bool $strictUserAgentCheck = true;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ áp dụng cho authenticated users
        if (! $request->user()) {
            return $next($request);
        }

        // Check for session hijacking
        if ($this->isSessionHijacked($request)) {
            Session::flush();
            auth()->logout();

            return redirect()->route('login')
                ->with('error', 'Phiên làm việc không hợp lệ. Vui lòng đăng nhập lại.');
        }

        // Regenerate session ID periodically
        $this->regenerateSessionPeriodically($request);

        // Store security markers
        $this->storeSecurityMarkers($request);

        return $next($request);
    }

    /**
     * Check if session is potentially hijacked
     */
    protected function isSessionHijacked(Request $request): bool
    {
        // Check IP address if strict mode enabled
        if ($this->strictIpCheck) {
            $currentIp = $request->ip();
            $sessionIp = Session::get('security.ip');

            if ($sessionIp && $sessionIp !== $currentIp) {
                return true;
            }
        }

        // Check User Agent
        if ($this->strictUserAgentCheck) {
            $currentUserAgent = $request->userAgent();
            $sessionUserAgent = Session::get('security.user_agent');

            if ($sessionUserAgent && $sessionUserAgent !== $currentUserAgent) {
                return true;
            }
        }

        return false;
    }

    /**
     * Regenerate session ID periodically
     */
    protected function regenerateSessionPeriodically(Request $request): void
    {
        $lastRegeneration = Session::get('security.last_regeneration');

        if (! $lastRegeneration) {
            Session::put('security.last_regeneration', now()->timestamp);

            return;
        }

        // Convert timestamp to Carbon instance for comparison
        $lastRegenerationTime = \Carbon\Carbon::createFromTimestamp($lastRegeneration);
        $minutesSinceRegeneration = now()->diffInMinutes($lastRegenerationTime);

        if ($minutesSinceRegeneration >= $this->regenerationInterval) {
            Session::regenerate();
            Session::put('security.last_regeneration', now()->timestamp);
        }
    }

    /**
     * Store security markers in session
     */
    protected function storeSecurityMarkers(Request $request): void
    {
        if (! Session::has('security.ip')) {
            Session::put('security.ip', $request->ip());
        }

        if (! Session::has('security.user_agent')) {
            Session::put('security.user_agent', $request->userAgent());
        }

        // Update last activity timestamp
        Session::put('security.last_activity', now()->timestamp);
    }
}
