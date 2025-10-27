<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartItemCollection;
use App\Http\Resources\CartItemResource;
use App\Http\Resources\SuccessResource;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['cart_id', 'product_id']);
        $cartItems = $this->cartService->getCartItemsWithFilters($filters);

        return new CartItemCollection($cartItems);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartItemRequest $request)
    {
        $cartItem = $this->cartService->createCartItem($request->validated());

        return CartItemResource::created($cartItem);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cartItem = $this->cartService->getCartItemById($id);

        return CartItemResource::retrieved($cartItem);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartItemRequest $request, $id)
    {
        $cartItem = $this->cartService->updateCartItemById($id, $request->validated());

        return CartItemResource::updated($cartItem);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->cartService->deleteCartItem($id);

        return SuccessResource::deleted('Cart item deleted successfully');
    }
}
