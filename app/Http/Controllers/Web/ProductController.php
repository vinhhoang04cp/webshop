<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products for admin UI.
     */
    public function index(Request $request) //Request $request de lay du lieu tu Http request
    {
        try {
            // $query de lay products voi relationship category
            $query = Product::with('category');

            // Search
            if ($request->has('search') && $request->search) { //$request->has('search') kiem tra xem co tham so search trong request khong, && $request->search kiem tra xem tham so search co gia tri khong
                $searchTerm = $request->search; // Lay gia tri search tu request
                $query->where(function ($q) use ($searchTerm) { // Su dung where de loc products theo name hoac description
                    $q->where('name', 'like', '%'.$searchTerm.'%') // Loc theo name
                        ->orWhere('description', 'like', '%'.$searchTerm.'%'); // Hoac loc theo description
                });
            }

            // Pagination
            $perPage = 12; // So luong products tren moi trang
            $products = $query->paginate($perPage); // $query->paginate de phan trang      

            // allProducts de lay tat ca products khong phan trang
            $allProducts = Product::all();

            // categories de lay tat ca categories
            $categories = Category::all();

            return view('dashboard.products.index', [ // Truyen du lieu sang view
                'paginatedProducts' => $products->items(), //'paginatedProducts' chi chua products tren trang hien tai
                'products' => $allProducts, // 'products' chua tat ca products
                'categories' => $categories, // 'categories' chua tat ca categories
                'pagination' => $products, // 'pagination' chua thong tin phan trang (tong so trang, trang hien tai, so luong tren moi trang, v.v.)
            ]);

        } catch (\Exception $e) {
            return view('dashboard.products.index', [
                'paginatedProducts' => [],
                'products' => [],
                'categories' => [],
                'error' => 'Lỗi khi tải danh sách sản phẩm: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        try {
            // Lấy danh sách categories cho dropdown
            $categories = Category::all();

            return view('dashboard.products.create', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không thể tải form tạo sản phẩm: '.$e->getMessage());
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'image_url' => 'nullable|url',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        try {
            // Tạo product mới sử dụng Eloquent
            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
                'stock_quantity' => $request->stock_quantity ?? 0,
            ]);

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm đã được tạo thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.create')
                ->with('error', 'Lỗi khi tạo sản phẩm: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        try {
            // Lấy product với relationship category
            $product = Product::with('category')->findOrFail($id);

            return view('dashboard.products.show', compact('product'));

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        try {
            // Lấy thông tin product
            $product = Product::findOrFail($id);

            // Lấy danh sách categories
            $categories = Category::all();

            return view('dashboard.products.edit', compact('product', 'categories'));

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|exists:categories,category_id',
            'image_url' => 'nullable|url',
            'stock_quantity' => 'nullable|integer|min:0',
        ]);

        try {
            // Tìm và cập nhật product
            $product = Product::findOrFail($id);

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'category_id' => $request->category_id,
                'image_url' => $request->image_url,
                'stock_quantity' => $request->stock_quantity ?? $product->stock_quantity,
            ]);

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm đã được cập nhật thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.edit', $id)
                ->with('error', 'Lỗi khi cập nhật sản phẩm: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        try {
            // Tìm và xóa product
            $product = Product::findOrFail($id);
            $product->delete();

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm đã được xóa thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Lỗi khi xóa sản phẩm: '.$e->getMessage());
        }
    }
}
