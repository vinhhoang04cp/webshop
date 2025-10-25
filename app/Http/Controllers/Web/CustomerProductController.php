<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RatingRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('q') && $request->q) {
            $query->where('name', 'like', "%{$request->q}%")
                ->orWhere('description', 'like', "%{$request->q}%");
        }

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        $sortBy = $request->get('sort', 'latest');
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->latest('created_at');
        }

        $products = $query->paginate(12);
        $categories = Category::withCount('products')->get();

        $cartCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        }

        return view('products.index', compact('products', 'categories', 'cartCount'));
    }

    public function show($id)
    {
        $product = Product::with(['category', 'details', 'inventory', 'ratings.user'])
            ->findOrFail($id);

        $relatedProducts = Product::with('category')
            ->where('category_id', $product->category_id)
            ->where('product_id', '!=', $id)
            ->take(4)
            ->get();

        $cartCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        }

        $categories = Category::withCount('products')->get();

        return view('products.show', compact('product', 'relatedProducts', 'categories', 'cartCount'));
    }

    public function search(Request $request)
    {
        return $this->index($request);
    }

    public function category($id)
    {
        $category = Category::findOrFail($id);

        $products = Product::with('category')
            ->where('category_id', $id)
            ->latest('created_at')
            ->paginate(12);

        $categories = Category::withCount('products')->get();

        $cartCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        }

        return view('products.category', compact('category', 'products', 'categories', 'cartCount'));
    }

    public function storeRating(RatingRequest $request, $productId)
    {
        $existingRating = Rating::where('user_id', Auth::id())
            ->where('product_id', $productId)
            ->first();

        if ($existingRating) {
            return redirect()->back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }
        $rating = new Rating;
        $rating->user_id = Auth::id();
        $rating->product_id = $productId;
        $rating->rating = $request->input('rating');
        $rating->review = $request->input('review', '');
        $rating->save();

        return redirect()->back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    public function promotions()
    {
        $products = Product::with('category')
            ->whereNotNull('original_price')
            ->whereColumn('original_price', '>', 'price')
            ->orderByRaw('((original_price - price) / original_price) DESC')
            ->paginate(12);

        $categories = Category::withCount('products')->get();

        $cartCount = 0;
        if (Auth::check() && Auth::user()->cart) {
            $cartCount = Auth::user()->cart->items()->sum('quantity');
        }

        return view('products.promotions', compact('products', 'categories', 'cartCount'));
    }
}
