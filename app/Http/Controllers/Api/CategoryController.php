<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the categories.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['name', 'description']);
        $categories = $this->categoryService->getCategories($filters);

        return new CategoryCollection($categories);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CategoryRequest $request)
    {
        $category = $this->categoryService->createCategoryWithFresh($request->validated());

        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = $this->categoryService->findCategory($id);

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category retrieved successfully',
            ])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = $this->categoryService->findCategory($id);

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category = $this->categoryService->updateCategoryWithFresh($id, $request->validated());

        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category updated successfully',
            ]);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = $this->categoryService->findCategory($id);

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        if (! $this->categoryService->canDeleteCategory($category)) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete category with associated products',
            ], 400);
        }

        $this->categoryService->deleteCategory($id);

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
