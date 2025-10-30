<?php

namespace App\Exceptions\Coupon;

/**
 * Exception thrown when order doesn't meet coupon's minimum amount
 */
class MinimumOrderAmountNotMetException extends InvalidCouponException
{
    public function __construct(
        float $minAmount,
        string $message = 'Minimum order amount not met'
    ) {
        $formattedAmount = number_format($minAmount, 0, ',', '.');
        $userMessage = "Đơn hàng chưa đạt giá trị tối thiểu {$formattedAmount} VND để áp dụng mã giảm giá";

        parent::__construct($message, $userMessage);
    }
}
