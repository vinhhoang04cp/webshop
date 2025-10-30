<?php

namespace App\Contracts;

interface PaymentServiceInterface
{
    /**
     * Tạo URL thanh toán VNPay
     *
     * @param  int  $orderId
     * @param  string  $ipAddress
     * @return array ['url' => string, 'txn_ref' => string, 'order_id' => int]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function createVNPayPaymentUrl($orderId, $ipAddress);

    /**
     * Xác thực callback từ VNPay
     *
     * @param  array  $inputData
     * @return bool
     */
    public function validateVNPayCallback($inputData);

    /**
     * Xử lý kết quả thanh toán VNPay
     *
     * @param  array  $inputData
     * @param  int|null  $userId
     * @return array ['success' => bool, 'order_id' => int, 'message' => string]
     *
     * @throws \Exception
     */
    public function processVNPayReturn($inputData, $userId = null);

    /**
     * Xử lý IPN (Instant Payment Notification) từ VNPay
     *
     * @param  array  $inputData
     * @return array ['RspCode' => string, 'Message' => string]
     */
    public function processVNPayIPN($inputData);
}
