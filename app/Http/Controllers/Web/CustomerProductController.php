<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RatingRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;

class CustomerProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $filters = $request->only(['q', 'category', 'min_price', 'max_price', 'sort']);

        $products = $this->productService->getProductsList($filters, 12);
        $categories = $this->productService->getAllCategories();
        $cartCount = $this->productService->getCartCount();

        return view('products.index', compact('products', 'categories', 'cartCount'));
    }

    public function show($id)
    {
        $product = $this->productService->getProductDetail($id);
        $relatedProducts = $this->productService->getRelatedProducts($id, $product->category_id, 4);
        $categories = $this->productService->getAllCategories();
        $cartCount = $this->productService->getCartCount();

        return view('products.show', compact('product', 'relatedProducts', 'categories', 'cartCount'));
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function category($id)
    {
        $result = $this->productService->getProductsByCategory($id, 12);
        $categories = $this->productService->getAllCategories();
        $cartCount = $this->productService->getCartCount();

        return view('products.category', compact('categories', 'cartCount'))
            ->with($result);
    }

    public function storeRating(RatingRequest $request, $productId)
    {
        try {
            $this->productService->createRating($request->validated(), $productId);

            return redirect()->back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function promotions()
    {
        $products = $this->productService->getPromotionProducts(12);
        $categories = $this->productService->getAllCategories();
        $cartCount = $this->productService->getCartCount();

        return view('products.promotions', compact('products', 'categories', 'cartCount'));
    }
}
