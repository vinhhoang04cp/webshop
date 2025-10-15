<?php

namespace App\View\Composers;

use App\Models\Category;
use Illuminate\View\View;

class NavigationComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        // Lấy danh sách categories
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        // Đếm số lượng sản phẩm trong giỏ hàng
        $cartCount = 0;
        if (auth()->check()) {
            $cart = auth()->user()->cart;
            if ($cart) {
                $cartCount = $cart->items()->sum('quantity');
            }
        }

        $view->with([
            'categories' => $categories,
            'cartCount' => $cartCount,
        ]);
    }
}
