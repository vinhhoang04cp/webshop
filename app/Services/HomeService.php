<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;

class HomeService
{
    /**
     * Lấy danh sách categories với số lượng products
     */
    public function getCategories()
    {
        return Category::withCount('products')
            ->orderBy('name')
            ->get();
    }

    /**
     * Lấy sản phẩm nổi bật
     */
    public function getFeaturedProducts($limit = 8)
    {
        return Product::with('category')
            ->inRandomOrder()
            ->take($limit)
            ->get();
    }

    /**
     * Lấy sản phẩm mới nhất
     */
    public function getNewProducts($limit = 8)
    {
        return Product::with('category')
            ->latest('created_at')
            ->take($limit)
            ->get();
    }

    /**
     * Lấy số lượng sản phẩm trong giỏ hàng của user
     */
    public function getCartCount($user)
    {
        if (! $user) {
            return 0;
        }

        $cart = $user->cart;
        if (! $cart) {
            return 0;
        }

        return $cart->items()->sum('quantity');
    }
}
