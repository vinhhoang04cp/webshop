<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\SuccessResource;
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
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayment(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,order_id',
        ], [
            'order_id.required' => 'Vui lòng cung cấp mã đơn hàng',
            'order_id.exists' => 'Đơn hàng không tồn tại',
        ]);

        try {
            // Sử dụng OrderService để lấy và xác thực đơn hàng
            $order = $this->orderService->getOrderForPayment($request->order_id, auth()->id());

            $paymentData = $this->paymentService->createVNPayPaymentUrl($request->order_id, $request->ip());

            return SuccessResource::withData([
                'payment_url' => $paymentData['url'],
                'txn_ref' => $paymentData['txn_ref'],
                'order_id' => $paymentData['order_id'],
            ], 'Tạo URL thanh toán thành công');
        } catch (\Exception $e) {
            return ErrorResource::badRequest($e->getMessage());
        }
    }

    /**
     * Xử lý callback từ VNPay sau khi thanh toán
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vnpayReturn(Request $request)
    {
        $inputData = $request->all();

        // Kiểm tra chữ ký từ VNPay
        if (! $this->paymentService->validateVNPayCallback($inputData)) {
            return ErrorResource::badRequest('Giao dịch không hợp lệ');
        }

        try {
            $result = $this->paymentService->processVNPayReturn($inputData, auth()->id());

            if ($result['success']) {
                // Lấy thông tin đơn hàng để trả về
                $order = $this->orderService->getOrderWithItemsForDisplay($result['order_id']);

                return (new PaymentResource($order))->additional([
                    'status' => true,
                    'message' => $result['message'],
                ]);
            } else {
                return ErrorResource::badRequest($result['message'], [
                    'order_id' => $result['order_id'],
                ]);
            }
        } catch (\Exception $e) {
            return ErrorResource::serverError('Có lỗi xảy ra khi xử lý thanh toán', $e->getMessage());
        }
    }

    /**
     * IPN - Instant Payment Notification từ VNPay
     * VNPay sẽ gọi URL này để xác nhận giao dịch
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vnpayIPN(Request $request)
    {
        $inputData = $request->all();

        try {
            // Kiểm tra chữ ký từ VNPay
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
     * Lấy trạng thái thanh toán của đơn hàng
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentStatus($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            // Kiểm tra quyền truy cập
            if (! auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
                return ErrorResource::forbidden('Bạn không có quyền xem đơn hàng này');
            }

            return SuccessResource::withData([
                'order_id' => $order->order_id,
                'payment_status' => $order->payment_status,
                'payment_method' => $order->payment_method,
                'transaction_id' => $order->transaction_id,
                'total_amount' => $order->total_amount,
                'paid_at' => $order->paid_at?->format('Y-m-d H:i:s'),
            ], 'Payment status retrieved successfully');
        } catch (\Exception $e) {
            return ErrorResource::notFound('Không tìm thấy đơn hàng');
        }
    }

    /**
     * Lấy chi tiết thanh toán thành công
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentSuccess($orderId)
    {
        try {
            $order = $this->orderService->getOrderWithItemsForDisplay($orderId);

            // Kiểm tra quyền truy cập
            if (! auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
                return ErrorResource::forbidden('Bạn không có quyền xem đơn hàng này');
            }

            return (new PaymentResource($order))->additional([
                'status' => true,
                'message' => 'Thanh toán thành công',
            ]);
        } catch (\Exception $e) {
            return ErrorResource::notFound('Không tìm thấy đơn hàng');
        }
    }

    /**
     * Lấy chi tiết thanh toán thất bại
     *
     * @param  string  $orderId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentFailed($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            // Kiểm tra quyền truy cập
            if (! auth()->user()->hasRole('admin') && $order->user_id !== auth()->id()) {
                return ErrorResource::forbidden('Bạn không có quyền xem đơn hàng này');
            }

            return ErrorResource::badRequest('Thanh toán thất bại', [
                'order_id' => $order->order_id,
                'payment_status' => $order->payment_status,
                'total_amount' => $order->total_amount,
            ]);
        } catch (\Exception $e) {
            return ErrorResource::notFound('Không tìm thấy đơn hàng');
        }
    }
}
