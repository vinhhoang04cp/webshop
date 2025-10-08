<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (! Auth::check()) {
            return redirect()->route('login')->withErrors([
                'email' => 'Vui lòng đăng nhập để tiếp tục.',
            ]);
        }

        $user = Auth::user();

        // Kiểm tra user có ít nhất một trong các role được yêu cầu
        $hasRole = false;
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                $hasRole = true;
                break;
            }
        }

        if (! $hasRole) {
            // User không có quyền, đăng xuất và redirect về login
            Auth::logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Bạn không có quyền truy cập vào khu vực này.',
            ]);
        }

        return $next($request);
    }
}
