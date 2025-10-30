<?php

namespace App\Exceptions\Payment;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when payment fails
 */
class PaymentFailedException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $reason,
        ?string $userMessage = null
    ) {
        parent::__construct(
            "Payment failed: {$reason}",
            $userMessage ?? "Thanh toán thất bại: {$reason}"
        );
    }
}
