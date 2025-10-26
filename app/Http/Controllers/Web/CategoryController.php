<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search']);
            $result = $this->categoryService->getCategoriesForAdmin($filters, 10);

            return view('dashboard.categories.index', [
                'categories' => $result['paginated'],
                'allCategories' => $result['all'],
            ]);
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
            $this->categoryService->createCategory($request->validated());

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
            $category = $this->categoryService->getCategoryWithProducts($id);

            return view('dashboard.categories.show', compact('category'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tải chi tiết danh mục: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id);

            return view('dashboard.categories.edit', compact('category'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tải form chỉnh sửa: '.$e->getMessage());
        }
    }

    public function update(CategoryRequest $request, $id)
    {
        try {
            $this->categoryService->updateCategory($id, $request->validated());

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
            $this->categoryService->deleteCategory($id);

            return redirect()->route('dashboard.categories.index')
                ->with('success', 'Danh mục đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi xóa danh mục: '.$e->getMessage());
        }
    }
}
