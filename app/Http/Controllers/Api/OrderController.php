<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
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
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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

            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        $order = $this->orderService->findOrder($id, true);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (! $request->user()->isAdmin() && ! $this->orderService->userOwnsOrder($order, $request->user()->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. You can only access your own orders.',
            ], 403);
        }

        return (new OrderResource($order))->additional([
            'status' => true,
            'message' => 'Order retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $order = $this->orderService->findOrder($id);

            if (! $order) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if (! $request->user()->isAdmin() && ! $this->orderService->userOwnsOrder($order, $request->user()->id)) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. You can only update your own orders.',
                ], 403);
            }

            $orderData = $request->validated();

            if (isset($orderData['status']) && $orderData['status'] !== $order->status) {
                if (! $request->user()->isAdmin()) {
                    DB::rollback();

                    return response()->json([
                        'status' => false,
                        'message' => 'Only admin can change order status.',
                    ], 403);
                }

                if (! $this->orderService->canTransitionToStatus($order, $orderData['status'])) {
                    DB::rollback();

                    return response()->json([
                        'status' => false,
                        'message' => "Cannot change status from '{$order->status}' to '{$orderData['status']}'. Invalid status transition.",
                        'current_status' => $order->status,
                        'allowed_transitions' => Order::STATUS_TRANSITIONS[$order->status] ?? [],
                    ], 422);
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

            return response()->json([
                'status' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $order = $this->orderService->findOrder($id);

        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        if (! $request->user()->isAdmin() && ! $this->orderService->userOwnsOrder($order, $request->user()->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. You can only delete your own orders.',
            ], 403);
        }

        $this->orderService->deleteOrderById($id);

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully',
        ], 200);
    }

    /**
     * Change order status (Admin only)
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

                return response()->json([
                    'status' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            if (! $this->orderService->canTransitionToStatus($order, $request->status)) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => "Cannot change status from '{$order->status}' to '{$request->status}'. Invalid status transition.",
                    'current_status' => $order->status,
                    'allowed_transitions' => Order::STATUS_TRANSITIONS[$order->status] ?? [],
                ], 422);
            }

            $order = $this->orderService->updateOrderStatus($id, $request->status);

            DB::commit();

            return (new OrderResource($order))->additional([
                'status' => true,
                'message' => 'Order status updated successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update order status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all available statuses
     */
    public function getStatuses(Request $request)
    {
        $statuses = $this->orderService->getAllStatuses();

        return response()->json([
            'status' => true,
            'message' => 'Statuses retrieved successfully',
            'data' => $statuses,
        ], 200);
    }

    /**
     * Get order statistics
     */
    public function stats(Request $request)
    {
        $stats = $this->orderService->getOrderStats(
            $request->user()->id,
            $request->user()->isAdmin()
        );

        return response()->json([
            'status' => true,
            'message' => 'Order statistics retrieved successfully',
            'data' => $stats,
        ], 200);
    }
}
