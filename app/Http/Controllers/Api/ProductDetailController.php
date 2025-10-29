<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductDetailRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\ProductDetailResource;
use App\Http\Resources\SuccessResource;
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
     * Hiển thị danh sách chi tiết sản phẩm
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
     * Lưu chi tiết sản phẩm mới được tạo
     */
    public function store(ProductDetailRequest $request)
    {
        try {
            $productDetail = $this->productService->createProductDetail($request->validated());

            return ProductDetailResource::created($productDetail);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Failed to create product detail: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết sản phẩm theo ID
     */
    public function show($id)
    {
        $productDetail = $this->productService->findProductDetail($id);

        if (! $productDetail) {
            return ErrorResource::notFound('Product detail not found');
        }

        return ProductDetailResource::retrieved($productDetail);
    }

    /**
     * Cập nhật chi tiết sản phẩm theo ID
     */
    public function update(ProductDetailRequest $request, $id)
    {
        try {
            $productDetail = $this->productService->updateProductDetail($id, $request->validated());

            if (! $productDetail) {
                return ErrorResource::notFound('Product detail not found');
            }

            return ProductDetailResource::updated($productDetail);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Failed to update product detail: '.$e->getMessage());
        }
    }

    /**
     * Xóa chi tiết sản phẩm theo ID
     */
    public function destroy($id)
    {
        $deleted = $this->productService->deleteProductDetail($id);

        if (! $deleted) {
            return ErrorResource::notFound('Product detail not found');
        }

        return SuccessResource::deleted('Product detail deleted successfully');
    }
}
