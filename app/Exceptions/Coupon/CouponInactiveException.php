<?php

namespace App\Exceptions\Coupon;

/**
 * Exception thrown when coupon is inactive
 */
class CouponInactiveException extends InvalidCouponException
{
    public function __construct(
        string $code,
        string $message = 'Coupon is inactive'
    ) {
        $userMessage = "Mã giảm giá '{$code}' không còn hoạt động";

        parent::__construct($message, $userMessage);
    }
}
