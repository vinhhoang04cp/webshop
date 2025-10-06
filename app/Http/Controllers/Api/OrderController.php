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
    public function index(Request $request) // (Request $request) la tham so truyen tu client qua URL den controller
    {
        $query = Order::query(); // $query la mot truy van Eloquent khoi tao de lay du lieu tu bang orders

        $this->applyFilters($query, $request); // $this la doi tuong hien tai cua lop, applyFilters la phuong thuc rieng cua lop OrderController de ap dung cac bo loc tren truy van $query dua tren cac tham so trong $request

        $orders = $query->paginate(10); // paginate(10) phan trang ket qua, moi trang co toi da 10 ban ghi, $orders la ket qua tra ve sau khi ap dung cac bo loc

        return new OrderCollection($orders); // tra ve mot OrderCollection chua cac don hang da duoc phan trang
    }

    /**
     * Ham luu don hang moi vao co so du lieu, tra ve OrderResource cho don hang vua tao
     */
    public function store(OrderRequest $request)
    {
        DB::beginTransaction(); // DB::beginTransaction() bat dau mot giao dich co so du lieu , neu co loi xay ra trong qua trinh thuc hien cac thao tac tren co so du lieu, ta co the su dung DB::rollback() de hoan tac lai tat ca cac thay doi da thuc hien trong giao dich, neu tat ca cac thao tac thanh cong, ta su dung DB::commit() de luu cac thay doi vao co so du lieu

        try {
            $orderData = $request->validated(); // bien $orderData de luu du lieu tu request
            $items = $orderData['items']; // $items la mang chua cac san pham trong don hang
            unset($orderData['items']); // ham uset de xoa phan items khoi orderData, vi ta se xu ly rieng phan items

            // Kiểm tra stock trước khi tạo order
            $stockValidation = $this->validateStock($items);
            if (!$stockValidation['valid']) {
                DB::rollback();
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient stock',
                    'errors' => $stockValidation['errors'],
                ], 422);
            }

            $itemsWithPrices = $this->calculateItemPrices($items); // $itemsWithPrices la mang chua cac san pham trong don hang, voi gia duoc lay tu database
            // $this la doi tuong hien tai, lam viec voi lop hien tai
            $orderData['total_amount'] = $this->calculateTotalAmount($itemsWithPrices); // $orderData['total_amount'] de luu tong so tien cua don hang, duoc tinh tu ham calculateTotalAmount
            $order = Order::create($orderData); // $order la bien luu don hang moi duoc tao
            $this->createOrderItems($order, $itemsWithPrices); // ham createOrderItems duoc truyen cac tham so la $order va $itemsWithPrices

            // Cập nhật stock sau khi tạo order thành công
            $this->updateStock($items);

            // Bỏ reorderIds khỏi transaction để tránh deadlock
            DB::commit(); // Neu khong co loi xay ra thi commit

            // Chạy reorderIds ngoài transaction
            Order::reorderIds(); // Goi phuong thuc static reorderIds() de sap xep lai order_id lien tuc tu 1,2,3,...

            $order = Order::with('items')->find($order->order_id); // with la ham de load quan he items, find() de tim don hang vua tao voi tham so order_id

            return (new OrderResource($order))->response()->setStatusCode(201); // Tra ve OrderResource cho don hang vua tao

        } catch (\Exception $e) { // neu co loi xay ra trong khoi try, se bat loi va thuc hien cac cau lenh trong khoi catch
            DB::rollback(); // neu co loi xay ra thi rollback de hoan tac lai tat ca cac thay doi da thuc hien trong giao dich

            return response()->json([ // tra ve mot response dang json
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id) // ham show() voi tham so $id la order_id cua don hang can lay
    {
        $order = Order::with('items')->findOrFail($id); // with de load quan he items, findOrFail de tim don hang voi order_id bang $id, neu khong tim thay se tra ve loi 404

        return new OrderResource($order); // tra ve mot OrderResource chua don hang
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(OrderRequest $request, string $id)
    {
        DB::beginTransaction(); // Bat dau mot giao dich co so du lieu

        try {
            $order = Order::findOrFail($id); // Tim don hang can cap nhat bang order_id, neu khong tim thay se tra ve loi 404
            $orderData = $request->validated(); // $orderData la bien de luu du lieu tu request dau vao da duoc xac thuc
            $items = $orderData['items'] ?? []; // $items la bien de luu cac san pham trong don hang, neu khong co thi la mang rong
            unset($orderData['items']); // ham uset de xoa phan items khoi orderData, vi ta se xu ly rieng phan items

            if (! empty($items)) { // (empty($items)) kiem tra xem mang items co rong hay khong, neu khong rong thi thuc hien cac cau lenh trong khoi if
                $order->items()->delete(); // $order->items() la quan he items cua don hang, goi phuong thuc delete() de xoa tat ca cac item hien co trong don hang
                $itemsWithPrices = $this->calculateItemPrices($items); // $itemsWithPrices la mang chua cac san pham trong don hang, calculateItemPrices() lay tham so la mang items, tra ve mang items voi gia duoc lay tu database
                // $this la doi tuong hien tai, lam viec voi lop hien tai
                $orderData['total_amount'] = $this->calculateTotalAmount($itemsWithPrices); // $orderData['total_amount'] de luu tong so tien cua don hang, duoc tinh tu ham calculateTotalAmount
                $this->createOrderItems($order, $itemsWithPrices); // ham createOrderItems duoc truyen cac tham so la $order va $itemsWithPrices
            }

            $order->update($orderData); // cap nhat don hang voi du lieu moi trong orderData
            Order::reorderIds(); // Goi phuong thuc static reorderIds() de sap xep lai order_id lien tuc tu 1,2,3,...
            DB::commit(); // Neu khong co loi xay ra thi commit

            $order = Order::with('items')->find($order->order_id); // with de load quan he items, find() de tim don hang voi order_id

            return new OrderResource($order); // tra ve mot OrderResource chua don hang vua cap nhat

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
     * Ham tinh gia cho tung item trong mang items, tra ve mang items voi gia duoc lay tu database
     */
    private function calculateItemPrices($items) // Tham so $items la mot mang chua cac san pham trong don hang, moi san pham la mot mang con chua product_id va quantity
    {
        foreach ($items as $index => $item) { // lap qua tung item trong mang items, $index la chi so cua item trong mang, $item la phan tu hien tai trong mang
            $product = Product::findOrFail($item['product_id']); // tim san pham trong database voi product_id tu item, neu khong tim thay se tra ve loi 404
            $items[$index]['price'] = $product->price; // gan gia cua san pham vao phan price trong item
        }

        return $items; // Tra ve mang items voi gia duoc lay tu database
    }

    /**
     * ham tinh tong so tien cua don hang, voi tham so la mang items, tra ve tong so tien
     */
    private function calculateTotalAmount($items)
    {
        $totalAmount = 0; // khoi tao bien totalAmount de luu tong so tien
        foreach ($items as $item) { // lap qua tung item trong mang items
            $totalAmount += $item['quantity'] * $item['price']; // so luong * gia cua tung don hang va cong don vao totalAmount
        }

        return $totalAmount; // tra ve tong so tien
    }

    /**
     * Ham tao cac item cho don hang, voi tham so la doi tuong order va mang items
     */
    private function createOrderItems($order, $items) // tham so $order la doi tuong don hang vua tao, $items la mang chua cac san pham trong don hang
    {
        foreach ($items as $item) { // lap qua tung item trong mang items
            $order->items()->create([ // $order->items() la quan he items cua don hang, goi phuong thuc create() de tao moi mot item trong don hang
                'product_id' => $item['product_id'], // gan product_id tu item vao phan product_id trong order item
                'quantity' => $item['quantity'], // gan quantity tu item vao phan quantity trong order item
                'price' => $item['price'], // gan price tu item vao phan price trong order item, $item['price'] da duoc tinh toan trong ham calculateItemPrices
            ]);
        }
    }

    /**
     * Kiểm tra stock có đủ không trước khi tạo order
     */
    private function validateStock($items)
    {
        $errors = [];
        $valid = true;

        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (!$product) {
                $errors[] = "Product with ID {$item['product_id']} not found";
                $valid = false;
                continue;
            }

            if ($product->stock_quantity < $item['quantity']) {
                $errors[] = "Insufficient stock for product '{$product->name}'. Available: {$product->stock_quantity}, Requested: {$item['quantity']}";
                $valid = false;
            }
        }

        return ['valid' => $valid, 'errors' => $errors];
    }

    /**
     * Cập nhật stock sau khi tạo order thành công
     */
    private function updateStock($items)
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->decrement('stock_quantity', $item['quantity']);
            }
        }
    }
}
