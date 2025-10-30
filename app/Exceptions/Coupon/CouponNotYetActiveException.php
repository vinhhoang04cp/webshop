<?php

namespace App\Exceptions\Coupon;

/**
 * Exception thrown when coupon is not yet active
 */
class CouponNotYetActiveException extends InvalidCouponException
{
    public function __construct(
        string $code,
        string $startDate,
        string $message = 'Coupon is not yet active'
    ) {
        $userMessage = "Mã giảm giá '{$code}' chưa có hiệu lực (bắt đầu: {$startDate})";

        parent::__construct($message, $userMessage);
    }
}
