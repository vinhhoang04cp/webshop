<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\RatingRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RatingResource;
use App\Http\Resources\SuccessResource;
use App\Models\Rating;
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
        $filters = $request->only([
            'category', 'name', 'search', 'min_price', 'max_price',
            'stock_quantity', 'stock_status', 'has_discount', 'sort_by', 'sort_order',
        ]);
        $perPage = $request->input('per_page', 15);

        $products = $this->productService->getProducts($filters, $perPage);

        return new ProductCollection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        try {
            $product = $this->productService->createProductFull($request->validated());

            return ProductResource::created($product);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Error creating product: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = $this->productService->findProduct($id, true);

        if (! $product) {
            return ErrorResource::notFound('Product not found');
        }

        return ProductResource::retrieved($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProductFull($id, $request->validated());

            if (! $product) {
                return ErrorResource::notFound('Product not found');
            }

            return ProductResource::updated($product);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Error updating product: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $deleted = $this->productService->deleteProductById($id);

            if (! $deleted) {
                return ErrorResource::notFound('Product not found');
            }

            return SuccessResource::deleted('Product and related data deleted successfully');
        } catch (\Exception $e) {
            return ErrorResource::serverError('Error deleting product: '.$e->getMessage());
        }
    }

    /**
     * Lấy danh sách đánh giá của sản phẩm
     */
    public function getRatings($id)
    {
        try {
            $product = $this->productService->findProduct($id, false);

            if (! $product) {
                return ErrorResource::notFound('Product not found');
            }

            $ratings = Rating::where('product_id', $id)
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->get();

            $averageRating = $ratings->avg('rating');
            $totalRatings = $ratings->count();

            return RatingResource::collection($ratings)->additional([
                'status' => true,
                'message' => 'Ratings retrieved successfully',
                'average_rating' => round($averageRating, 1),
                'total_ratings' => $totalRatings,
            ]);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Error retrieving ratings: '.$e->getMessage());
        }
    }

    /**
     * Thêm đánh giá cho sản phẩm
     */
    public function addRating(RatingRequest $request, $id)
    {
        try {
            $product = $this->productService->findProduct($id);

            if (! $product) {
                return ErrorResource::notFound('Product not found');
            }

            $rating = $this->productService->createRating($request->validated(), $id);

            return RatingResource::created($rating);
        } catch (\Exception $e) {
            return ErrorResource::badRequest('Failed to add rating: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật đánh giá (chỉ user tạo rating mới được cập nhật)
     */
    public function updateRating(RatingRequest $request, $productId, $ratingId)
    {
        try {
            $rating = Rating::where('id', $ratingId)
                ->where('product_id', $productId)
                ->where('user_id', $request->user()->id)
                ->first();

            if (! $rating) {
                return ErrorResource::notFound('Rating not found or you do not have permission to update');
            }

            $rating->rating = $request->rating;
            $rating->review = $request->review ?? $rating->review;
            $rating->save();

            return RatingResource::updated($rating);
        } catch (\Exception $e) {
            return ErrorResource::serverError('Failed to update rating: '.$e->getMessage());
        }
    }

    /**
     * Xóa đánh giá (chỉ user tạo rating hoặc admin mới được xóa)
     */
    public function deleteRating(Request $request, $productId, $ratingId)
    {
        try {
            $user = $request->user();
            $rating = Rating::where('id', $ratingId)
                ->where('product_id', $productId)
                ->first();

            if (! $rating) {
                return ErrorResource::notFound('Rating not found');
            }

            // Kiểm tra quyền: chỉ user tạo rating hoặc admin mới được xóa
            if ($rating->user_id !== $user->id && ! $user->isAdmin()) {
                return ErrorResource::forbidden('You do not have permission to delete this rating');
            }

            $rating->delete();

            return SuccessResource::deleted('Rating deleted successfully');
        } catch (\Exception $e) {
            return ErrorResource::serverError('Failed to delete rating: '.$e->getMessage());
        }
    }

    /**
     * Get product statistics
     */
    public function stats(Request $request)
    {
        $stats = $this->productService->getProductStats();

        return response()->json([
            'status' => true,
            'message' => 'Product statistics retrieved successfully',
            'data' => $stats,
        ]);
    }
}
