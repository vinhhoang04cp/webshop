<?php

namespace App\Services;

use App\Contracts\CartServiceInterface;
use App\Exceptions\Cart\CartNotFoundException;
use App\Exceptions\Cart\EmptyCartException;
use App\Exceptions\Cart\UnauthorizedCartAccessException;
use App\Exceptions\Coupon\CouponNotFoundException;
use App\Exceptions\Product\InsufficientStockException;
use App\Exceptions\Product\ProductNotFoundException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService implements CartServiceInterface
{
    /**
     * Xử lý checkout đơn hàng (dùng chung cho Web và API)
     *
     * @param  array  $data  Dữ liệu checkout (shipping info, payment method, coupon)
     * @param  int|null  $userId  User ID (nếu null, dùng Auth::id())
     * @return array ['success', 'order', 'discount_amount', 'payment_method']
     */
    public function processCheckout(array $data, $userId = null)
    // tham so truyen vao la mang du lieu checkout va userId co the la null
    {
        // Lấy cart của user (hỗ trợ cả Web session và API token)
        $userId = $userId ?? Auth::id(); // userId neu null thi lay tu Auth
        $cart = Cart::where('user_id', $userId)->first(); // lay cart theo userId

        // Kiểm tra giỏ hàng có tồn tại và có sản phẩm

        if (! $cart || $cart->items()->count() == 0) {
            throw new EmptyCartException(); // neu khong ton tai hoac khong co san pham thi nem loi EmptyCartException
        }

        DB::beginTransaction(); // bat dau giao dich database
        try {
            // Kiểm tra tồn kho
            $this->validateStock($cart); // goi den ham validateStock de kiem tra ton kho

            $totalAmount = $cart->totalPrice(); // tinh tong tien trong cart
            $discountAmount = 0; // khoi tao bien giam gia bang 0
            $coupon = null; // khoi tao bien coupon bang null

            // Xử lý coupon
            if (! empty($data['coupon_code'])) { // neu co coupon_code trong du lieu checkout
                $result = $this->applyCoupon($data['coupon_code'], $totalAmount);
                // goi den ham applyCoupon de ap dung coupon
                $coupon = $result['coupon'];
                $discountAmount = $result['discount'];
                $totalAmount = $result['total'];
            }

            // Tạo đơn hàng
            $order = $this->createOrder($data, $totalAmount, $userId);

            // Tăng số lần sử dụng coupon
            if ($coupon) {
                $coupon->increment('used_count');
                $coupon->save();
            }

            // Tạo order items và trừ tồn kho
            $this->createOrderItems($cart, $order);

            // Xóa giỏ hàng
            $cart->items()->delete();

            DB::commit();

            return [
                'success' => true,
                'order' => $order->fresh(['items']), // Reload với items
                'discount_amount' => $discountAmount,
                'payment_method' => $data['payment_method'] ?? 'cod',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Kiểm tra tồn kho
     */
    protected function validateStock($cart)
    {
        foreach ($cart->items as $item) {
            $product = $item->product;
            if (! $product) {
                throw new ProductNotFoundException($item->product_id);
            }

            if ($product->stock_quantity < $item->quantity) {
                throw new InsufficientStockException($product->name, $product->stock_quantity, $item->quantity);
            }
        }
    }

    /**
     * Áp dụng mã giảm giá
     */
    protected function applyCoupon($couponCode, $totalAmount)
    {
        $couponCode = strtoupper(trim($couponCode));
        $coupon = Coupon::where('code', $couponCode)->first();

        if (! $coupon) {
            throw new CouponNotFoundException($couponCode);
        }

        // ✅ Use CouponService with exception-based validation
        $couponService = app(\App\Services\CouponService::class);

        // This will throw specific exceptions if invalid
        $couponService->validateCoupon($coupon, $totalAmount);

        $discountAmount = $couponService->calculateDiscount($coupon, $totalAmount);
        $totalAmount = $totalAmount - $discountAmount;

        return [
            'coupon' => $coupon,
            'discount' => $discountAmount,
            'total' => $totalAmount,
        ];
    }

    /**
     * Tạo đơn hàng
     */
    protected function createOrder(array $data, $totalAmount, $userId = null)
    {
        $order = new Order();
        $order->user_id = $userId ?? Auth::id();
        $order->total_amount = $totalAmount;
        $order->status = 'pending';
        $order->order_date = now();
        $order->shipping_name = $data['shipping_name'];
        $order->shipping_phone = $data['shipping_phone'];
        $order->shipping_address = $data['shipping_address'];
        $order->note = $data['note'] ?? null;
        $order->save();

        return $order;
    }

    /**
     * Tạo order items và cập nhật tồn kho
     */
    protected function createOrderItems($cart, $order)
    {
        foreach ($cart->items as $item) {
            $product = $item->product;

            // Tạo order item
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->order_id;
            $orderItem->product_id = $item->product_id;
            $orderItem->quantity = $item->quantity;
            $orderItem->price = $item->price ?? $product->price;
            $orderItem->save();

            // Trừ tồn kho
            $product->decrement('stock_quantity', $item->quantity);

            // Cập nhật inventory
            $this->updateInventory($product, $item->quantity);
        }
    }

    /**
     * Cập nhật inventory
     */
    protected function updateInventory($product, $quantity)
    {
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $product->product_id],
            [
                'stock_in' => 0,
                'stock_out' => 0,
                'current_stock' => 0,
            ]
        );
        $inventory->increment('stock_out', $quantity);
        $inventory->decrement('current_stock', $quantity);
        $inventory->save();
    }

    /**
     * Lấy hoặc tạo giỏ hàng (với relationships cho API)
     */
    public function getOrCreateCart()
    {
        $cart = Auth::user()->cart;

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
            ]);
        }

        // Load relationships cho API Resource
        $cart->load(['items.product.category']);

        return $cart;
    }

    /**
     * Lấy danh sách items trong giỏ hàng
     */
    public function getCartItems($cart)
    {
        try {
            return $cart->items()
                ->with(['product' => function ($query) {
                    $query->with('category');
                }])
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error loading cart items: '.$e->getMessage());

            return collect();
        }
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function addToCart($productId, $quantity = 1)
    {
        DB::beginTransaction();
        try {
            $product = Product::findOrFail($productId);

            $cart = $this->getOrCreateCart();

            $cartItem = CartItem::where('cart_id', $cart->cart_id)
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->cart_id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            DB::commit();

            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     */
    public function updateCartItem($cartItemId, $quantity)
    {
        DB::beginTransaction();
        try {
            $cartItem = CartItem::findOrFail($cartItemId);

            if ($cartItem->cart->user_id != Auth::id()) {
                throw new UnauthorizedCartAccessException();
            }

            $cartItem->quantity = $quantity;
            $cartItem->save();

            DB::commit();

            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     */
    public function removeFromCart($cartItemId)
    {
        DB::beginTransaction();
        try {
            $cartItem = CartItem::findOrFail($cartItemId);

            if ($cartItem->cart->user_id != Auth::id()) {
                throw new UnauthorizedCartAccessException();
            }

            $cartItem->delete();

            DB::commit();

            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clearCart()
    {
        $cart = Auth::user()->cart;
        if ($cart) {
            $cart->items()->delete();
        }

        return ['success' => true];
    }

    /**
     * Get carts with filters (for API)
     */
    public function getCarts($userId = null, $isAdmin = false, $filters = [])
    {
        $query = Cart::with(['items.product.category']);

        if (! $isAdmin) {
            $query->where('user_id', $userId);
        } elseif (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['product_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        return $query->paginate(10);
    }

    /**
     * Calculate cart totals
     */
    public function calculateCartTotals($cart)
    {
        $totalAmount = 0;
        $totalItems = 0;

        foreach ($cart->items as $cartItem) {
            $totalAmount += $cartItem->product->price * $cartItem->quantity;
            $totalItems += $cartItem->quantity;
        }

        return [
            'amount' => $totalAmount,
            'items' => $totalItems,
        ];
    }

    /**
     * Find or create cart for a specific user
     */
    public function findOrCreateCartForUser($cartId, $userId)
    {
        if ($cartId) {
            $cart = Cart::where('cart_id', $cartId)
                ->where('user_id', $userId)
                ->first();

            if (! $cart) {
                throw new CartNotFoundException($cartId);
            }
        } else {
            $cart = Cart::where('user_id', $userId)->first();
        }

        if (! $cart) {
            $cart = Cart::create(['user_id' => $userId]);
        }

        return $cart;
    }

    /**
     * Add multiple items to cart
     */
    public function addItemsToCart($cart, array $items)
    {
        foreach ($items as $item) {
            $this->addSingleItemToCart($cart, $item);
        }

        return $cart->fresh(['items.product']);
    }

    /**
     * Add single item to cart
     */
    protected function addSingleItemToCart($cart, array $item)
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
                'price' => $product->price,
            ]);
        }
    }

    /**
     * Update multiple items in cart
     */
    public function updateCartItems($cart, array $items)
    {
        foreach ($items as $item) {
            $this->updateSingleCartItem($cart, $item);
        }

        return $cart->fresh(['items.product']);
    }

    /**
     * Update single cart item
     */
    protected function updateSingleCartItem($cart, array $item)
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
                'price' => $product->price,
            ]);
        }
    }

    /**
     * Get cart by ID (with full relationships)
     */
    public function getCartById($cartId)
    {
        return Cart::with(['items.product.category'])->findOrFail($cartId);
    }

    /**
     * Check if user owns cart
     */
    public function userOwnsCart($cart, $userId)
    {
        return $cart->user_id === $userId;
    }

    /**
     * Delete cart
     */
    public function deleteCart($cart)
    {
        $cart->delete();

        try {
            Cart::reOrderIds();
        } catch (\Exception $e) {
            \Log::warning('Failed to reorder Cart IDs after delete: '.$e->getMessage());
        }

        return ['success' => true];
    }

    /**
     * Prepare items data from validated request
     */
    public function prepareItemsData(array $cartData)
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
     * Get cart items with filters (for API)
     */
    public function getCartItemsWithFilters(array $filters = [])
    {
        $query = CartItem::query();

        if (isset($filters['cart_id'])) {
            $query->where('cart_id', $filters['cart_id']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->get();
    }

    /**
     * Create cart item
     */
    public function createCartItem(array $data)
    {
        $cartItem = CartItem::create($data);

        return $cartItem->fresh();
    }

    /**
     * Get cart item by ID
     */
    public function getCartItemById($id)
    {
        return CartItem::findOrFail($id);
    }

    /**
     * Update cart item by ID
     */
    public function updateCartItemById($id, array $data)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->update($data);

        return $cartItem->fresh();
    }

    /**
     * Delete cart item by ID
     */
    public function deleteCartItem($id)
    {
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();

        return ['success' => true];
    }
}
