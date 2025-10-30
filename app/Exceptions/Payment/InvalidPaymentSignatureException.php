<?php

namespace App\Exceptions\Payment;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when payment signature is invalid
 */
class InvalidPaymentSignatureException extends BusinessException
{
    protected int $statusCode = 400;

    public function __construct(
        string $message = 'Invalid payment signature',
        ?string $userMessage = 'Chữ ký thanh toán không hợp lệ. Giao dịch có thể bị giả mạo.'
    ) {
        parent::__construct($message, $userMessage);
    }
}
