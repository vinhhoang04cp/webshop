<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Requests\CheckoutRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerCartController extends Controller
{
    public function checkout(CheckoutRequest $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán!');
        }

        $cart = Auth::user()->cart;
        if (! $cart || $cart->items()->count() == 0) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }

        DB::beginTransaction();
        try {
            // Kiểm tra tồn kho
            foreach ($cart->items as $item) {
                $product = $item->product;
                if (! $product) {
                    throw new \Exception('Sản phẩm không tồn tại!');
                }

                if ($product->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm '{$product->name}' chỉ còn {$product->stock_quantity} sản phẩm trong kho!");
                }
            }

            $totalAmount = $cart->totalPrice();
            $discountAmount = 0;
            $coupon = null;

            // Xử lý coupon
            if ($request->filled('coupon_code')) {
                $couponCode = strtoupper(trim($request->coupon_code));
                $coupon = \App\Models\Coupon::where('code', $couponCode)->first();

                if (! $coupon) {
                    throw new \Exception('Mã giảm giá không hợp lệ!');
                }

                $validation = $coupon->isValid($totalAmount);
                if (! $validation['valid']) {
                    throw new \Exception($validation['message']);
                }

                $discountAmount = $coupon->calculateDiscount($totalAmount);
                $totalAmount = $totalAmount - $discountAmount;
            }

            // Tạo đơn hàng
            $order = new \App\Models\Order;
            $order->user_id = Auth::id();
            $order->total_amount = $totalAmount;
            $order->status = 'pending';
            $order->order_date = now();
            $order->shipping_name = $request->shipping_name;
            $order->shipping_phone = $request->shipping_phone;
            $order->shipping_address = $request->shipping_address;
            $order->note = $request->note;
            $order->save();

            if ($coupon) {
                $coupon->increment('used_count');
                $coupon->save();
            }

            // Tạo order items và trừ tồn kho
            foreach ($cart->items as $item) {
                $product = $item->product;

                $orderItem = new \App\Models\OrderItem;
                $orderItem->order_id = $order->order_id;
                $orderItem->product_id = $item->product_id;
                $orderItem->quantity = $item->quantity;
                $orderItem->price = $item->price ?? $product->price;
                $orderItem->save();

                // Trừ tồn kho
                $product->decrement('stock_quantity', $item->quantity);

                // Cập nhật inventory
                $inventory = \App\Models\Inventory::firstOrCreate(
                    ['product_id' => $product->product_id],
                    [
                        'stock_in' => 0,
                        'stock_out' => 0,
                        'current_stock' => 0,
                    ]
                );
                $inventory->increment('stock_out', $item->quantity);
                $inventory->decrement('current_stock', $item->quantity);
                $inventory->save();
            }

            $cart->items()->delete();

            DB::commit();

            // Kiểm tra phương thức thanh toán
            if ($request->payment_method === 'vnpay') {
                session(['pending_payment_order_id' => $order->order_id]);

                return redirect()->route('payment.create.get')
                    ->with('success', 'Đơn hàng đã được tạo. Đang chuyển đến trang thanh toán...');
            }

            $successMessage = 'Đặt hàng thành công! Đơn hàng của bạn đã được ghi nhận.';
            if ($discountAmount > 0) {
                $successMessage .= ' Bạn đã tiết kiệm được '.number_format($discountAmount, 0, ',', '.').' VND với mã giảm giá!';
            }
            $successMessage .= ' Chúng tôi sẽ liên hệ với bạn qua số điện thoại '.$request->shipping_phone.' để xác nhận.';

            return redirect()->route('cart.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra khi đặt hàng: '.$e->getMessage());
        }
    }

    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ hàng!');
        }

        try {
            $cart = Auth::user()->cart;

            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                ]);
            }

            $cartItems = collect();

            try {
                $cartItems = $cart->items()
                    ->with(['product' => function ($query) {
                        $query->with('category');
                    }])
                    ->get();
            } catch (\Exception $e) {
                \Log::error('Error loading cart items: '.$e->getMessage());
                $cartItems = collect();
            }

            $categories = \App\Models\Category::withCount('products')->get();
            $cartCount = $cartItems->sum('quantity');

            return view('cart.index', compact('cart', 'cartItems', 'categories', 'cartCount'));

        } catch (\Exception $e) {
            \Log::error('Error in cart index: '.$e->getMessage());

            return redirect()->route('home')->with('error', 'Có lỗi xảy ra khi tải giỏ hàng: '.$e->getMessage());
        }
    }

    public function add(CartRequest $request, $productId)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
        }

        $quantity = $request->get('quantity', 1);

        try {
            DB::beginTransaction();

            $product = Product::findOrFail($productId);

            $cart = Auth::user()->cart;
            if (! $cart) {
                $cart = Cart::create([
                    'user_id' => Auth::id(),
                ]);
            }

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

            return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function update(CartRequest $request, $cartItemId)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($cartItemId);

            if ($cartItem->cart->user_id != Auth::id()) {
                return redirect()->route('cart.index')->with('error', 'Không có quyền!');
            }

            $cartItem->quantity = $request->quantity;
            $cartItem->save();

            $cart = $cartItem->cart;

            DB::commit();

            return redirect()->route('cart.index')->with('success', 'Đã cập nhật số lượng sản phẩm!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function remove($cartItemId)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            DB::beginTransaction();

            $cartItem = CartItem::findOrFail($cartItemId);

            if ($cartItem->cart->user_id != Auth::id()) {
                return redirect()->route('cart.index')->with('error', 'Không có quyền!');
            }

            $cart = $cartItem->cart;
            $cartItem->delete();

            DB::commit();

            return redirect()->route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function clear()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cart->items()->delete();
            }

            return redirect()->back()->with('success', 'Đã xóa toàn bộ giỏ hàng!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }
}
