<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    /**
     * Hiển thị trang chủ
     */
    public function index() // ham index de hien thi trang chu
    {
        // Lấy danh sách danh mục với số lượng sản phẩm
        $categories = Category::withCount('products') // query builder de dem so luong san pham trong tung danh muc
            ->orderBy('name') // sap xep theo ten
            ->get(); // lay ve danh sach danh muc voi so luong san pham trong tung danh muc

        // Lấy sản phẩm nổi bật (8 sản phẩm random)
        $featuredProducts = Product::with('category') // lay ve san pham voi category
            ->inRandomOrder() // sap xep ngau nhien
            ->take(8)
            ->get();

        // Lấy sản phẩm mới nhất (8 sản phẩm)
        $newProducts = Product::with('category')
            ->latest('created_at') // sap xep theo ngay tao moi nhat
            ->take(8)
            ->get();

        // Đếm số lượng sản phẩm trong giỏ hàng (nếu user đã đăng nhập)
        $cartCount = 0; // khoi tao cartCount bang 0
        if (auth()->check()) { // Check neu user da dang nhap
            $cart = auth()->user()->cart; // auth() la ham tra ve doi tuong nguoi dung hien tai dang nhap
            if ($cart) {  // neu co gio hang thi tinh tong so luong san pham trong gio hang
                $cartCount = $cart->items()->sum('quantity'); // tinh tong so luong san pham trong gio hang
            }
        }

        return view('home', compact('categories', 'featuredProducts', 'newProducts', 'cartCount')); // truyen du lieu ra view
    }
}
