<?php

namespace App\Exceptions\Coupon;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when coupon is invalid
 */
class InvalidCouponException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $reason = 'Coupon is invalid',
        ?string $userMessage = null
    ) {
        parent::__construct($reason, $userMessage ?? $reason);
    }
}
