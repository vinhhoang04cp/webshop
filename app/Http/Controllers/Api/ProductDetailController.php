<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductDetail;
use Illuminate\Http\Request;
use App\Http\Requests\ProductDetailRequest;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\ProductDetailCollection;

class ProductDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductDetail::query();
        
        if ($request->has('product_id')) {
            $query->where('product_id', $request->get('product_id'));
        }
        if ($request->has('color')) {
            $query->where('color', $request->get('color'));
        }
        if ($request->has('size')) {
            $query->where('size', $request->get('size'));
        }

        $productDetails = $query->paginate(10); // Paginate results, 10 per page

        return new ProductDetailCollection($productDetails);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductDetailRequest $request)
    {
        try {
            $productDetail = ProductDetail::create($request->validated());
            
            return (new ProductDetailResource($productDetail))
                ->additional(['message' => 'Product detail created successfully'])
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product detail', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productDetail = ProductDetail::find($id);
        if (!$productDetail) {
            return response()->json(['message' => 'Product detail not found'], 404);
        }
        return new ProductDetailResource($productDetail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductDetailRequest $request, $id)
    {
        //
        $query = ProductDetail::find($id);
        if (!$query) {
            return response()->json(['message' => 'Product detail not found'], 404);
        }

        $query->update($request->validated());

        return response()->json(['message' => 'Product detail updated successfully', 'data' => $query]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
        $productDetail = ProductDetail::find($id);
        if (!$productDetail) {
            return response()->json(['message' => 'Product detail not found'], 404);
        }

        $productDetail->delete();

        return response()->json(['message' => 'Product detail deleted successfully']);
    }
}
