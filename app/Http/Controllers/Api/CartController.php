<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CartRequest $request)
    {
        $filters = $request->only(['user_id', 'product_id']);
        $carts = $this->cartService->getCarts(
            $request->user()->id,
            $request->user()->isAdmin(),
            $filters
        );

        // Calculate grand total
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
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
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

            return (new CartResource($cart))
                ->additional([
                    'status' => true,
                    'message' => 'Items added to cart successfully',
                    'items_added' => count($itemsToAdd),
                ])
                ->response()
                ->setStatusCode(201);
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
     * Display the specified resource.
     */
    public function show(CartRequest $request, $id)
    {
        $cart = $this->cartService->getCartById($id);

        if (! $request->user()->isAdmin() && ! $this->cartService->userOwnsCart($cart, $request->user()->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. You can only access your own cart.',
            ], 403);
        }

        return (new CartResource($cart))
            ->additional([
                'status' => true,
                'message' => 'Cart retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $cartData = $request->validated();
            $cart = $this->cartService->getCartById($id);

            if (! $request->user()->isAdmin() && ! $this->cartService->userOwnsCart($cart, $request->user()->id)) {
                DB::rollback();

                return response()->json([
                    'status' => false,
                    'message' => 'Access denied. You can only update your own cart.',
                ], 403);
            }

            $itemsToUpdate = $this->cartService->prepareItemsData($cartData);
            $cart = $this->cartService->updateCartItems($cart, $itemsToUpdate);

            DB::commit();

            return (new CartResource($cart))
                ->additional([
                    'status' => true,
                    'message' => 'Cart updated successfully',
                ])
                ->response()
                ->setStatusCode(200);
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
    public function destroy(CartRequest $request, $id)
    {
        $cart = $this->cartService->getCartById($id);

        if (! $request->user()->isAdmin() && ! $this->cartService->userOwnsCart($cart, $request->user()->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. You can only delete your own cart.',
            ], 403);
        }

        $this->cartService->deleteCart($cart);

        return response()->json([
            'status' => true,
            'message' => 'Cart deleted successfully',
        ], 200);
    }

    /**
     * Lấy giỏ hàng hiện tại của user
     */
    public function current(Request $request)
    {
        try {
            $cart = $this->cartService->getOrCreateCart();

            return (new CartResource($cart))
                ->additional([
                    'status' => true,
                    'message' => 'Current cart retrieved successfully',
                ])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve cart',
                'error' => $e->getMessage(),
            ], 500);
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

            return (new CartResource($cart))
                ->additional([
                    'status' => true,
                    'message' => 'Product added to cart successfully',
                ])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to add product to cart',
                'error' => $e->getMessage(),
            ], 500);
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

            return (new CartResource($cart))
                ->additional([
                    'status' => true,
                    'message' => 'Cart item updated successfully',
                ])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update cart item',
                'error' => $e->getMessage(),
            ], 400);
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

            return (new CartResource($cart))
                ->additional([
                    'status' => true,
                    'message' => 'Item removed from cart successfully',
                ])
                ->response()
                ->setStatusCode(200);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to remove item from cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Xóa toàn bộ giỏ hàng
     */
    public function clear(Request $request)
    {
        try {
            $this->cartService->clearCart();

            return response()->json([
                'status' => true,
                'message' => 'Cart cleared successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to clear cart',
                'error' => $e->getMessage(),
            ], 500);
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
                return response()->json([
                    'status' => false,
                    'message' => 'Cart is empty',
                ], 400);
            }

            $totalAmount = $cart->totalPrice();
            $couponCode = strtoupper(trim($request->coupon_code));

            // Tìm coupon
            $coupon = \App\Models\Coupon::where('code', $couponCode)->first();

            if (! $coupon) {
                return response()->json([
                    'status' => false,
                    'message' => 'Coupon not found',
                    'valid' => false,
                ], 404);
            }

            // Validate coupon
            $validation = $coupon->isValid($totalAmount);

            if (! $validation['valid']) {
                return response()->json([
                    'status' => false,
                    'message' => $validation['message'],
                    'valid' => false,
                ], 400);
            }

            // Calculate discount
            $discountAmount = $coupon->calculateDiscount($totalAmount);
            $finalAmount = $totalAmount - $discountAmount;

            return response()->json([
                'status' => true,
                'message' => 'Coupon is valid',
                'valid' => true,
                'data' => [
                    'coupon_code' => $coupon->code,
                    'discount_type' => $coupon->discount_type,
                    'discount_value' => $coupon->discount_value,
                    'original_amount' => $totalAmount,
                    'discount_amount' => $discountAmount,
                    'final_amount' => $finalAmount,
                    'savings' => $discountAmount,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to validate coupon',
                'error' => $e->getMessage(),
            ], 500);
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

            $response = [
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => [
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
                ],
            ];

            // Nếu là VNPAY, trả về thông tin để redirect
            if ($result['payment_method'] === 'vnpay') {
                $response['data']['payment_redirect'] = true;
                $response['data']['order_id_for_payment'] = $result['order']->order_id;
                $response['message'] = 'Order created. Please proceed to payment.';
            }

            return response()->json($response, 201);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => false,
                'message' => 'Failed to process checkout',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
