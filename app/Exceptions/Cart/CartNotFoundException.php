<?php

namespace App\Exceptions\Cart;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when cart not found
 */
class CartNotFoundException extends BusinessException
{
    protected int $statusCode = 404;

    public function __construct(
        int $cartId,
        string $message = 'Cart not found'
    ) {
        $userMessage = "Không tìm thấy giỏ hàng (ID: {$cartId})";

        parent::__construct($message, $userMessage);
    }
}
