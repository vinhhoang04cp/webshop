<?php

namespace App\Exceptions\Order;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when order status transition is invalid
 */
class InvalidOrderStatusTransitionException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $currentStatus,
        string $newStatus,
        string $message = 'Invalid order status transition'
    ) {
        $userMessage = "Không thể chuyển trạng thái đơn hàng từ '{$currentStatus}' sang '{$newStatus}'";

        parent::__construct($message, $userMessage);
    }
}
