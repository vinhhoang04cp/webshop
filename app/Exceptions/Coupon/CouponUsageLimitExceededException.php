<?php

namespace App\Exceptions\Coupon;

/**
 * Exception thrown when coupon usage limit is exceeded
 */
class CouponUsageLimitExceededException extends InvalidCouponException
{
    public function __construct(
        string $code,
        string $message = 'Coupon usage limit exceeded'
    ) {
        $userMessage = "Mã giảm giá '{$code}' đã hết lượt sử dụng";

        parent::__construct($message, $userMessage);
    }
}
