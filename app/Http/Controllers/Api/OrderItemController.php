<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderItemRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\OrderItemCollection;
use App\Http\Resources\OrderItemResource;
use App\Http\Resources\SuccessResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderItemController extends Controller
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
        $filters = $request->only(['order_id', 'product_id', 'min_price', 'max_price', 'min_quantity', 'max_quantity']);
        $perPage = $request->input('per_page', 15);
        $orderItems = $this->orderService->getOrderItems($filters, $perPage);

        return new OrderItemCollection($orderItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderItemRequest $request)
    {
        $orderItem = $this->orderService->createOrderItem($request->validated());

        return (new OrderItemResource($orderItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $orderItem = $this->orderService->findOrderItem($id, true);

        if (! $orderItem) {
            return ErrorResource::notFound('Order item not found');
        }

        return (new OrderItemResource($orderItem))->additional([
            'status' => true,
            'message' => 'Order item retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderItemRequest $request, string $id)
    {
        $orderItem = $this->orderService->findOrderItem($id, false);

        if (! $orderItem) {
            return ErrorResource::notFound('Order item not found');
        }

        $orderItem = $this->orderService->updateOrderItem($id, $request->validated());

        return (new OrderItemResource($orderItem))->additional([
            'status' => true,
            'message' => 'Order item updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $orderItem = $this->orderService->findOrderItem($id, false);

        if (! $orderItem) {
            return ErrorResource::notFound('Order item not found');
        }

        $this->orderService->deleteOrderItem($id);

        return SuccessResource::deleted('Order item deleted successfully');
    }
}
