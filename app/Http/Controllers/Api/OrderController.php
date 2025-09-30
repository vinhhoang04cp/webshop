<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Resources\OrderCollection;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Order::query();
        if ($request->has('status')) {  // has('status') truyen tham so status tu request
            $query->where('status', $request->get('status'));
        }
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

        $orders = $query->get();
        $orders = $query->paginate(10); // Paginate results, 10 per page

        return new OrderCollection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        // Tạo transaction để đảm bảo data integrity
        DB::beginTransaction(); // transaction thuc hien cac thao tac tren db
        
        try {
            // Lấy dữ liệu đã validate và tách items
            $orderData = $request->validated();
            $items = $orderData['items']; // $items la mot mang chua cac san pham trong don hang lay tu request, bao gom product_id, quantity, va price cho moi san pham.
            unset($orderData['items']); // Loai bo items khoi orderData de tranh loi khi tao order
            
            // Tự động tính tổng tiền từ các items
            $totalAmount = 0;
            foreach ($items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }
            
            // Gán tổng tiền đã tính toán vào order data
            $orderData['total_amount'] = $totalAmount;
            
            // Tạo order với tổng tiền đã được tính tự động
            $order = Order::create($orderData);
            
            // Tạo order items
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);
            }

            Order::reorderIds();
            
            DB::commit(); //commit de luu cac thay doi neu khong co loi xay ra trong transaction
            
            $order = $order->fresh(); // Tải lại để lấy dữ liệu mới nhất
            return (new OrderResource($order))
                ->response()
                ->setStatusCode(201); // Trả về 201 Created với dữ liệu đã chuẩn hoá
                
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $order = Order::findOrFail($id);

        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
