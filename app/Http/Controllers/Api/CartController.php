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
            
            // Xác định user_id
            $userId = auth()->id() ?? $request->user_id ?? 1;

            // Kiểm tra xem user đã có cart chưa hoặc sử dụng cart_id nếu được cung cấp
            if (isset($cartData['cart_id'])) {
                $cart = Cart::where('cart_id', $cartData['cart_id'])
                    ->where('user_id', $userId)
                    ->first();
                    
                if (!$cart) {
                    throw new Exception("Cart with ID {$cartData['cart_id']} not found or does not belong to user");
                }
            } else {
                $cart = Cart::where('user_id', $userId)->first();
            }
            
            // Nếu chưa có cart thì tạo mới
            if (!$cart) {
                $cart = Cart::create([
                    'user_id' => $userId,
                ]);
            }

            $totalAmount = 0;
            $itemsToAdd = [];

            // Xử lý items array nếu có
            if (isset($cartData['items']) && is_array($cartData['items'])) {
                $itemsToAdd = $cartData['items'];
            } 
            // Xử lý single item nếu có product_id và quantity
            elseif (isset($cartData['product_id']) && isset($cartData['quantity'])) {
                $itemsToAdd[] = [
                    'product_id' => $cartData['product_id'],
                    'quantity' => $cartData['quantity'],
                ];
            }

            // Thêm hoặc cập nhật cart items
            foreach ($itemsToAdd as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                // Kiểm tra xem sản phẩm đã có trong cart chưa
                $cartItem = $cart->items()->where('product_id', $item['product_id'])->first();
                
                if ($cartItem) {
                    // Nếu đã có thì cập nhật số lượng (cộng dồn)
                    $cartItem->quantity += $item['quantity'];
                    $cartItem->save();
                } else {
                    // Nếu chưa có thì tạo mới
                    $cart->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }

                // Tính tổng tiền: giá sản phẩm * số lượng
                $itemTotal = $product->price * $item['quantity'];
                $totalAmount += $itemTotal;
            }

            DB::commit();

            // Load lại cart với relationships
            $cart = Cart::with('items.product')->find($cart->cart_id);

            // Tính tổng tiền của toàn bộ cart (bao gồm cả items cũ)
            $cartTotalAmount = 0;
            foreach ($cart->items as $cartItem) {
                $cartTotalAmount += $cartItem->product->price * $cartItem->quantity;
            }

            // Trả về dữ liệu đã chuẩn hoá sử dụng CartResource với thông tin tổng tiền
            return response()->json([
                'status' => true,
                'message' => 'Items added to cart successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotalAmount,
                'total_items' => $cart->items->sum('quantity'),
                'items_added' => count($itemsToAdd),
            ], 201);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to add items to cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated();
            
            // Tìm cart theo ID
            $cart = Cart::find($id);
            
            if (!$cart) {
                throw new Exception("Cart with ID {$id} not found");
            }
            
            // Kiểm tra quyền sở hữu cart
            $userId = auth()->id() ?? $request->user_id ?? 1;
            if ($cart->user_id !== $userId) {
                throw new Exception("Unauthorized to update this cart");
            }

            $itemsToUpdate = [];

            // Xử lý items array nếu có
            if (isset($cartData['items']) && is_array($cartData['items'])) {
                $itemsToUpdate = $cartData['items'];
            } 
            // Xử lý single item nếu có product_id và quantity
            elseif (isset($cartData['product_id']) && isset($cartData['quantity'])) {
                $itemsToUpdate[] = [
                    'product_id' => $cartData['product_id'],
                    'quantity' => $cartData['quantity'],
                ];
            }

            // Cập nhật cart items
            foreach ($itemsToUpdate as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    throw new Exception("Product with ID {$item['product_id']} not found");
                }

                // Tìm cart item
                $cartItem = $cart->items()->where('product_id', $item['product_id'])->first();
                
                if ($cartItem) {
                    // Cập nhật số lượng (ghi đè, không cộng dồn)
                    $cartItem->quantity = $item['quantity'];
                    $cartItem->save();
                } else {
                    // Nếu chưa có thì tạo mới
                    $cart->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            DB::commit();

            // Load lại cart với relationships
            $cart = Cart::with('items.product')->find($cart->cart_id);

            // Tính tổng tiền của toàn bộ cart
            $cartTotalAmount = 0;
            foreach ($cart->items as $cartItem) {
                $cartTotalAmount += $cartItem->product->price * $cartItem->quantity;
            }

            // Trả về dữ liệu đã chuẩn hoá sử dụng CartResource
            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotalAmount,
                'total_items' => $cart->items->sum('quantity'),
            ], 200);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
