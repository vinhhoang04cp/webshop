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
            $orderData = $request->validated(); // orderaData la mot mang chua du lieu da duoc validate, lay tu Request
            $items = $orderData['items']; // $items la mot mang chua cac san pham trong don hang lay tu request,$items lay tu Request
            unset($orderData['items']); // Loai bo items khoi orderData de tranh loi khi tao order

            // Tự động tính tổng tiền từ giá sản phẩm trong database
            $totalAmount = 0; // khoi tao bien totalAmount de tinh tong tien
            foreach ($items as $index => $item) { // lap qua tung item trong mang items
                // Lấy giá sản phẩm từ database
                $product = Product::findOrFail($item['product_id']); // tim kiem san pham trong db bang product_id
                $productPrice = $product->price; // lay gia san pham tu db
                // Tính tổng tiền cho từng sản phẩm
                $totalAmount += $item['quantity'] * $productPrice; // += la phep cong don cac product lai voi nhau

                // Cập nhật giá trong item để lưu vào order_items
                $items[$index]['price'] = $productPrice;
            }

            // Gán tổng tiền đã tính toán vào order data
            $orderData['total_amount'] = $totalAmount; // gan gia tri cua bien totalAmount vao bien $orderData la 1 mang chua du lieu da duoc validate

            // Tạo order với tổng tiền đã được tính tự động
            $order = Order::create($orderData);

            // Tạo order items với giá đã được lấy từ database
            // dung de lap qua tung item trong mang items va tao moi order item
            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'], // [product_id] lay tu request
                    'quantity' => $item['quantity'], // [quantity] lay tu request
                    'price' => $item['price'], // Giá đã được cập nhật từ database
                ]);
            }

            // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
            Order::reorderIds();

            DB::commit(); // commit de luu cac thay doi neu khong co loi xay ra trong transaction

            // Load lại order với relationships
            $order = Order::with('items')->find($order->order_id);

            // Trả về dữ liệu đã chuẩn hoá sử dụng OrderResource
            return (new OrderResource($order))
                ->response()
                ->setStatusCode(201); // Trả về 201 Created với dữ liệu đã chuẩn hoá

        } catch (\Exception $e) { // neu co loi xay ra trong transaction
            DB::rollback(); // rollback de hoan tac lai cac thay doi trong transaction

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
        //
        $order = Order::findOrFail($id);

        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, string $id)
    {
        DB::beginTransaction();

        try {
            $order = Order::findOrFail($id);

            // Lấy dữ liệu đã validate và tách items
            $orderData = $request->validated();
            $items = $orderData['items'] ?? [];
            unset($orderData['items']);

            // Nếu có items mới, tính lại tổng tiền
            if (! empty($items)) {
                $totalAmount = 0;

                // Xóa các order items cũ
                $order->items()->delete();

                // Tạo order items mới với giá từ database
                foreach ($items as $index => $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $productPrice = $product->price;

                    $totalAmount += $item['quantity'] * $productPrice;

                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $productPrice,
                    ]);
                }

                $orderData['total_amount'] = $totalAmount;
            }

            // Cập nhật thông tin order
            $order->update($orderData);

            DB::commit();

            // Load lại order với relationships
            $order = Order::with('items')->find($order->order_id);

            // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
            Order::reorderIds();

            DB::commit();

            // Load lại order với relationships để trả về dữ liệu cập nhật
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
        //
        $order = Order::findOrFail($id);
        if (! $order) {
            return response()->json([
                'status' => false,
                'message' => 'Order not found',
            ], 404);
        }

        $order->delete();

        // Reorder IDs để đảm bảo thứ tự 1, 2, 3, ...
        Order::reorderIds();

        return response()->json([
            'status' => true,
            'message' => 'Order deleted successfully',
        ], 200);
    }
}
