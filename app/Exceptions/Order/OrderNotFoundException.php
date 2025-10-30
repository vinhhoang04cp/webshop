<?php

namespace App\Exceptions\Order;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when order is not found
 */
class OrderNotFoundException extends BusinessException
{
    protected int $statusCode = 404;

    public function __construct(
        int $orderId,
        string $message = 'Order not found'
    ) {
        $userMessage = "Không tìm thấy đơn hàng (ID: {$orderId})";

        parent::__construct($message, $userMessage);
    }
}
