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
     * Danh sách các origin được phép truy cập
     * Chỉ các domain trong danh sách này mới được phép gửi request tới API
     */
    protected array $allowedOrigins = [
        'http://localhost:3000', // Frontend development server
        'http://localhost:8000', // Backend development server
        'https://yourdomain.com', // Production domain
    ];

    /**
     * Các HTTP method được phép sử dụng
     * Định nghĩa các phương thức HTTP mà client có thể sử dụng khi gọi API
     */
    protected array $allowedMethods = [
        'GET',     // Lấy dữ liệu
        'POST',    // Tạo mới
        'PUT',     // Cập nhật toàn bộ
        'PATCH',   // Cập nhật một phần
        'DELETE',  // Xóa
        'OPTIONS', // Preflight request
    ];

    /**
     * Các header được phép gửi trong request
     * Chỉ những header này mới được phép xuất hiện trong request từ client
     */
    protected array $allowedHeaders = [
        'Accept',            // Loại dữ liệu client muốn nhận
        'Authorization',     // Token xác thực
        'Content-Type',      // Loại nội dung gửi lên
        'X-Requested-With',  // Xác định AJAX request
        'X-CSRF-TOKEN',      // Token bảo mật CSRF
    ];

    /**
     * Các header được phép client đọc từ response
     * Những header này sẽ được expose để JavaScript có thể truy cập
     */
    protected array $exposedHeaders = [
        'X-Total-Count', // Tổng số bản ghi (cho pagination)
        'X-Page-Count',  // Tổng số trang (cho pagination)
    ];

    /**
     * Xử lý request đến
     *
     * Method chính của middleware để xử lý CORS cho mọi request
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Xử lý preflight request (OPTIONS)
        // Browser tự động gửi OPTIONS request trước khi gửi request thực tế
        if ($request->isMethod('OPTIONS')) {
            return $this->preflightResponse($request);
        }

        // Cho phép request đi tiếp
        $response = $next($request);

        // Thêm các CORS headers vào response
        $this->addCorsHeaders($request, $response);

        return $response;
    }

    /**
     * Xử lý preflight OPTIONS request
     *
     * Browser gửi OPTIONS request để kiểm tra xem server có cho phép
     * cross-origin request hay không trước khi gửi request thực tế
     */
    protected function preflightResponse(Request $request): Response
    {
        // Trả về response rỗng với status code 204 (No Content)
        $response = response('', 204);

        // Thêm CORS headers để browser biết request được phép
        $this->addCorsHeaders($request, $response);

        return $response;
    }

    /**
     * Thêm các CORS headers vào response
     *
     * Set các headers cần thiết để browser cho phép cross-origin request
     */
    protected function addCorsHeaders(Request $request, Response $response): void
    {
        // Lấy origin từ request header
        $origin = $request->header('Origin');

        // Kiểm tra và cho phép origin nếu nằm trong whitelist
        if ($this->isAllowedOrigin($origin)) {
            // Cho phép origin cụ thể (bảo mật hơn là dùng *)
            $response->headers->set('Access-Control-Allow-Origin', $origin);

            // Cho phép gửi credentials (cookies, authorization headers)
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        // Set các HTTP methods được phép sử dụng
        $response->headers->set(
            'Access-Control-Allow-Methods',
            implode(', ', $this->allowedMethods)
        );

        // Set các headers được phép gửi trong request
        $response->headers->set(
            'Access-Control-Allow-Headers',
            implode(', ', $this->allowedHeaders)
        );

        // Set các headers mà client được phép đọc từ response
        if (! empty($this->exposedHeaders)) {
            $response->headers->set(
                'Access-Control-Expose-Headers',
                implode(', ', $this->exposedHeaders)
            );
        }

        // Set thời gian cache cho preflight request (24 giờ)
        // Browser sẽ không gửi lại OPTIONS request trong khoảng thời gian này
        $response->headers->set('Access-Control-Max-Age', '86400'); // 24 hours
    }

    /**
     * Kiểm tra xem origin có được phép truy cập hay không
     *
     * Validate origin để đảm bảo chỉ các domain đáng tin cậy
     * mới có thể gửi request đến API
     */
    protected function isAllowedOrigin(?string $origin): bool
    {
        // Nếu không có origin thì từ chối
        if (! $origin) {
            return false;
        }

        // Kiểm tra origin từ file cấu hình .env
        // Cho phép cấu hình động origins thông qua config
        $envAllowedOrigins = config('cors.allowed_origins', []);
        if (in_array($origin, $envAllowedOrigins)) {
            return true;
        }

        // Kiểm tra với danh sách hardcoded trong class
        return in_array($origin, $this->allowedOrigins);
    }
}
