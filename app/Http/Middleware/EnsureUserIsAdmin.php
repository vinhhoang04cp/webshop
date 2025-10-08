<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserIsAdmin Middleware
 *
 * Middleware này được sử dụng để bảo vệ các route cần quyền admin
 * Kiểm tra user hiện tại có role 'admin' hay không
 * Nếu không có quyền admin, trả về lỗi 403 Forbidden
 */
class EnsureUserIsAdmin
{
    /**
     * Xử lý request đến - kiểm tra quyền admin trước khi cho phép truy cập
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response // function handle(Request $request, Closure $next): Response voi tham so truyen vao la $request va $next
    {
        // Closure $next la ham de tiep tuc xu ly request

        if (! $request->user() || ! $request->user()->hasRole('admin')) { // Nếu không có user hoặc không có role admin
            // Nếu user không tồn tại HOẶC không có role admin
            // Trả về lỗi 403 Forbidden với message rõ ràng
            return response()->json([
                'status' => false,                              // Trạng thái thất bại
                'message' => 'Access denied. Admin role required.', // Thông báo cần quyền admin
            ], 403); // HTTP status 403 Forbidden - không có quyền truy cập
        }

        // Bước 3: Nếu user có quyền admin, cho phép request tiếp tục
        // $next($request) - chuyển request sang middleware/controller tiếp theo
        return $next($request); // ham next de tiep tuc xu ly request voi tham so truyen vao la $request
    }
}
