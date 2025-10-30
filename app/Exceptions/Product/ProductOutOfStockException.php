<?php

namespace App\Exceptions\Product;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when product is out of stock
 */
class ProductOutOfStockException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $productName,
        string $message = 'Product is out of stock'
    ) {
        $userMessage = "Sản phẩm '{$productName}' đã hết hàng";

        parent::__construct($message, $userMessage);
    }
}
