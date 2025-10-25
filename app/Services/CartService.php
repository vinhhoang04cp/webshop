<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Xử lý checkout đơn hàng
     */
    public function processCheckout(array $data)
    {
        $cart = Auth::user()->cart;

        if (! $cart || $cart->items()->count() == 0) {
            throw new \Exception('Giỏ hàng trống!');
        }

        DB::beginTransaction();
        try {
            // Kiểm tra tồn kho
            $this->validateStock($cart);

            $totalAmount = $cart->totalPrice();
            $discountAmount = 0;
            $coupon = null;

            // Xử lý coupon
            if (! empty($data['coupon_code'])) {
                $result = $this->applyCoupon($data['coupon_code'], $totalAmount);
                $coupon = $result['coupon'];
                $discountAmount = $result['discount'];
                $totalAmount = $result['total'];
            }

            // Tạo đơn hàng
            $order = $this->createOrder($data, $totalAmount);

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
                'order' => $order,
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
                throw new \Exception('Sản phẩm không tồn tại!');
            }

            if ($product->stock_quantity < $item->quantity) {
                throw new \Exception("Sản phẩm '{$product->name}' chỉ còn {$product->stock_quantity} sản phẩm trong kho!");
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
            throw new \Exception('Mã giảm giá không hợp lệ!');
        }

        $validation = $coupon->isValid($totalAmount);
        if (! $validation['valid']) {
            throw new \Exception($validation['message']);
        }

        $discountAmount = $coupon->calculateDiscount($totalAmount);
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
    protected function createOrder(array $data, $totalAmount)
    {
        $order = new Order;
        $order->user_id = Auth::id();
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
            $orderItem = new OrderItem;
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
     * Lấy hoặc tạo giỏ hàng
     */
    public function getOrCreateCart()
    {
        $cart = Auth::user()->cart;

        if (! $cart) {
            $cart = Cart::create([
                'user_id' => Auth::id(),
            ]);
        }

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
                throw new \Exception('Không có quyền!');
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
                throw new \Exception('Không có quyền!');
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
}
