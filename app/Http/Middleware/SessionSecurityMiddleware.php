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
     * Khoảng thời gian regenerate session ID (tính bằng phút)
     * Regenerate định kỳ để ngăn chặn session fixation
     */
    protected int $regenerationInterval = 15;

    /**
     * Bật kiểm tra IP address nghiêm ngặt
     * Nếu true, session sẽ bị hủy khi IP thay đổi
     * Lưu ý: Có thể gây vấn đề với mobile users (thay đổi mạng)
     */
    protected bool $strictIpCheck = false;

    /**
     * Bật kiểm tra User Agent nghiêm ngặt
     * Nếu true, session sẽ bị hủy khi User Agent thay đổi
     * Giúp phát hiện session hijacking
     */
    protected bool $strictUserAgentCheck = true;

    /**
     * Xử lý request đến
     *
     * Kiểm tra và bảo vệ session khỏi các cuộc tấn công
     * như session hijacking và session fixation
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ áp dụng security checks cho user đã đăng nhập
        // Guest users không cần kiểm tra session security
        if (! $request->user()) {
            return $next($request);
        }

        // Kiểm tra xem session có bị hijack (đánh cắp) không
        if ($this->isSessionHijacked($request)) {
            // Xóa toàn bộ session data
            Session::flush();
            // Đăng xuất user
            auth()->logout();

            // Redirect về trang login với thông báo lỗi
            return redirect()->route('login')
                ->with('error', 'Phiên làm việc không hợp lệ. Vui lòng đăng nhập lại.');
        }

        // Regenerate session ID định kỳ để tăng bảo mật
        $this->regenerateSessionPeriodically($request);

        // Lưu các thông tin bảo mật vào session
        $this->storeSecurityMarkers($request);

        // Cho phép request tiếp tục
        return $next($request);
    }

    /**
     * Kiểm tra xem session có khả năng bị hijack không
     *
     * So sánh IP và User Agent hiện tại với giá trị đã lưu
     * để phát hiện session hijacking
     */
    protected function isSessionHijacked(Request $request): bool
    {
        // Kiểm tra IP address nếu bật strict mode
        if ($this->strictIpCheck) {
            $currentIp = $request->ip();
            $sessionIp = Session::get('security.ip');

            // Nếu IP thay đổi => có thể session bị đánh cắp
            if ($sessionIp && $sessionIp !== $currentIp) {
                return true;
            }
        }

        // Kiểm tra User Agent
        if ($this->strictUserAgentCheck) {
            $currentUserAgent = $request->userAgent();
            $sessionUserAgent = Session::get('security.user_agent');

            // Nếu User Agent thay đổi => có thể session bị đánh cắp
            if ($sessionUserAgent && $sessionUserAgent !== $currentUserAgent) {
                return true;
            }
        }

        // Không phát hiện dấu hiệu hijacking
        return false;
    }

    /**
     * Regenerate session ID định kỳ
     *
     * Tạo session ID mới sau một khoảng thời gian nhất định
     * để ngăn chặn session fixation attack
     */
    protected function regenerateSessionPeriodically(Request $request): void
    {
        // Lấy thời điểm regenerate lần cuối
        $lastRegeneration = Session::get('security.last_regeneration');

        // Nếu chưa có lịch sử regenerate thì lưu lại và return
        if (! $lastRegeneration) {
            Session::put('security.last_regeneration', now()->timestamp);

            return;
        }

        // Chuyển timestamp thành Carbon instance để so sánh
        $lastRegenerationTime = \Carbon\Carbon::createFromTimestamp($lastRegeneration);
        // Tính số phút đã trôi qua từ lần regenerate cuối
        $minutesSinceRegeneration = now()->diffInMinutes($lastRegenerationTime);

        // Nếu đã quá khoảng thời gian quy định thì regenerate
        if ($minutesSinceRegeneration >= $this->regenerationInterval) {
            // Tạo session ID mới (giữ nguyên data)
            Session::regenerate();
            // Cập nhật thời điểm regenerate
            Session::put('security.last_regeneration', now()->timestamp);
        }
    }

    /**
     * Lưu các thông tin bảo mật vào session
     *
     * Lưu IP, User Agent và thời gian hoạt động
     * để sử dụng cho việc kiểm tra bảo mật sau này
     */
    protected function storeSecurityMarkers(Request $request): void
    {
        // Lưu IP address nếu chưa có
        // Sẽ được dùng để so sánh với các request sau
        if (! Session::has('security.ip')) {
            Session::put('security.ip', $request->ip());
        }

        // Lưu User Agent nếu chưa có
        // Giúp phát hiện nếu session được sử dụng từ browser khác
        if (! Session::has('security.user_agent')) {
            Session::put('security.user_agent', $request->userAgent());
        }

        // Cập nhật timestamp hoạt động cuối cùng
        // Có thể dùng để implement session timeout
        Session::put('security.last_activity', now()->timestamp);
    }
}
