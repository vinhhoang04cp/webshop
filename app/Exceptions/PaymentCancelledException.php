<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Exception khi người dùng hủy giao dịch thanh toán
 */
class PaymentCancelledException extends Exception
{
    /**
     * Constructor
     */
    public function __construct($message = 'Giao dịch thanh toán đã bị hủy bởi người dùng', $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render($request)
    {
        // API request - trả về JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => 'PAYMENT_CANCELLED',
                'data' => null,
            ], 400);
        }

        // Web request - redirect về giỏ hàng với thông báo
        return redirect()->route('cart.index')
            ->with('error', $this->getMessage());
    }

    /**
     * Report the exception (logging)
     */
    public function report(): bool
    {
        // Log nhẹ (info level) - không phải lỗi hệ thống
        Log::info('Payment cancelled by user', [
            'message' => $this->getMessage(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Không gửi lên error tracking (Sentry) vì đây là user action
        return false;
    }
}
