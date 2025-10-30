<?php

namespace App\Exceptions\Product;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when product is not found
 */
class ProductNotFoundException extends BusinessException
{
    protected int $statusCode = 404;

    public function __construct(
        int $productId,
        string $message = 'Product not found'
    ) {
        $userMessage = "Không tìm thấy sản phẩm (ID: {$productId})";

        parent::__construct($message, $userMessage);
    }
}
