<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\RatingRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Http\Resources\RatingResource;
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
                'message' => 'Error creating product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = $this->productService->findProduct($id, true);

        if (! $product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return (new ProductResource($product))->additional([
            'status' => true,
            'message' => 'Product retrieved successfully',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $product = $this->productService->updateProductFull($id, $request->validated());

            if (! $product) {
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
                'message' => 'Error updating product: '.$e->getMessage(),
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

            if (! $deleted) {
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
                'message' => 'Error deleting product: '.$e->getMessage(),
            ], 500);
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
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
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
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving ratings',
                'error' => $e->getMessage(),
            ], 500);
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
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $rating = $this->productService->createRating($request->validated(), $id);

            return (new RatingResource($rating))->additional([
                'status' => true,
                'message' => 'Rating added successfully',
            ])->response()->setStatusCode(201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to add rating',
                'error' => $e->getMessage(),
            ], 400);
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
                return response()->json([
                    'status' => false,
                    'message' => 'Rating not found or you do not have permission to update',
                ], 404);
            }

            $rating->rating = $request->rating;
            $rating->review = $request->review ?? $rating->review;
            $rating->save();

            return (new RatingResource($rating))->additional([
                'status' => true,
                'message' => 'Rating updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to update rating',
                'error' => $e->getMessage(),
            ], 500);
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
                return response()->json([
                    'status' => false,
                    'message' => 'Rating not found',
                ], 404);
            }

            // Kiểm tra quyền: chỉ user tạo rating hoặc admin mới được xóa
            if ($rating->user_id !== $user->id && ! $user->isAdmin()) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to delete this rating',
                ], 403);
            }

            $rating->delete();

            return response()->json([
                'status' => true,
                'message' => 'Rating deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete rating',
                'error' => $e->getMessage(),
            ], 500);
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
        ], 200);
    }
}
