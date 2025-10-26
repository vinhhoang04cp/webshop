<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductDetailRequest;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\ProductDetailResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
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
            'product_id' => $request->get('product_id'),
            'color' => $request->get('color'),
            'storage' => $request->get('storage'),
            'ram' => $request->get('ram'),
            'chip' => $request->get('chip'),
            'os' => $request->get('os'),
        ];

        $productDetails = $this->productService->getProductDetails($filters);

        return new ProductDetailCollection($productDetails);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductDetailRequest $request)
    {
        try {
            $productDetail = $this->productService->createProductDetail($request->validated());

            return (new ProductDetailResource($productDetail))
                ->additional(['message' => 'Product detail created successfully'])
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create product detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $productDetail = $this->productService->findProductDetail($id);

        if (! $productDetail) {
            return response()->json(['message' => 'Product detail not found'], 404);
        }

        return new ProductDetailResource($productDetail);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductDetailRequest $request, $id)
    {
        try {
            $productDetail = $this->productService->updateProductDetail($id, $request->validated());

            if (! $productDetail) {
                return response()->json(['message' => 'Product detail not found'], 404);
            }

            return (new ProductDetailResource($productDetail))
                ->additional(['message' => 'Product detail updated successfully'])
                ->response()
                ->setStatusCode(200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update product detail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deleted = $this->productService->deleteProductDetail($id);

        if (! $deleted) {
            return response()->json(['message' => 'Product detail not found'], 404);
        }

        return response()->json(['message' => 'Product detail deleted successfully']);
    }
}
