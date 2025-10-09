<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for admin UI.
     */
    public function index(Request $request)
    {
        try {
            // Lấy danh sách categories với search
            $query = Category::query();
            
            // Nếu có search, filter dữ liệu
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where('name', 'LIKE', "%{$searchTerm}%");
            }
            
            // Pagination
            $perPage = 10;
            $categories = $query->paginate($perPage);
            
            // Lấy tất cả categories để truyền vào view (nếu cần)
            $allCategories = Category::all();
            
            return view('dashboard.categories.index', compact('categories', 'allCategories'));
            
        } catch (\Exception $e) {
            return view('dashboard.categories.index', [
                'categories' => collect()->paginate(10),
                'allCategories' => collect(),
                'error' => 'Lỗi khi tải danh sách danh mục: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        try {
            // Tạo category mới sử dụng Eloquent
            Category::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được tạo thành công!');
                
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tạo danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:categories,name,' . $id . ',category_id',
            'description' => 'nullable|string',
        ]);

        try {
            // Tìm và cập nhật category
            $category = Category::findOrFail($id);
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được cập nhật thành công!');
                
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi cập nhật danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        try {
            // Tìm và xóa category
            $category = Category::findOrFail($id);
            $category->delete();

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được xóa thành công!');
                
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi xóa danh mục: ' . $e->getMessage());
        }
    }
}
