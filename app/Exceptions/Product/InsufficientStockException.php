<?php

namespace App\Exceptions\Product;

use App\Exceptions\BusinessException;

/**
 * Exception thrown when product stock is insufficient
 */
class InsufficientStockException extends BusinessException
{
    protected int $statusCode = 422;

    public function __construct(
        string $productName,
        int $availableStock,
        int $requestedQuantity,
        string $message = 'Insufficient stock'
    ) {
        $userMessage = "Sản phẩm '{$productName}' chỉ còn {$availableStock} trong kho, không đủ cho số lượng yêu cầu ({$requestedQuantity})";

        parent::__construct($message, $userMessage);
    }
}
