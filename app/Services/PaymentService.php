<?php

namespace App\Services;

use App\Contracts\PaymentServiceInterface;
use App\Exceptions\Payment\InvalidPaymentSignatureException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService implements PaymentServiceInterface
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function createVNPayPaymentUrl($orderId, $ipAddress)
    {
        $order = Order::where('order_id', $orderId)->firstOrFail();

        // Tham số cấu hình VNPay
        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_Returnurl = config('services.vnpay.return_url');

        // Debug log
        Log::info('VNPay Config', [
            'tmn_code' => $vnp_TmnCode,
            'hash_secret_length' => strlen($vnp_HashSecret),
            'return_url' => $vnp_Returnurl,
        ]);

        // Tham số gửi sang VNPay
        $vnp_TxnRef = $order->order_id.'_'.time();
        $vnp_OrderInfo = 'Thanh toán đơn hàng #'.$order->order_id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_amount * 100;
        $vnp_Locale = 'vn';
        $vnp_BankCode = '';
        $vnp_IpAddr = $ipAddress;

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnp_TmnCode,
            'vnp_Amount' => $vnp_Amount,
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => date('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $vnp_IpAddr,
            'vnp_Locale' => $vnp_Locale,
            'vnp_OrderInfo' => $vnp_OrderInfo,
            'vnp_OrderType' => $vnp_OrderType,
            'vnp_ReturnUrl' => $vnp_Returnurl,
            'vnp_TxnRef' => $vnp_TxnRef,
        ];

        if (isset($vnp_BankCode) && $vnp_BankCode != '') {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

        ksort($inputData);
        $query = '';
        $i = 0;
        $hashdata = '';

        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashdata .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key).'='.urlencode($value).'&';
        }

        $vnp_Url = $vnp_Url.'?'.$query;
        if (isset($vnp_HashSecret)) {
            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
            $vnp_Url .= 'vnp_SecureHash='.$vnpSecureHash;

            Log::info('VNPay Payment URL', [
                'hashdata' => $hashdata,
                'secure_hash' => $vnpSecureHash,
                'full_url' => $vnp_Url,
            ]);
        }

        return [
            'url' => $vnp_Url,
            'txn_ref' => $vnp_TxnRef,
            'order_id' => $order->order_id,
        ];
    }

    /**
     * Xác thực callback từ VNPay
     */
    public function validateVNPayCallback($inputData)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        ksort($inputData);
        $hashData = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData .= urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash != $vnp_SecureHash) {
            Log::error('VNPay: Invalid signature', [
                'expected' => $secureHash,
                'received' => $vnp_SecureHash,
            ]);

            throw new InvalidPaymentSignatureException;
        }

        return true;
    }

    /**
     * Xử lý kết quả thanh toán VNPay
     */
    public function processVNPayReturn($inputData, $userId = null)
    {
        $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
        $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
        $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';

        $orderIdFromTxn = explode('_', $vnp_TxnRef)[0];

        DB::beginTransaction();

        try {
            $order = Order::findOrFail($orderIdFromTxn);

            if ($vnp_ResponseCode == '00') {
                // Giao dịch thành công
                $order->update([
                    'payment_status' => 'paid',
                    'payment_method' => 'vnpay',
                    'transaction_id' => $vnp_TransactionNo,
                    'paid_at' => now(),
                ]);

                // Xóa giỏ hàng sau khi thanh toán thành công
                if ($order->user_id) {
                    $cart = Cart::where('user_id', $order->user_id)->first();
                    if ($cart) {
                        CartItem::where('cart_id', $cart->id)->delete();
                    }
                }

                DB::commit();

                Log::info('VNPay: Payment successful', [
                    'order_id' => $order->order_id,
                    'transaction_id' => $vnp_TransactionNo,
                    'amount' => $inputData['vnp_Amount'] / 100,
                ]);

                return [
                    'success' => true,
                    'order_id' => $order->order_id,
                    'message' => 'Thanh toán thành công!',
                ];
            } else {
                // Giao dịch thất bại
                $order->update([
                    'payment_status' => 'failed',
                    'payment_method' => 'vnpay',
                ]);

                DB::commit();

                $errorMessage = $this->getVNPayErrorMessage($vnp_ResponseCode);

                Log::warning('VNPay: Payment failed', [
                    'order_id' => $order->order_id,
                    'response_code' => $vnp_ResponseCode,
                    'message' => $errorMessage,
                ]);

                // Throw specific exception based on response code
                if ($vnp_ResponseCode == '24') {
                    throw new PaymentCancelledException;
                } else {
                    throw new PaymentFailedException($errorMessage, $vnp_ResponseCode);
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VNPay: Error processing payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Xử lý IPN từ VNPay
     */
    public function processVNPayIPN($inputData)
    {
        $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
        $vnp_Amount = $inputData['vnp_Amount'] ?? 0;
        $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
        $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';

        $orderIdFromTxn = explode('_', $vnp_TxnRef)[0];
        $order = Order::find($orderIdFromTxn);

        $returnData = [];

        if ($order != null) {
            if ($order->total_amount * 100 == $vnp_Amount) {
                if ($order->payment_status == 'pending') {
                    if ($vnp_ResponseCode == '00') {
                        $order->update([
                            'payment_status' => 'paid',
                            'transaction_id' => $vnp_TransactionNo,
                            'paid_at' => now(),
                        ]);

                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Success';
                    } else {
                        $order->update(['payment_status' => 'failed']);
                        $returnData['RspCode'] = '00';
                        $returnData['Message'] = 'Confirm Success';
                    }
                } else {
                    $returnData['RspCode'] = '02';
                    $returnData['Message'] = 'Order already confirmed';
                }
            } else {
                $returnData['RspCode'] = '04';
                $returnData['Message'] = 'Invalid amount';
            }
        } else {
            $returnData['RspCode'] = '01';
            $returnData['Message'] = 'Order not found';
        }

        return $returnData;
    }

    /**
     * Lấy thông báo lỗi từ VNPay response code
     */
    private function getVNPayErrorMessage($responseCode)
    {
        $messages = [
            '07' => 'Trừ tiền thành công. Giao dịch bị nghi ngờ (liên quan tới lừa đảo, giao dịch bất thường).',
            '09' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng chưa đăng ký dịch vụ InternetBanking tại ngân hàng.',
            '10' => 'Giao dịch không thành công do: Khách hàng xác thực thông tin thẻ/tài khoản không đúng quá 3 lần',
            '11' => 'Giao dịch không thành công do: Đã hết hạn chờ thanh toán. Xin quý khách vui lòng thực hiện lại giao dịch.',
            '12' => 'Giao dịch không thành công do: Thẻ/Tài khoản của khách hàng bị khóa.',
            '13' => 'Giao dịch không thành công do Quý khách nhập sai mật khẩu xác thực giao dịch (OTP).',
            '24' => 'Giao dịch không thành công do: Khách hàng hủy giao dịch',
            '51' => 'Giao dịch không thành công do: Tài khoản của quý khách không đủ số dư để thực hiện giao dịch.',
            '65' => 'Giao dịch không thành công do: Tài khoản của Quý khách đã vượt quá hạn mức giao dịch trong ngày.',
            '75' => 'Ngân hàng thanh toán đang bảo trì.',
            '79' => 'Giao dịch không thành công do: KH nhập sai mật khẩu thanh toán quá số lần quy định.',
            '99' => 'Các lỗi khác (lỗi còn lại, không có trong danh sách mã lỗi đã liệt kê)',
        ];

        return $messages[$responseCode] ?? 'Giao dịch không thành công';
    }
}
