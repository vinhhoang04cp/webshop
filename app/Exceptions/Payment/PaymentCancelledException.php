<?php

namespace App\Exceptions\Payment;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when payment is cancelled by user
 */
class PaymentCancelledException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $message = 'Payment was cancelled by user',
        ?string $userMessage = 'Bạn đã hủy thanh toán'
    ) {
        parent::__construct($message, $userMessage);
    }
}
