<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'category' => $request->get('category'),
            'name' => $request->get('name'),
            'min_price' => $request->get('min_price'),
            'max_price' => $request->get('max_price'),
            'stock_quantity' => $request->get('stock_quantity'),
        ];

        $products = $this->productService->getProducts($filters);

        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        try {
            $product = $this->productService->createProductFull($request->validated());

            return (new ProductResource($product))
                ->additional([
                    'status' => true,
                    'message' => 'Product created successfully',
                ])
                ->response()
                ->setStatusCode(201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = $this->productService->findProduct($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return (new ProductResource($product))
            ->additional([
                'status' => true,
                'message' => 'Product retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProductFull($id, $request->validated());

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            return (new ProductResource($product))
                ->additional([
                    'status' => true,
                    'message' => 'Product updated successfully',
                ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->productService->deleteProductById($id);

            if (!$deleted) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Product and related data deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting product: ' . $e->getMessage(),
            ], 500);
        }
    }
}
