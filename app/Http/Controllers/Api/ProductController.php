<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductCollection;
use App\Http\Requests\ProductRequest;


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() // Tra ve danh sach san pham
    {
        $products = Product::with('category')->get();
        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)  // Request lam viec voi du lieu vao va $request chua du lieu nguoi dung gui len
    {
        $product = Product::create($request->only(['name', 'description', 'price', 'category_id', 'stock_quantity', 'image_url']));
        return (new ProductResource($product))
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
    public function update(ProductRequest $request, $id)
    {
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
            ]);    
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
