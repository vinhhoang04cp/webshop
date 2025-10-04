<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderItemCollection;
use App\Http\Resources\OrderItemResource;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = OrderItem::query();

        if ($request->has('order_id')) {
            $query->where('order_id', $request->get('order_id'));
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->get('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->get('max_price'));
        }
        if ($request->has('min_quantity')) {
            $query->where('quantity', '>=', $request->get('min_quantity'));
        }
        if ($request->has('max_quantity')) {
            $query->where('quantity', '<=', $request->get('max_quantity'));
        }

        $orderItems = $query->paginate(10); // Paginate results, 10 per page

        return new OrderItemCollection($orderItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $orderItem = OrderItem::create($request->all());
        OrderItem::reorderIds();
        $orderItem->fresh();

        return (new OrderItemResource($orderItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $orderItem = OrderItem::findOrFail($id);

        return new OrderItemResource($orderItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $orderItem = OrderItem::findOrFail($id);
        $orderItem->update($request->all());
        OrderItem::reorderIds();
        $orderItem->fresh();

        return (new OrderItemResource($orderItem))
            ->additional([
                'status' => true,
                'message' => 'Order item updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $orderItem = OrderItem::findOrFail($id);
        $orderItem->delete();
        OrderItem::reorderIds();

        return response()->json([
            'status' => true,
            'message' => 'Order item deleted successfully',
        ], 200);
    }
}
