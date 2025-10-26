<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $orderService;

    public function __construct(PaymentService $paymentService, OrderService $orderService)
    {
        $this->paymentService = $paymentService;
        $this->orderService = $orderService;
    }

    /**
     * Tạo URL thanh toán VNPay
     */
    public function createPayment(Request $request)
    {
        $orderId = $request->order_id ?? session('pending_payment_order_id');

        if (! $orderId) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng');
        }

        session()->forget('pending_payment_order_id');

        try {
            // Sử dụng OrderService để lấy và validate order
            $order = $this->orderService->getOrderForPayment($orderId, auth()->id());

            $paymentData = $this->paymentService->createVNPayPaymentUrl($orderId, $request->ip());

            // Lưu thông tin transaction vào session
            session([
                'vnpay_txnref' => $paymentData['txn_ref'],
                'order_id' => $paymentData['order_id'],
            ]);

            return redirect($paymentData['url']);
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }
    }

    /**
     * Xử lý callback từ VNPay sau khi thanh toán
     */
    public function vnpayReturn(Request $request)
    {
        $inputData = $request->all();

        // Kiểm tra chữ ký
        if (! $this->paymentService->validateVNPayCallback($inputData)) {
            return redirect()->route('cart.index')->with('error', 'Giao dịch không hợp lệ');
        }

        try {
            $result = $this->paymentService->processVNPayReturn($inputData, auth()->id());

            if ($result['success']) {
                return redirect()->route('payment.success', ['order_id' => $result['order_id']])
                    ->with('success', $result['message']);
            } else {
                return redirect()->route('payment.failed', ['order_id' => $result['order_id']])
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
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
        $inputData = $request->all();

        try {
            // Kiểm tra chữ ký
            if (! $this->paymentService->validateVNPayCallback($inputData)) {
                return response()->json([
                    'RspCode' => '97',
                    'Message' => 'Invalid signature',
                ]);
            }

            $returnData = $this->paymentService->processVNPayIPN($inputData);

            return response()->json($returnData);
        } catch (\Exception $e) {
            return response()->json([
                'RspCode' => '99',
                'Message' => 'Unknown error',
            ]);
        }
    }

    /**
     * Trang thành công
     */
    public function success(Request $request)
    {
        $orderId = $request->get('order_id');
        
        try {
            $order = $this->orderService->getOrderWithItemsForDisplay($orderId);
            return view('payment.success', compact('order'));
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng');
        }
    }

    /**
     * Trang thất bại
     */
    public function failed(Request $request)
    {
        $orderId = $request->get('order_id');
        
        try {
            $order = $this->orderService->getOrderById($orderId);
            return view('payment.failed', compact('order'));
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', 'Không tìm thấy đơn hàng');
        }
    }
}
