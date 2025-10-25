<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Hiển thị danh sách sản phẩm cho admin
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['search']);
            $result = $this->productService->getProductsForAdmin($filters, 12);
            $categories = $this->productService->getAllCategories();

            return view('dashboard.products.index', [
                'paginatedProducts' => $result['paginated']->items(),
                'products' => $result['all'],
                'categories' => $categories,
                'pagination' => $result['paginated'],
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
     * Hiển thị form tạo sản phẩm mới
     */
    public function create()
    {
        try {
            $categories = $this->productService->getAllCategories();

            return view('dashboard.products.create', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không thể tải form tạo sản phẩm: '.$e->getMessage());
        }
    }

    /**
     * Lưu sản phẩm mới vào database
     */
    public function store(ProductRequest $request)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $this->productService->createProduct($data);

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm và tồn kho đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.create')
                ->with('error', 'Lỗi khi tạo sản phẩm: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Hiển thị chi tiết sản phẩm cho admin
     */
    public function show($id)
    {
        try {
            $product = $this->productService->getProductWithDetails($id);

            return view('dashboard.products.show', compact('product'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     */
    public function edit($id)
    {
        try {
            $product = $this->productService->getProductWithDetails($id);
            $categories = $this->productService->getAllCategories();

            return view('dashboard.products.edit', compact('product', 'categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không tìm thấy sản phẩm hoặc lỗi: '.$e->getMessage());
        }
    }

    /**
     * Cập nhật thông tin sản phẩm
     */
    public function update(ProductRequest $request, $id)
    {
        try {
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image');
            }

            $this->productService->updateProduct($id, $data);

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm và tồn kho đã được cập nhật thành công!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.edit', $id)
                ->with('error', 'Lỗi khi cập nhật sản phẩm: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Xóa sản phẩm khỏi database
     */
    public function destroy($id)
    {
        try {
            $this->productService->deleteProduct($id);

            return redirect()->route('dashboard.products.index')
                ->with('success', 'Sản phẩm và tồn kho đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Lỗi khi xóa sản phẩm: '.$e->getMessage());
        }
    }
}
