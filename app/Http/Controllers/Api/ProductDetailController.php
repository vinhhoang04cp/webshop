<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductDetailRequest;
use App\Http\Resources\ProductDetailCollection;
use App\Http\Resources\ProductDetailResource;
use App\Models\ProductDetail;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ProductDetail::query(); // $query la bien de thuc hien query den Bang ProductDetail thong qua model

        // Filter by product_id, color, size

        if ($request->has('product_id')) {  // neu request truyen len co product_id
            $query->where('product_id', $request->get('product_id')); // thuc hien query den product_id
        }
        if ($request->has('color')) {  // neu request truyen len co color
            $query->where('color', $request->get('color'));
        }
        if ($request->has('size')) {  // neu request truyen len co size
            $query->where('size', $request->get('size'));
        }

        $productDetails = $query->paginate(10); // Paginate results, 10 per page

        return new ProductDetailCollection($productDetails); // tra ve ProductDetailCollection
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductDetailRequest $request) // (ProductDetailRequest $request) la tham so truyen vao de validate , duoc validate truoc khi vao controller
    {
        try {
            $productDetail = ProductDetail::create($request->validated()); // Tạo mới bản ghi với dữ liệu đã được validate

            return (new ProductDetailResource($productDetail)) // tra ve ProductDetailResource
                ->additional(['message' => 'Product detail created successfully']) // them cac thong tin khac vao json tra ve
                ->response()
                ->setStatusCode(201);
        } catch (\Exception $e) { // Bắt lỗi nếu có ngoại lệ xảy ra
            return response()->json([
                'message' => 'Failed to create product detail',
                'error' => $e->getMessage(),
            ], 500);
        }

        return (new ProductDetailResource($productDetail))
            ->additional(['message' => 'Product detail created successfully'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id) // tham so truyen vao la id cua product detail can lay
    {
        $productDetail = ProductDetail::find($id); // $bien productDetail de tim kiem product detail theo id
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
            $productDetail = ProductDetail::find($id);
            if (! $productDetail) {
                return response()->json(['message' => 'Product detail not found'], 404);
            }

            $productDetail->update($request->validated());

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
        //
        $productDetail = ProductDetail::find($id);
        if (! $productDetail) {
            return response()->json(['message' => 'Product detail not found'], 404);
        }

        $productDetail->delete();

        return response()->json(['message' => 'Product detail deleted successfully']);
    }
}
