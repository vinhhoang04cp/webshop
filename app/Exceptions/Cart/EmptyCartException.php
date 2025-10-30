<?php

namespace App\Exceptions\Cart;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when cart is empty
 */
class EmptyCartException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $message = 'Cart is empty',
        ?string $userMessage = 'Giỏ hàng trống! Vui lòng thêm sản phẩm trước khi thanh toán.'
    ) {
        parent::__construct($message, $userMessage);
    }
}
