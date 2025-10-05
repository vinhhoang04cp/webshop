<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::query();
        
        $this->applyFilters($query, $request);
        
        $orders = $query->paginate(10);
        
        return new OrderCollection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        DB::beginTransaction();
        
        try {
            $orderData = $request->validated();
            $items = $orderData['items'];
            unset($orderData['items']);

            $itemsWithPrices = $this->calculateItemPrices($items); // 
            $orderData['total_amount'] = $this->calculateTotalAmount($itemsWithPrices);

            $order = Order::create($orderData);
            $this->createOrderItems($order, $itemsWithPrices);
            
            Order::reorderIds();
            DB::commit();

            $order = Order::with('items')->find($order->order_id);
            
            return (new OrderResource($order))->response()->setStatusCode(201);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::with('items')->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, string $id)
    {
        DB::beginTransaction();
        
        try {
            $order = Order::findOrFail($id);
            $orderData = $request->validated();
            $items = $orderData['items'] ?? [];
            unset($orderData['items']);

            if (!empty($items)) {
                $order->items()->delete();
                $itemsWithPrices = $this->calculateItemPrices($items);
                $orderData['total_amount'] = $this->calculateTotalAmount($itemsWithPrices);
                $this->createOrderItems($order, $itemsWithPrices);
            }

            $order->update($orderData);
            Order::reorderIds();
            DB::commit();

            $order = Order::with('items')->find($order->order_id);
            return new OrderResource($order);
            
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
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        
        Order::reorderIds();
        
        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully',
        ], 200);
    }

    /**
     * Apply filters to the query based on request parameters
     */
    private function applyFilters($query, $request)
    {
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->has('min_date')) {
            $query->where('order_date', '>=', $request->get('min_date'));
        }
        if ($request->has('max_date')) {
            $query->where('order_date', '<=', $request->get('max_date'));
        }
        if ($request->has('min_total')) {
            $query->where('total_amount', '>=', $request->get('min_total'));
        }
        if ($request->has('max_total')) {
            $query->where('total_amount', '<=', $request->get('max_total'));
        }
    }

    /**
     * Calculate item prices from database and return items with prices
     */
    private function calculateItemPrices($items)
    {
        foreach ($items as $index => $item) {
            $product = Product::findOrFail($item['product_id']);
            $items[$index]['price'] = $product->price;
        }
        return $items;
    }

    /**
     * Calculate total amount from items with prices
     */
    private function calculateTotalAmount($items)
    {
        $totalAmount = 0;
        foreach ($items as $item) {
            $totalAmount += $item['quantity'] * $item['price'];
        }
        return $totalAmount;
    }

    /**
     * Create order items for the given order
     */
    private function createOrderItems($order, $items)
    {
        foreach ($items as $item) {
            $order->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
    }
}
