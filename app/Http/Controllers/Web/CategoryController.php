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

    // hien thi danh sach danh muc
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search']); // $request->only de tim kiem
            $result = $this->categoryService->getCategoriesForAdmin($filters, 10);
            // ket qua tra ra bang cach goi den phuong thuc trong services

            return view('dashboard.categories.index', [ // tra ve view
                'categories' => $result['paginated'], // tra ve paginated lay tu $result, result lay tu services
                'allCategories' => $result['all'], // tra ve all lay tu $result, $result lay tu services
            ]);
        } catch (\Exception $e) { // bat loi ngoai le neu co
            return view('dashboard.categories.index', [
                'categories' => collect()->paginate(10),
                'allCategories' => collect(),
                'error' => 'Lỗi khi tải danh sách danh mục: '.$e->getMessage(),
            ]);
        }
    }

    public function create()
    {
        return view('dashboard.categories.create'); // tra ve view tao danh muc
    }

    public function store(CategoryRequest $request) // Request de validate du lieu, CategoryRequest de validate theo quy dinh
    {
        try {
            $this->categoryService->createCategory($request->validated());
            // Tra ve trang thai thanh cong

            return redirect()->route('dashboard.categories.index') // trao ve route danh sach danh muc
                ->with('success', 'Danh mục đã được tạo thành công!'); // thong bao thanh cong
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tạo danh mục: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $category = $this->categoryService->getCategoryWithProducts($id); // lay danh muc voi san pham tu service

            return view('dashboard.categories.show', compact('category')); // tra ve view chi tiet danh muc
        } catch (\Exception $e) {
            return redirect()->route('dashboard.categories.index')
                ->with('error', 'Lỗi khi tải chi tiết danh mục: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        try {
            $category = $this->categoryService->getCategoryById($id); // lay danh muc theo ID tu service

            return view('dashboard.categories.edit', compact('category')); // tra ve view chinh sua danh muc
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
