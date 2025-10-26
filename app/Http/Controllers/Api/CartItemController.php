<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartItemCollection;
use App\Http\Resources\CartItemResource;
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

        return (new CartItemCollection($cartItems))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CartItemRequest $request)
    {
        $cartItem = $this->cartService->createCartItem($request->validated());

        return (new CartItemResource($cartItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $cartItem = $this->cartService->getCartItemById($id);

        return (new CartItemResource($cartItem))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CartItemRequest $request, $id)
    {
        $cartItem = $this->cartService->updateCartItemById($id, $request->validated());

        return (new CartItemResource($cartItem))
            ->additional([
                'status' => true,
                'message' => 'Cart item updated successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->cartService->deleteCartItem($id);

        return response()->json([
            'status' => true,
            'message' => 'Cart item deleted successfully',
        ], 200);
    }
}
