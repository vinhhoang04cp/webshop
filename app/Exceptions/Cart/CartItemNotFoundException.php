<?php

namespace App\Exceptions\Cart;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when cart item not found
 */
class CartItemNotFoundException extends BusinessException
{
    protected int $statusCode = 404;

    public function __construct(
        int $cartItemId,
        string $message = 'Cart item not found'
    ) {
        $userMessage = "Không tìm thấy sản phẩm trong giỏ hàng (ID: {$cartItemId})";

        parent::__construct($message, $userMessage);
    }
}
