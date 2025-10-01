<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartCollection;
use App\Http\Requests\CartRequest;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cart::query();
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        
        $carts = $query->paginate(10); // Paginate results, 10 per page
        return new CartCollection($carts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated();
            $items = $cartData['items'];

            // Tạo cart mới cho user
            $cart = Cart::create([
                'user_id' => auth()->id() ?? $request->user_id ?? 1, // Lấy user_id từ auth hoặc request
            ]);

            // Tạo cart items với giá lấy từ database
            foreach ($items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                $cart->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            DB::commit();

            // Load lại cart với relationships
            $cart = Cart::with('items.product')->find($cart->cart_id);

            // Trả về dữ liệu đã chuẩn hoá sử dụng CartResource
            return (new CartResource($cart))
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to create cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, $id)
    {
        //
        \DB::beginTransaction(); //
        try{
            $cartData = $request->validated();
            $items = $cartData['items'];
            unset($cartData['items']);

            $totalAmount = 0;
            foreach($items as $index => $item){
                $product = Product::find($item['product_id']);
                $productPrice = $product->price;
                $totalAmount += $productPrice * $item['quantity'];
                $items[$index]['price'] = $productPrice;
            }
            $cartData['total_amount'] = $totalAmount; // $

            // Tạo order với tổng tiền đã được tính tự động
            $cart = Cart::create($cartData);

            // Tạo order items với giá đã được lấy từ database
            // dung de lap qua tung item trong mang items va tao moi order item
            foreach ($items as $item) {
                $cart->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'], // Giá đã được cập nhật từ database
                ]);
            }

            DB::commit(); // commit de luu cac thay doi neu khong co loi xay ra trong transaction

            // Load lại order với relationships
            $order = Order::with('items')->find($order->order_id);

            // Trả về dữ liệu đã chuẩn hoá sử dụng OrderResource
            return (new OrderResource($order))
                ->response()
                ->setStatusCode(201); // Trả về 201 Created với dữ liệu đã chuẩn hoá
        } catch (Exception $e) {
        DB::rollback(); // rollback de hoan tac lai cac thay doi trong transaction

            return response()->json([
                'status' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage(),
            ], 500);
        }
        \DB::reorderIds(); // Goi ham reorderIds de sap xep lai ID sau khi tao moi

        return (new CartResource($cart))
            ->response()
            ->setStatusCode(201); // Trả về 201 Created với dữ liệu đã chuẩn hoá   
        }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
