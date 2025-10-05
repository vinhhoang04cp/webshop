<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Models\Product;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cart::with('items.product');
        
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        
        $carts = $query->paginate(10);
        $cartsData = [];
        $grandTotal = 0;

        foreach ($carts as $cart) {
            $cartTotals = $this->calculateCartTotals($cart);
            
            $cartData = new CartResource($cart);
            $cartData->additional([
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
            ]);

            $cartsData[] = [
                'cart' => $cartData,
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
            ];

            $grandTotal += $cartTotals['amount'];
        }

        return response()->json([
            'status' => true,
            'message' => 'Carts retrieved successfully',
            'data' => $cartsData,
            'pagination' => [
                'current_page' => $carts->currentPage(),
                'per_page' => $carts->perPage(),
                'total' => $carts->total(),
                'last_page' => $carts->lastPage(),
            ],
            'grand_total' => $grandTotal,
            'total_carts' => $carts->total(),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartRequest $request)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated(); // Tao bien cartData de luu du lieu tu request
            $userId = $this->getUserId($request); // Tao bien userId de luu id nguoi dung, $this->getUserId() se lay id tu auth hoac request hoac mac dinh la 1
            $cart = $this->findOrCreateCart($cartData, $userId); //bien $cart de luu cart tim thay hoac tao moi
            $itemsToAdd = $this->prepareItemsData($cartData); // bien $itemsToAdd de luu cac san pham can them vao cart, ham prepareItemsData se chuan hoa du lieu tu cartData

            foreach ($itemsToAdd as $item) { // lap qua tung item trong itemsToAdd, item la mot mang chua product_id va quantity
                $this->addOrUpdateCartItem($cart, $item); // $this la doi tuong hien tai, goi ham addOrUpdateCartItem de them hoac cap nhat item trong cart
            }

            DB::commit(); // Neu khong co loi xay ra thi commit

            $cart = Cart::with('items.product')->find($cart->cart_id); // Tai lai cart de lay du lieu moi nhat, ham with de load quan he items va product
            $cartTotals = $this->calculateCartTotals($cart); // Tinh tong tien va so luong san pham trong cart

            return response()->json([
                'status' => true,
                'message' => 'Items added to cart successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
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
            $cart = Cart::findOrFail($id);
            $userId = $this->getUserId($request);
            
            if ($cart->user_id !== $userId) {
                throw new Exception('Unauthorized to update this cart');
            }

            $itemsToUpdate = $this->prepareItemsData($cartData);

            foreach ($itemsToUpdate as $item) {
                $this->updateCartItem($cart, $item);
            }

            DB::commit();

            $cart = Cart::with('items.product')->find($cart->cart_id);
            $cartTotals = $this->calculateCartTotals($cart);

            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
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
    public function destroy($id)
    {
        $cart = Cart::findOrFail($id);
        $userId = auth()->id() ?? 1;
        
        if ($cart->user_id !== $userId) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized to delete this cart',
            ], 403);
        }

        $cart->delete();

        try {
            Cart::reOrderIds();
        } catch (\Exception $e) {
            \Log::warning('Failed to reorder Cart IDs after delete: ' . $e->getMessage());
        }

        return response()->json([
            'status' => true,
            'message' => 'Cart deleted successfully',
        ], 200);
    }

    /**
     * Calculate cart totals (amount and items count)
     */
    private function calculateCartTotals($cart) // ham tinh tong tien va so luong san pham trong cart voi tham so la doi tuong cart
    {
        $totalAmount = 0; // khoi tao bien totalAmount de luu tong tien
        $totalItems = 0; // khoi tao bien totalItems de luu so luong san pham

        foreach ($cart->items as $cartItem) { // lap qua tung cartItem trong $cart->items
            $totalAmount += $cartItem->product->price * $cartItem->quantity; // cong don cart item hien tai vao totalAmount
            $totalItems += $cartItem->quantity; // cong don so luong cart item hien tai vao totalItems
        }

        return [
            'amount' => $totalAmount,
            'items' => $totalItems,
        ];
    }

    /**
     * Get user ID from auth or request
     */
    private function getUserId($request)
    {
        return auth()->id() ?? $request->user_id ?? 1;
    }

    /**
     * Find existing cart or create new one
     */
    private function findOrCreateCart($cartData, $userId)
    {
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

        if (!$cart) {
            $cart = Cart::create(['user_id' => $userId]);
        }

        return $cart;
    }

    /**
     * Prepare items data from request
     */
    private function prepareItemsData($cartData)
    {
        if (isset($cartData['items']) && is_array($cartData['items'])) {
            return $cartData['items'];
        }
        
        if (isset($cartData['product_id']) && isset($cartData['quantity'])) {
            return [[
                'product_id' => $cartData['product_id'],
                'quantity' => $cartData['quantity'],
            ]];
        }

        return [];
    }

    /**
     * Add or update cart item (for store method)
     */
    private function addOrUpdateCartItem($cart, $item)
    {
        $product = Product::findOrFail($item['product_id']);
        $cartItem = $cart->items()->where('product_id', $item['product_id'])->first();

        if ($cartItem) {
            $cartItem->quantity += $item['quantity'];
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        Log::info("Added/Updated product ID {$item['product_id']} in cart ID {$cart->cart_id}");
    }

    /**
     * Update cart item (for update method)
     */
    private function updateCartItem($cart, $item)
    {
        $product = Product::findOrFail($item['product_id']);
        $cartItem = $cart->items()->where('product_id', $item['product_id'])->first();

        if ($cartItem) {
            $cartItem->quantity = $item['quantity'];
            $cartItem->save();
        } else {
            $cart->items()->create([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }
    }
}
