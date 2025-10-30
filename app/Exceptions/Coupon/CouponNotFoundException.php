<?php

namespace App\Exceptions\Coupon;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when coupon is not found
 */
class CouponNotFoundException extends BusinessException
{
    protected int $statusCode = 404;

    public function __construct(
        string $code,
        string $message = 'Coupon not found'
    ) {
        $userMessage = "Không tìm thấy mã giảm giá '{$code}'";

        parent::__construct($message, $userMessage);
    }
}
