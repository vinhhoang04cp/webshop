<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Services\CategoryService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Hiển thị danh sách các danh mục
     */
    public function index(Request $request)
    {
        $filters = $request->only(['name', 'description']);
        $categories = $this->categoryService->getCategories($filters);

        return new CategoryCollection($categories);
    }

    /**
     * Lưu danh mục mới được tạo
     */
    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategoryWithFresh($request->validated());

        return CategoryResource::created($category);
    }

    /**
     * Hiển thị danh mục theo ID
     * Query params: ?with_products=1 để bao gồm sản phẩm
     */
    public function show(Request $request, $id)
    {
        try {
            $withProducts = $request->query('with_products', false);
            $category = $this->categoryService->findCategoryOrFail($id, $withProducts);

            return CategoryResource::retrieved($category);
        } catch (ModelNotFoundException $e) {
            return ErrorResource::notFound('Category not found');
        }
    }

    /**
     * Cập nhật danh mục theo ID
     */
    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = $this->categoryService->updateCategoryWithFresh($id, $request->validated());

            return CategoryResource::updated($category);
        } catch (ModelNotFoundException $e) {
            return ErrorResource::notFound('Category not found');
        }
    }

    /**
     * Xóa danh mục theo ID
     */
    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategoryWithValidation($id);

            return SuccessResource::deleted('Category deleted successfully');
        } catch (ModelNotFoundException $e) {
            return ErrorResource::notFound('Category not found');
        } catch (Exception $e) {
            return ErrorResource::badRequest($e->getMessage());
        }
    }
}
