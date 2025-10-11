<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $role
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = Auth::user();

        // Kiểm tra quyền dựa trên role
        switch ($role) {
            case 'admin':
                if (!$user->isAdmin()) {
                    abort(403, 'Bạn không có quyền truy cập trang này. Chỉ Admin mới được phép.');
                }
                break;
            
            case 'manager':
                if (!$user->isManager() && !$user->isAdmin()) {
                    abort(403, 'Bạn không có quyền truy cập trang này. Chỉ Manager và Admin mới được phép.');
                }
                break;
            
            case 'dashboard':
                if (!$user->canAccessDashboard()) {
                    abort(403, 'Bạn không có quyền truy cập dashboard.');
                }
                break;
                
            default:
                // Kiểm tra role cụ thể
                if (!$user->hasRole($role)) {
                    abort(403, "Bạn không có quyền {$role} để truy cập trang này.");
                }
                break;
        }

        return $next($request);
    }
}
