<?php

namespace App\Exceptions\Order;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when user doesn't own the order
 */
class UnauthorizedOrderAccessException extends BusinessException
{
    protected int $statusCode = 403;

    public function __construct(
        int $orderId,
        string $message = 'Unauthorized order access'
    ) {
        $userMessage = "Bạn không có quyền truy cập đơn hàng #{$orderId}";

        parent::__construct($message, $userMessage);
    }
}
