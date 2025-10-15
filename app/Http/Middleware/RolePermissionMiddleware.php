<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RolePermissionMiddleware - Middleware tổng hợp cho phân quyền
 *
 * Sử dụng:
 * - route()->middleware('role.permission:admin') - Kiểm tra role admin
 * - route()->middleware('role.permission:admin,manager') - Kiểm tra nhiều role (OR)
 * - route()->middleware('role.permission:permission:edit_product') - Kiểm tra permission
 */
class RolePermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$args  - Các tham số role hoặc permission
     */
    public function handle(Request $request, Closure $next, ...$args): Response // (Request $request, Closure $next, ...$args) trong do tham so truyen vao ham
    {
        // Kiểm tra user đã đăng nhập
        if (! Auth::check()) { // Auth::check() kiem tra user da dang nhap chua
            // Nếu yêu cầu JSON, trả về lỗi JSON
            if ($request->expectsJson()) { // $request->expectsJson() kiem tra xem yeu cau co phai la JSON khong
                return response()->json([ // neu phai thi tra ve loi dang json
                    'status' => false,
                    'message' => 'Vui lòng đăng nhập để tiếp tục.',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.'); // Neu khong phai thi chuyen huong ve trang login
        }

        /** @var \App\Models\User $user */
        $user = Auth::user(); // Lay user hien tai

        // Nếu không có tham số, chỉ cần đăng nhập
        if (empty($args)) { // Neu khong co tham so truyen vao
            return $next($request); // Cho phep di tiep
        }

        // Admin có tất cả quyền
        if ($user->isAdmin()) { // Neu user la admin
            return $next($request); // Cho phep di tiep
        }

        // Phân tích tham số
        $checkType = 'role'; // $checkType la bien de kiem tra loai (role hay permission)
        $items = $args; // $items la mang chua cac tham so truyen vao

        // Nếu tham số đầu tiên là 'permission:', chuyển sang kiểm tra permission
        if (isset($args[0]) && str_starts_with($args[0], 'permission:')) {
            $checkType = 'permission';
            $items[0] = substr($args[0], 11); // Bỏ prefix 'permission:'
        }

        // Kiểm tra role hoặc permission
        foreach ($items as $item) {
            $hasAccess = false;

            if ($checkType === 'permission') {
                // Kiểm tra permission
                $hasAccess = $user->hasPermission($item);
            } else {
                // Kiểm tra role với các rule đặc biệt
                switch ($item) {
                    case 'admin':
                        $hasAccess = $user->isAdmin();
                        break;

                    case 'manager':
                        $hasAccess = $user->isManager() || $user->isAdmin();
                        break;

                    case 'customer':
                        $hasAccess = $user->isCustomer() || $user->isAdmin();
                        break;

                    case 'dashboard':
                        $hasAccess = $user->canAccessDashboard();
                        break;

                    default:
                        $hasAccess = $user->hasRole($item);
                        break;
                }
            }

            // Nếu có ít nhất 1 role/permission hợp lệ thì cho phép (OR logic)
            if ($hasAccess) {
                return $next($request);
            }
        }

        // Không có quyền
        $message = $checkType === 'permission'
            ? 'Bạn không có quyền thực hiện hành động này.'
            : 'Bạn không có quyền truy cập trang này.';

        if ($request->expectsJson()) {
            return response()->json([
                'status' => false,
                'message' => $message,
            ], 403);
        }

        abort(403, $message);
    }
}
