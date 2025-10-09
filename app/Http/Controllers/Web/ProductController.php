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
    public function index(Request $request)
    {
        try {
            // Tạo query builder cho products với relationship category
            $query = Product::with('category');

            // Nếu có search, filter dữ liệu
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%'.$searchTerm.'%')
                        ->orWhere('description', 'like', '%'.$searchTerm.'%');
                });
            }

            // Pagination
            $perPage = 12;
            $products = $query->paginate($perPage);

            // Lấy tất cả products cho search (nếu cần)
            $allProducts = Product::all();

            // Lấy danh sách categories cho dropdown
            $categories = Category::all();

            return view('dashboard.products.index', [
                'paginatedProducts' => $products->items(),
                'products' => $allProducts,
                'categories' => $categories,
                'pagination' => $products,
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
