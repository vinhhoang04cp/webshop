<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\RatingRequest;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
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
                'message' => 'Error creating product: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = $this->productService->findProduct($id);

        if (! $product) {
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
            $product = $this->productService->findProduct($id);

            if (! $product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $ratings = Rating::where('product_id', $id)
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($rating) {
                    return [
                        'id' => $rating->id,
                        'rating' => $rating->rating,
                        'review' => $rating->review,
                        'user' => [
                            'id' => $rating->user->id,
                            'name' => $rating->user->name,
                        ],
                        'created_at' => $rating->created_at,
                    ];
                });

            $averageRating = $ratings->avg('rating');
            $totalRatings = $ratings->count();

            return response()->json([
                'status' => true,
                'message' => 'Ratings retrieved successfully',
                'data' => [
                    'ratings' => $ratings,
                    'average_rating' => round($averageRating, 1),
                    'total_ratings' => $totalRatings,
                ],
            ], 200);
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

            return response()->json([
                'status' => true,
                'message' => 'Rating added successfully',
                'data' => [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'review' => $rating->review,
                    'created_at' => $rating->created_at,
                ],
            ], 201);
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

            return response()->json([
                'status' => true,
                'message' => 'Rating updated successfully',
                'data' => [
                    'id' => $rating->id,
                    'rating' => $rating->rating,
                    'review' => $rating->review,
                    'updated_at' => $rating->updated_at,
                ],
            ], 200);
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
}
