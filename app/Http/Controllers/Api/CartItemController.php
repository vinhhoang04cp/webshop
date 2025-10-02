<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartItemCollection;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = CartItem::query();
        // Loc theo cart_id neu co
        if ($request->has('cart_id')) {
            $query->where('cart_id', $request->input('cart_id'));
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        }

        return (new CartItemCollection($query->get()))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $cartItem = CartItem::create($request->all());
        CartItem::reorderIds();
        $cartItem->fresh();

        return (new CartItemResource($cartItem))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
        $cartItem = CartItem::findOrFail($id);

        return (new CartItemResource($cartItem))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
        $cartItem = CartItem::findOrFail($id);
        $cartItem->update($request->all());
        CartItem::reorderIds();
        $cartItem->fresh();

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
        //
        $cartItem = CartItem::findOrFail($id);
        $cartItem->delete();
        CartItem::reorderIds();

        return response()->json([
            'status' => true,
            'message' => 'Cart item deleted successfully',
        ], 200);
    }
}
