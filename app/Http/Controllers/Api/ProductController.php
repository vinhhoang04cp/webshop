<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->get();
        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'stock_quantity' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|max:255',
        ]);

        $products = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'stock_quantity' => $request->stock_quantity ?? 0,
            'image_url' => $request->image_url,
        ]);
        return (new ProductResource($products))
            ->additional([
                'status' => true,
                'message' => 'Product created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::with('category')->find($id);
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0',
            'category_id' => 'sometimes|required|integer|exists:categories,category_id',
            'stock_quantity' => 'nullable|integer|min:0',
            'image_url' => 'nullable|string|max:255',
        ]);

        $product = Product::find($id);
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }
        $product->update($request->only(['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url']));
        return (new ProductResource($product))
            ->additional([
                'status' => true,
                'message' => 'Product updated successfully',
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
        $product = Product::find($id);
        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }
        $product->delete();
        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully',
        ]); 
    }
}
