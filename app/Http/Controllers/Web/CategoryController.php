<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Hiển thị danh sách danh mục với tính năng tìm kiếm và phân trang
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }

            $perPage = 10;
            $categories = $query->paginate($perPage);
            $allCategories = Category::all();

            return view('dashboard.categories.index', compact('categories', 'allCategories'));

        } catch (\Exception $e) {
            return view('dashboard.categories.index', [
                'categories' => collect()->paginate(10),
                'allCategories' => collect(),
                'error' => 'Lỗi khi tải danh sách danh mục: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Hiển thị form tạo danh mục mới
     */
    public function create()
    {
        return view('dashboard.categories.create');
    }

    /**
     * Lưu danh mục mới vào database
     */
    public function store(CategoryRequest $request)
    {
        try {
            Category::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được tạo thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tạo danh mục: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết danh mục và danh sách sản phẩm
     */
    public function show($id)
    {
        try {
            $category = Category::with('products')->findOrFail($id);

            return view('dashboard.categories.show', compact('category'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tải chi tiết danh mục: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa danh mục
     */
    public function edit($id)
    {
        try {
            $category = Category::findOrFail($id);

            return view('dashboard.categories.edit', compact('category'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tải form chỉnh sửa: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin danh mục
     */
    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được cập nhật thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi cập nhật danh mục: '.$e->getMessage());
        }
    }

    /**
     * Xóa danh mục khỏi database
     *
     * Lưu ý: Cần kiểm tra ràng buộc với sản phẩm trước khi xóa
     */
    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được xóa thành công!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi xóa danh mục: '.$e->getMessage());
        }
    }
}
