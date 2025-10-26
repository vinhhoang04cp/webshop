<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Exception;
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

        $cartsData = [];
        $grandTotal = 0;

        foreach ($carts as $cart) {
            $cartTotals = $this->cartService->calculateCartTotals($cart);

            $cartData = new CartResource($cart);
            $cartData->additional([
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
            ]);

            $cartsData[] = [
                'cart' => $cartData,
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
            ];

            $grandTotal += $cartTotals['amount'];
        }

        return response()->json([
            'status' => true,
            'message' => 'Carts retrieved successfully',
            'data' => $cartsData,
            'pagination' => [
                'current_page' => $carts->currentPage(),
                'per_page' => $carts->perPage(),
                'total' => $carts->total(),
                'last_page' => $carts->lastPage(),
            ],
            'grand_total' => $grandTotal,
            'total_carts' => $carts->total(),
        ], 200);
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

            $cartTotals = $this->cartService->calculateCartTotals($cart);

            return response()->json([
                'status' => true,
                'message' => 'Items added to cart successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
                'items_added' => count($itemsToAdd),
            ], 201);
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

        $cartTotals = $this->cartService->calculateCartTotals($cart);

        return response()->json([
            'status' => true,
            'message' => 'Cart retrieved successfully',
            'data' => new CartResource($cart),
            'total_amount' => $cartTotals['amount'],
            'total_items' => $cartTotals['items'],
        ], 200);
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

            $cartTotals = $this->cartService->calculateCartTotals($cart);

            return response()->json([
                'status' => true,
                'message' => 'Cart updated successfully',
                'data' => new CartResource($cart),
                'total_amount' => $cartTotals['amount'],
                'total_items' => $cartTotals['items'],
            ], 200);
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
}
