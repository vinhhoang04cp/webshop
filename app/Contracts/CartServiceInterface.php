<?php

namespace App\Contracts;

use App\Models\Cart;

interface CartServiceInterface
{
    /**
     * Xử lý checkout đơn hàng (dùng chung cho Web và API)
     *
     * @param  array  $data  Dữ liệu checkout (shipping info, payment method, coupon)
     * @param  int|null  $userId  User ID (nếu null, dùng Auth::id())
     * @return array ['success', 'order', 'discount_amount', 'payment_method']
     *
     * @throws \Exception
     */
    public function processCheckout(array $data, $userId = null);

    /**
     * Lấy hoặc tạo giỏ hàng (với relationships cho API)
     *
     * @return Cart
     */
    public function getOrCreateCart();

    /**
     * Lấy danh sách items trong giỏ hàng
     *
     * @param  Cart  $cart
     * @return \Illuminate\Support\Collection
     */
    public function getCartItems($cart);

    /**
     * Thêm sản phẩm vào giỏ hàng
     *
     * @param  int  $productId
     * @param  int  $quantity
     * @return array
     *
     * @throws \Exception
     */
    public function addToCart($productId, $quantity = 1);

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng
     *
     * @param  int  $cartItemId
     * @param  int  $quantity
     * @return array
     *
     * @throws \Exception
     */
    public function updateCartItem($cartItemId, $quantity);

    /**
     * Xóa sản phẩm khỏi giỏ hàng
     *
     * @param  int  $cartItemId
     * @return array
     *
     * @throws \Exception
     */
    public function removeFromCart($cartItemId);

    /**
     * Xóa toàn bộ giỏ hàng
     *
     * @return array
     */
    public function clearCart();

    /**
     * Get carts with filters (for API)
     *
     * @param  int|null  $userId
     * @param  bool  $isAdmin
     * @param  array  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCarts($userId = null, $isAdmin = false, $filters = []);

    /**
     * Calculate cart totals
     *
     * @param  Cart  $cart
     * @return array ['amount' => float, 'items' => int]
     */
    public function calculateCartTotals($cart);

    /**
     * Find or create cart for user
     *
     * @param  int|null  $cartId
     * @param  int  $userId
     * @return Cart
     *
     * @throws \Exception
     */
    public function findOrCreateCartForUser($cartId, $userId);

    /**
     * Add multiple items to cart
     *
     * @param  Cart  $cart
     * @return Cart
     */
    public function addItemsToCart($cart, array $items);

    /**
     * Update multiple items in cart
     *
     * @param  Cart  $cart
     * @return Cart
     */
    public function updateCartItems($cart, array $items);

    /**
     * Get cart by ID (with full relationships)
     *
     * @param  int  $cartId
     * @return Cart
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCartById($cartId);

    /**
     * Check if user owns cart
     *
     * @param  Cart  $cart
     * @param  int  $userId
     * @return bool
     */
    public function userOwnsCart($cart, $userId);

    /**
     * Delete cart
     *
     * @param  Cart  $cart
     * @return array
     */
    public function deleteCart($cart);

    /**
     * Prepare items data from validated request
     *
     * @return array
     */
    public function prepareItemsData(array $cartData);

    /**
     * Get cart items with filters (for API)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCartItemsWithFilters(array $filters = []);

    /**
     * Create cart item
     *
     * @return \App\Models\CartItem
     */
    public function createCartItem(array $data);

    /**
     * Get cart item by ID
     *
     * @param  int  $id
     * @return \App\Models\CartItem
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCartItemById($id);

    /**
     * Update cart item by ID
     *
     * @param  int  $id
     * @return \App\Models\CartItem
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateCartItemById($id, array $data);

    /**
     * Delete cart item by ID
     *
     * @param  int  $id
     * @return array
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteCartItem($id);
}
