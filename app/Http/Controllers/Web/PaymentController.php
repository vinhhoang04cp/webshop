<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPayment(Request $request)
    {
        // Lấy order_id từ request hoặc session
        $orderId = $request->order_id ?? session('pending_payment_order_id');

        if (! $orderId) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng');
        }

        // Xóa session sau khi lấy
        session()->forget('pending_payment_order_id');

        $order = Order::where('order_id', $orderId)->firstOrFail();

        // Kiểm tra order thuộc về user hiện tại
        if ($order->user_id !== auth()->id()) {
            return redirect()->route('cart.index')->with('error', 'Đơn hàng không hợp lệ');
        }

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
        $vnp_TxnRef = $order->order_id.'_'.time(); // Mã đơn hàng
        $vnp_OrderInfo = 'Thanh toán đơn hàng #'.$order->order_id;
        $vnp_OrderType = 'billpayment';
        $vnp_Amount = $order->total_amount * 100; // VNPay yêu cầu số tiền * 100
        $vnp_Locale = 'vn';
        $vnp_BankCode = ''; // Để trống để hiển thị tất cả phương thức
        $vnp_IpAddr = $request->ip();

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

        // Tạo hashdata và query string theo tài liệu VNPay
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

            // Debug log
            Log::info('VNPay Payment URL', [
                'hashdata' => $hashdata,
                'secure_hash' => $vnpSecureHash,
                'full_url' => $vnp_Url,
            ]);
        }

        // Lưu thông tin transaction vào session hoặc database nếu cần
        session(['vnpay_txnref' => $vnp_TxnRef, 'order_id' => $order->order_id]);

        return redirect($vnp_Url);
    }

    /**
     * Xử lý callback từ VNPay sau khi thanh toán
     */
    public function vnpayReturn(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        // Lấy vnp_SecureHash
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        unset($inputData['vnp_SecureHash']);
        unset($inputData['vnp_SecureHashType']);

        // Sắp xếp tham số
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

        // Tính toán SecureHash
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Kiểm tra chữ ký
        if ($secureHash != $vnp_SecureHash) {
            Log::error('VNPay: Invalid signature', [
                'expected' => $secureHash,
                'received' => $vnp_SecureHash,
            ]);

            return redirect()->route('cart.index')->with('error', 'Giao dịch không hợp lệ');
        }

        // Lấy thông tin từ VNPay
        $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
        $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
        $vnp_Amount = $inputData['vnp_Amount'] ?? 0;
        $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';
        $vnp_BankCode = $inputData['vnp_BankCode'] ?? '';

        // Lấy order_id từ TxnRef
        $orderIdFromTxn = explode('_', $vnp_TxnRef)[0];

        try {
            DB::beginTransaction();

            $order = Order::findOrFail($orderIdFromTxn);

            // Kiểm tra mã phản hồi
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
                    'amount' => $vnp_Amount / 100,
                ]);

                return redirect()->route('payment.success', ['order_id' => $order->order_id])
                    ->with('success', 'Thanh toán thành công!');
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

                return redirect()->route('payment.failed', ['order_id' => $order->order_id])
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('VNPay: Error processing payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('cart.index')
                ->with('error', 'Có lỗi xảy ra khi xử lý thanh toán');
        }
    }

    /**
     * IPN - Instant Payment Notification từ VNPay
     * VNPay sẽ gọi URL này để xác nhận giao dịch
     */
    public function vnpayIPN(Request $request)
    {
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $inputData = $request->all();

        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';
        $returnData = [];

        try {
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

            $vnp_TxnRef = $inputData['vnp_TxnRef'] ?? '';
            $vnp_Amount = $inputData['vnp_Amount'] ?? 0;
            $vnp_ResponseCode = $inputData['vnp_ResponseCode'] ?? '';
            $vnp_TransactionNo = $inputData['vnp_TransactionNo'] ?? '';

            $orderIdFromTxn = explode('_', $vnp_TxnRef)[0];
            $order = Order::find($orderIdFromTxn);

            if ($secureHash == $vnp_SecureHash) {
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
            } else {
                $returnData['RspCode'] = '97';
                $returnData['Message'] = 'Invalid signature';
            }
        } catch (\Exception $e) {
            $returnData['RspCode'] = '99';
            $returnData['Message'] = 'Unknown error';

            Log::error('VNPay IPN Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json($returnData);
    }

    /**
     * Trang thành công
     */
    public function success(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::where('order_id', $orderId)->with('orderItems.product')->firstOrFail();

        return view('payment.success', compact('order'));
    }

    /**
     * Trang thất bại
     */
    public function failed(Request $request)
    {
        $orderId = $request->get('order_id');
        $order = Order::where('order_id', $orderId)->firstOrFail();

        return view('payment.failed', compact('order'));
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
