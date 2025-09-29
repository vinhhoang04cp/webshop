<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        $query = ProductDetail::query();
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        if ($request->has('color')) {
            $query->where('color', $request->get('color'));     // Filter by color
        }
        if ($request->has('size')) {
            $query->where('size', $request->get('size'));     // Filter by size
        }
        if ($request->has('min_stock')) {
            $query->where('stock', '>=', $request->get('min_stock')); // Filter by minimum stock
        }
        if ($request->has('max_stock')) {
            $query->where('stock', '<=', $request->get('max_stock')); // Filter by maximum stock
        }

        $productDetails = $query->get();
        $productDetails = $query->paginate(10); // Paginate results, 10 per page

        return response()->json($productDetails);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
