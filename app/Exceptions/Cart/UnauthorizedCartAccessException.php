<?php

namespace App\Exceptions\Cart;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when user doesn't have permission to access cart
 */
class UnauthorizedCartAccessException extends BusinessException
{
    protected int $statusCode = 403;

    public function __construct(
        string $message = 'Unauthorized cart access',
        ?string $userMessage = 'Bạn không có quyền truy cập giỏ hàng này!'
    ) {
        parent::__construct($message, $userMessage);
    }
}
