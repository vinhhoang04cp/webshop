<?php

namespace App\Http\Controllers\Api;

use App\Contracts\CartServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartItemCollection;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\SuccessResource;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    protected $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Hiển thị danh sách các item trong giỏ hàng
     */
    public function index(Request $request)
    {
        $filters = $request->only(['cart_id', 'product_id']);
        $cartItems = $this->cartService->getCartItemsWithFilters($filters);

        return new CartItemCollection($cartItems);
    }

    /**
     * Lưu cart item mới được tạo
     */
    public function store(CartItemRequest $request)
    {
        $cartItem = $this->cartService->createCartItem($request->validated());

        return CartItemResource::created($cartItem);
    }

    /**
     * Hiển thị cart item theo ID
     */
    public function show($id)
    {
        $cartItem = $this->cartService->getCartItemById($id);

        return CartItemResource::retrieved($cartItem);
    }

    /**
     * Cập nhật cart item theo ID
     */
    public function update(CartItemRequest $request, $id)
    {
        $cartItem = $this->cartService->updateCartItemById($id, $request->validated());

        return CartItemResource::updated($cartItem);
    }

    /**
     * Xóa cart item theo ID
     */
    public function destroy(string $id)
    {
        $this->cartService->deleteCartItem($id);

        return SuccessResource::deleted('Cart item deleted successfully');
    }
}
