<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected $cartService; // khai bao thuoc tinh cartService

    // __contruct(CartService $cartService) : khoi tao doi tuong cartService khi tao doi tuong CartController
    public function __construct(CartService $cartService) // Khoi tao CartService, su dung dependency injection
    {
        $this->cartService = $cartService; // Gan doi tuong CartService vao thuoc tinh cartService
    }

    /**
     * Hiển thị danh sách giỏ hàng
     */
    public function index(CartRequest $request)
    {
        $filters = $request->only(['user_id', 'product_id']); // ham only() lay cac tham so loc tu request, chi lay nhung tham so can thiet
        $carts = $this->cartService->getCarts(  // $carts la danh sach gio hang duoc lay tu cartService qua ham getCarts voi cac tham so loc
            $request->user()->id, // lay gio hang cua user hien tai
            $request->user()->isAdmin(), // kiem tra neu user la admin
            $filters // truyen cac tham so loc de lay gio hang theo yeu cau
        );

        // Tính tổng giá trị tất cả giỏ hàng
        $grandTotal = $carts->sum(function ($cart) {
            return $cart->items->sum(function ($item) {
                return $item->quantity * ($item->product->price ?? $item->price);
            });
        });

        return CartResource::collection($carts)
            ->additional([
                'status' => true,
                'message' => 'Carts retrieved successfully',
                'summary' => [
                    'grand_total' => $grandTotal,
                    'total_carts' => $carts->total(),
                ],
            ]);
    }

    /**
     * Lưu giỏ hàng mới được tạo
     */
    public function store(CartRequest $request)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated();
            $userId = $request->user()->id;

            $cart = $this->cartService->findOrCreateCartForUser(
                $cartData['cart_id'] ?? null,
                $userId
            );

            $itemsToAdd = $this->cartService->prepareItemsData($cartData);
            $cart = $this->cartService->addItemsToCart($cart, $itemsToAdd);

            DB::commit();

            return CartResource::created($cart, ['items_added' => count($itemsToAdd)]);
        } catch (Exception $e) {
            DB::rollback();

            return ErrorResource::serverError('Failed to add items to cart: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị giỏ hàng theo ID
     */
    public function show(CartRequest $request, $id)
    {
        $cart = $this->cartService->getCartById($id);

        if (! $request->user()->isAdmin() && ! $this->cartService->userOwnsCart($cart, $request->user()->id)) {
            return ErrorResource::forbidden('Access denied. You can only access your own cart.');
        }

        return CartResource::retrieved($cart);
    }

    /**
     * Cập nhật giỏ hàng theo ID
     */
    public function update(CartRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated();
            $cart = $this->cartService->getCartById($id);

            if (! $request->user()->isAdmin() && ! $this->cartService->userOwnsCart($cart, $request->user()->id)) {
                DB::rollback();

                return ErrorResource::forbidden('Access denied. You can only update your own cart.');
            }

            $itemsToUpdate = $this->cartService->prepareItemsData($cartData);
            $cart = $this->cartService->updateCartItems($cart, $itemsToUpdate);

            DB::commit();

            return CartResource::updated($cart);
        } catch (Exception $e) {
            DB::rollback();

            return ErrorResource::serverError('Failed to update cart: '.$e->getMessage());
        }
    }

    /**
     * Xóa giỏ hàng theo ID
     */
    public function destroy(CartRequest $request, $id)
    {
        $cart = $this->cartService->getCartById($id);

        if (! $request->user()->isAdmin() && ! $this->cartService->userOwnsCart($cart, $request->user()->id)) {
            return ErrorResource::forbidden('Access denied. You can only delete your own cart.');
        }

        $this->cartService->deleteCart($cart);

        return SuccessResource::deleted('Cart deleted successfully');
    }

    /**
     * Lấy giỏ hàng hiện tại của user
     */
    public function current(Request $request)
    {
        try {
            $cart = $this->cartService->getOrCreateCart();

            return CartResource::current($cart);
        } catch (Exception $e) {
            return ErrorResource::serverError('Failed to retrieve cart: '.$e->getMessage());
        }
    }

    /**
     * Thêm một sản phẩm vào giỏ hàng (đơn giản hóa)
     */
    public function addProduct(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'sometimes|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $quantity = $request->input('quantity', 1);
            $this->cartService->addToCart($productId, $quantity);

            $cart = $this->cartService->getOrCreateCart();

            DB::commit();

            return CartResource::productAdded($cart);
        } catch (Exception $e) {
            DB::rollback();

            return ErrorResource::serverError('Failed to add product to cart: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật số lượng một cart item
     */
    public function updateItem(Request $request, $cartItemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $this->cartService->updateCartItem($cartItemId, $request->quantity);

            $cart = $this->cartService->getOrCreateCart();

            DB::commit();

            return CartResource::itemUpdated($cart);
        } catch (Exception $e) {
            DB::rollback();

            return ErrorResource::badRequest('Failed to update cart item: '.$e->getMessage());
        }
    }

    /**
     * Xóa một item khỏi giỏ hàng
     */
    public function removeItem(Request $request, $cartItemId)
    {
        DB::beginTransaction();
        try {
            $this->cartService->removeFromCart($cartItemId);

            $cart = $this->cartService->getOrCreateCart();

            DB::commit();

            return CartResource::itemRemoved($cart);
        } catch (Exception $e) {
            DB::rollback();

            return ErrorResource::serverError('Failed to remove item from cart: '.$e->getMessage());
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request)
    {
        try {
            $this->cartService->clearCart();

            return SuccessResource::message('Cart cleared successfully');
        } catch (Exception $e) {
            return ErrorResource::serverError('Failed to clear cart: '.$e->getMessage());
        }
    }

    /**
     * Validate và preview coupon (kiểm tra trước khi checkout)
     */
    public function validateCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:50',
        ]);

        try {
            $cart = $this->cartService->getOrCreateCart();

            if (! $cart || $cart->items()->count() == 0) {
                return ErrorResource::badRequest('Cart is empty');
            }

            $totalAmount = $cart->totalPrice();
            $couponCode = strtoupper(trim($request->coupon_code));

            // Tìm coupon
            $coupon = \App\Models\Coupon::where('code', $couponCode)->first();

            if (! $coupon) {
                return ErrorResource::notFound('Coupon not found', ['valid' => false]);
            }

            // Validate coupon
            $validation = $coupon->isValid($totalAmount);

            if (! $validation['valid']) {
                return ErrorResource::badRequest($validation['message'], ['valid' => false]);
            }

            // Calculate discount
            $discountAmount = $coupon->calculateDiscount($totalAmount);
            $finalAmount = $totalAmount - $discountAmount;

            return SuccessResource::withData([
                'valid' => true,
                'coupon_code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'original_amount' => $totalAmount,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'savings' => $discountAmount,
            ], 'Coupon is valid');
        } catch (Exception $e) {
            return ErrorResource::serverError('Failed to validate coupon: '.$e->getMessage());
        }
    }

    /**
     * Xử lý checkout đơn hàng
     */
    public function checkout(CheckoutRequest $request)
    {
        DB::beginTransaction();
        try {
            $result = $this->cartService->processCheckout($request->validated());

            DB::commit();

            $responseData = [
                'order' => [
                    'order_id' => $result['order']->order_id,
                    'total_amount' => $result['order']->total_amount,
                    'status' => $result['order']->status,
                    'shipping_name' => $result['order']->shipping_name,
                    'shipping_phone' => $result['order']->shipping_phone,
                    'shipping_address' => $result['order']->shipping_address,
                    'note' => $result['order']->note,
                    'order_date' => $result['order']->order_date,
                ],
                'discount_amount' => $result['discount_amount'],
                'payment_method' => $result['payment_method'],
            ];

            $message = 'Order placed successfully';

            // Nếu là VNPAY, trả về thông tin để redirect
            if ($result['payment_method'] === 'vnpay') {
                $responseData['payment_redirect'] = true;
                $responseData['order_id_for_payment'] = $result['order']->order_id;
                $message = 'Order created. Please proceed to payment.';
            }

            return SuccessResource::withData($responseData, $message, 201);
        } catch (Exception $e) {
            DB::rollback();

            return ErrorResource::badRequest('Failed to process checkout: '.$e->getMessage());
        }
    }
}
