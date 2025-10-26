<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    /**
     * Lấy danh sách categories với phân trang và tìm kiếm
     */
    public function getCategoriesForAdmin(array $filters = [], int $perPage = 10)
    {
        $query = Category::query();

        // Tìm kiếm theo tên
        if (! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where('name', 'LIKE', "%{$searchTerm}%");
        }

        return [
            'paginated' => $query->paginate($perPage),
            'all' => Category::all(),
        ];
    }

    /**
     * Lấy chi tiết category kèm products
     */
    public function getCategoryWithProducts($categoryId)
    {
        return Category::with('products')->findOrFail($categoryId);
    }

    /**
     * Lấy category theo ID
     */
    public function getCategoryById($categoryId)
    {
        return Category::findOrFail($categoryId);
    }

    /**
     * Tạo category mới
     */
    public function createCategory(array $data)
    {
        return Category::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    /**
     * Cập nhật category
     */
    public function updateCategory($categoryId, array $data)
    {
        $category = Category::findOrFail($categoryId);

        $category->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        return $category;
    }

    /**
     * Xóa category
     */
    public function deleteCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        $category->delete();

        return true;
    }

    /**
     * Lấy tất cả categories
     */
    public function getAllCategories()
    {
        return Category::all();
    }

    /**
     * Lấy categories với số lượng sản phẩm
     */
    public function getCategoriesWithProductCount()
    {
        return Category::withCount('products')->get();
    }

    /**
     * Get categories with filters (for API)
     */
    public function getCategories(array $filters = [], int $perPage = 15)
    {
        $query = Category::query();

        if (isset($filters['name'])) {
            $query->where('name', 'LIKE', '%'.$filters['name'].'%');
        }

        if (isset($filters['description'])) {
            $query->where('description', 'LIKE', '%'.$filters['description'].'%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find category by ID (nullable)
     */
    public function findCategory($categoryId)
    {
        return Category::find($categoryId);
    }

    /**
     * Check if category can be deleted
     */
    public function canDeleteCategory($category)
    {
        return $category->products->count() === 0;
    }

    /**
     * Create category and return fresh instance
     */
    public function createCategoryWithFresh(array $data)
    {
        $category = $this->createCategory($data);
        
        return $category->fresh();
    }

    /**
     * Update category and return fresh instance
     */
    public function updateCategoryWithFresh($categoryId, array $data)
    {
        $category = $this->updateCategory($categoryId, $data);
        
        return $category->fresh();
    }
}
