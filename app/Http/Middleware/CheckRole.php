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
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = Auth::user();
        
        // Debug logging
        \Log::info('CheckRole Middleware Debug', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_roles' => $roles,
            'user_roles' => $user->roles->pluck('role_name')->toArray(),
        ]);

        // Kiểm tra từng role được yêu cầu
        foreach ($roles as $role) {
            $hasPermission = false;
            
            switch ($role) {
                case 'admin':
                    $hasPermission = $user->isAdmin();
                    break;
                
                case 'manager':
                    $hasPermission = $user->isManager() || $user->isAdmin();
                    break;
                
                case 'dashboard':
                    $hasPermission = $user->canAccessDashboard();
                    break;
                    
                default:
                    // Kiểm tra role cụ thể
                    $hasPermission = $user->hasRole($role);
                    break;
            }
            
            \Log::info('Role Check Result', [
                'role' => $role,
                'hasPermission' => $hasPermission
            ]);
            
            // Nếu có ít nhất một role thỏa mãn thì cho phép
            if ($hasPermission) {
                return $next($request);
            }
        }

        // Không có quyền nào phù hợp
        \Log::warning('Access Denied', [
            'user_id' => $user->id,
            'required_roles' => $roles,
            'user_roles' => $user->roles->pluck('role_name')->toArray(),
        ]);
        
        abort(403, 'Bạn không có quyền truy cập trang này.');
    }
}
