<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        $featuredProducts = Product::with('category')
            ->inRandomOrder()
            ->take(8)
            ->get();

        $newProducts = Product::with('category')
            ->latest('created_at')
            ->take(8)
            ->get();

        $cartCount = 0;
        if (Auth::check()) {
            $cart = Auth::user()->cart;
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        }

        return view('home', compact('categories', 'featuredProducts', 'newProducts', 'cartCount'));
    }
}
