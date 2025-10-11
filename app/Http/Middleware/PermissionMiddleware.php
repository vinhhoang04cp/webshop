<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = Auth::user();

        // Admin có tất cả quyền
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Kiểm tra từng quyền
        foreach ($permissions as $permission) {
            if (!$user->hasPermission($permission)) {
                abort(403, "Bạn không có quyền {$permission} để thực hiện hành động này.");
            }
        }

        return $next($request);
    }
}
