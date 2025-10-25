<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
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

    public function create()
    {
        return view('dashboard.categories.create');
    }

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
