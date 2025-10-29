<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Http\Resources\SuccessResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Hiển thị danh sách đơn hàng
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'search', 'sort_by', 'sort_order']);

        if ($request->user()->isAdmin()) {
            $filters = array_merge($filters, $request->only(['user_id', 'min_date', 'max_date', 'min_total', 'max_total']));
        }

        $perPage = $request->input('per_page', 15);

        $orders = $this->orderService->getOrders(
            $request->user()->id,
            $request->user()->isAdmin(),
            $filters,
            $perPage
        );

        return new OrderCollection($orders);
    }

    /**
     * Lưu đơn hàng mới được tạo
     */
    public function store(OrderRequest $request)
    {
        DB::beginTransaction();

        try {
            $order = $this->orderService->createOrder($request->validated());

            DB::commit();

            try {
                Order::reorderIds();
            } catch (\Exception $e) {
                \Log::warning('Failed to reorder Order IDs: '.$e->getMessage());
            }

            return (new OrderResource($order))->response()->setStatusCode(201);

        } catch (\Exception $e) {
            DB::rollback();

            return ErrorResource::unprocessableEntity('Failed to create order', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Hiển thị đơn hàng theo ID
     */
    public function show(Request $request, $id)
    {
        $order = $this->orderService->findOrder($id, true);

        if (! $order) {
            return ErrorResource::notFound('Order not found');
        }

        if (! $request->user()->isAdmin() && ! $this->orderService->userOwnsOrder($order, $request->user()->id)) {
            return ErrorResource::forbidden('Access denied. You can only access your own orders.');
        }

        return (new OrderResource($order))->additional([
            'status' => true,
            'message' => 'Order retrieved successfully',
        ]);
    }

    /**
     * Cập nhật đơn hàng theo ID
     */
    public function update(OrderRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $order = $this->orderService->findOrder($id);

            if (! $order) {
                DB::rollback();

                return ErrorResource::notFound('Order not found');
            }

            if (! $request->user()->isAdmin() && ! $this->orderService->userOwnsOrder($order, $request->user()->id)) {
                DB::rollback();

                return ErrorResource::forbidden('Access denied. You can only update your own orders.');
            }

            $orderData = $request->validated();

            if (isset($orderData['status']) && $orderData['status'] !== $order->status) {
                if (! $request->user()->isAdmin()) {
                    DB::rollback();

                    return ErrorResource::forbidden('Only admin can change order status.');
                }

                if (! $this->orderService->canTransitionToStatus($order, $orderData['status'])) {
                    DB::rollback();

                    return ErrorResource::unprocessableEntity(
                        "Cannot change status from '{$order->status}' to '{$orderData['status']}'. Invalid status transition.",
                        [
                            'current_status' => $order->status,
                            'allowed_transitions' => Order::STATUS_TRANSITIONS[$order->status] ?? [],
                        ]
                    );
                }
            }

            $order = $this->orderService->updateOrder($id, $orderData, $order);

            try {
                Order::reorderIds();
            } catch (\Exception $e) {
                \Log::warning('Failed to reorder Order IDs: '.$e->getMessage());
            }

            DB::commit();

            return (new OrderResource($order))->additional([
                'status' => true,
                'message' => 'Order updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return ErrorResource::serverError('Failed to update order', $e->getMessage());
        }
    }

    /**
     * Xóa đơn hàng theo ID
     */
    public function destroy(Request $request, $id)
    {
        $order = $this->orderService->findOrder($id);

        if (! $order) {
            return ErrorResource::notFound('Order not found');
        }

        if (! $request->user()->isAdmin() && ! $this->orderService->userOwnsOrder($order, $request->user()->id)) {
            return ErrorResource::forbidden('Access denied. You can only delete your own orders.');
        }

        $this->orderService->deleteOrderById($id);

        return SuccessResource::deleted('Order deleted successfully');
    }

    /**
     * Thay đổi trạng thái đơn hàng (Chỉ Admin)
     */
    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
        ]);

        DB::beginTransaction();

        try {
            $order = $this->orderService->findOrder($id, false);

            if (! $order) {
                DB::rollback();

                return ErrorResource::notFound('Order not found');
            }

            if (! $this->orderService->canTransitionToStatus($order, $request->status)) {
                DB::rollback();

                return ErrorResource::unprocessableEntity(
                    "Cannot change status from '{$order->status}' to '{$request->status}'. Invalid status transition.",
                    [
                        'current_status' => $order->status,
                        'allowed_transitions' => Order::STATUS_TRANSITIONS[$order->status] ?? [],
                    ]
                );
            }

            $order = $this->orderService->updateOrderStatus($id, $request->status);

            DB::commit();

            return (new OrderResource($order))->additional([
                'status' => true,
                'message' => 'Order status updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return ErrorResource::serverError('Failed to update order status', $e->getMessage());
        }
    }

    /**
     * Lấy tất cả các trạng thái có sẵn
     */
    public function getStatuses(Request $request)
    {
        $statuses = $this->orderService->getAllStatuses();

        return SuccessResource::withData($statuses, 'Statuses retrieved successfully');
    }

    /**
     * Lấy thống kê đơn hàng
     */
    public function stats(Request $request)
    {
        $stats = $this->orderService->getOrderStats(
            $request->user()->id,
            $request->user()->isAdmin()
        );

        return SuccessResource::withData($stats, 'Order statistics retrieved successfully');
    }
}
