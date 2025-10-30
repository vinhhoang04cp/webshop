<?php

namespace App\Exceptions\Coupon;

/**
 * Exception thrown when coupon is expired
 */
class CouponExpiredException extends InvalidCouponException
{
    public function __construct(
        string $code,
        string $message = 'Coupon has expired'
    ) {
        $userMessage = "Mã giảm giá '{$code}' đã hết hạn sử dụng";

        parent::__construct($message, $userMessage);
    }
}
