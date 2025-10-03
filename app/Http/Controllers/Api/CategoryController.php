<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection // Tra ve ve CategoryCollection
     */
    public function index(Request $request)
    {
        $query = Category::query()->orderBy('sort_order', 'asc');
        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%'.$request->get('name').'%');
        }

        if ($request->has('description')) {
            $query->where('description', 'LIKE', '%'.$request->get('description').'%');
        }
        // Neu du lieu lon, khuyen nghi paginate
        $categories = $query->paginate(15); // Phan trang, moi trang

        return new CategoryCollection($categories);
    }

    /**
     * Store a newly created category in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request)
    {
        // Xử lý sort_order: tự động tăng giá trị
        $maxSortOrder = Category::max('sort_order') ?? 0;
        
        $category = Category::create([
            'name' => $request->name,  // lay tu request
            'description' => $request->description,
            'sort_order' => $request->input('sort_order', $maxSortOrder + 10), // Tăng 10 để dễ sắp xếp giữa các phần tử
        ]);

        // Không cần reorderIds() nữa vì đã dùng sort_order
        
        $category = $category->fresh();  // Tai lai doi tuong category de lay thong tin moi nhat

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
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $category = Category::find($id);

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
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'sort_order' => $request->input('sort_order', $category->sort_order), // Giữ nguyên sort_order nếu không có trong request
        ]);

        // Không cần reorderIds() nữa vì đã dùng sort_order

        $category = $category->fresh();

        return (new CategoryResource($category))
            ->additional([
                'status' => true,
                'message' => 'Category updated successfully',
            ]);
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        if ($category->products->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete category with associated products',
            ], 400);
        }

        $category->delete();

        // Không cần gọi reorderIds() nữa vì đã dùng sort_order

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
