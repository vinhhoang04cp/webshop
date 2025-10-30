<?php

namespace App\Exceptions\Order;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when trying to delete an order that cannot be deleted
 */
class OrderCannotBeDeletedException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        int $orderId,
        string $status,
        string $message = 'Order cannot be deleted'
    ) {
        $userMessage = "Không thể xóa đơn hàng #{$orderId} với trạng thái '{$status}'. Chỉ có thể xóa đơn hàng đã giao hoặc đã hủy.";

        parent::__construct($message, $userMessage);
    }
}
