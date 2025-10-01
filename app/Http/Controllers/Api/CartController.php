<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartCollection;
use App\Http\Requests\CartRequest;
use Illuminate\Support\Facades\DB;
use Exception;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cart::query();
        if ($request->has('user_id')) {
            $query->where('user_id', $request->get('user_id'));
        }
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        if ($request->has('min_date')) {
            $query->where('created_at', '>=', $request->get('min_date'));
        }
        if ($request->has('max_date')) {
            $query->where('created_at', '<=', $request->get('max_date'));
        }
        return (new CartCollection($query->with('items.product')->get()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
