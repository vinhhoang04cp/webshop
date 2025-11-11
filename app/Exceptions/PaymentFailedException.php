<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Exception khi giao dịch thanh toán thất bại
 */
class PaymentFailedException extends Exception
{
    protected $responseCode;

    protected $vnpayData;

    /**
     * Constructor
     */
    public function __construct($message = 'Giao dịch thanh toán thất bại', $responseCode = null, $vnpayData = [], $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->responseCode = $responseCode;
        $this->vnpayData = $vnpayData;
    }

    /**
     * Get VNPay response code
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Get VNPay data
     */
    public function getVnpayData()
    {
        return $this->vnpayData;
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
                'error_code' => 'PAYMENT_FAILED',
                'vnpay_response_code' => $this->responseCode,
                'data' => null,
            ], 400);
        }

        // Web request - redirect về giỏ hàng với thông báo lỗi
        return redirect()->route('cart.index')
            ->with('error', $this->getMessage())
            ->with('payment_error_code', $this->responseCode);
    }

    /**
     * Report the exception (logging)
     */
    public function report(): bool
    {
        // Log error level vì đây là lỗi thanh toán thực sự
        Log::error('Payment failed', [
            'message' => $this->getMessage(),
            'response_code' => $this->responseCode,
            'vnpay_data' => $this->vnpayData,
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Gửi lên error tracking (Sentry) để monitor
        return true;
    }
}
