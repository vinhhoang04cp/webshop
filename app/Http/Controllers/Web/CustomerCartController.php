<?php

namespace App\Http\Controllers\Web;

use App\Contracts\CartServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Support\Facades\Auth;

class CustomerCartController extends Controller
{
    protected $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    public function checkout(CheckoutRequest $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để thanh toán!');
        }

        try {
            $result = $this->cartService->processCheckout($request->validated());

            // Kiểm tra phương thức thanh toán
            if ($result['payment_method'] === 'vnpay') {
                session(['pending_payment_order_id' => $result['order']->order_id]);

                return redirect()->route('payment.create.get')
                    ->with('success', 'Đơn hàng đã được tạo. Đang chuyển đến trang thanh toán...');
            }

            $successMessage = 'Đặt hàng thành công! Đơn hàng của bạn đã được ghi nhận.';
            if ($result['discount_amount'] > 0) {
                $successMessage .= ' Bạn đã tiết kiệm được '.number_format($result['discount_amount'], 0, ',', '.').' VND với mã giảm giá!';
            }
            $successMessage .= ' Chúng tôi sẽ liên hệ với bạn qua số điện thoại '.$request->shipping_phone.' để xác nhận.';

            return redirect()->route('cart.index')->with('success', $successMessage);
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra khi đặt hàng: '.$e->getMessage());
        }
    }

    public function index()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để xem giỏ hàng!');
        }

        try {
            $cart = $this->cartService->getOrCreateCart();
            $cartItems = $this->cartService->getCartItems($cart);
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
            $this->cartService->addToCart($productId, $quantity);

            return redirect()->back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function update(CartRequest $request, $cartItemId)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $this->cartService->updateCartItem($cartItemId, $request->quantity);

            return redirect()->route('cart.index')->with('success', 'Đã cập nhật số lượng sản phẩm!');
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function remove($cartItemId)
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $this->cartService->removeFromCart($cartItemId);

            return redirect()->route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng!');
        } catch (\Exception $e) {
            return redirect()->route('cart.index')->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }

    public function clear()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập!');
        }

        try {
            $this->cartService->clearCart();

            return redirect()->back()->with('success', 'Đã xóa toàn bộ giỏ hàng!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: '.$e->getMessage());
        }
    }
}
