<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Resources\Json\ResourceCollection // Tra ve danh sach dang resource collection
     */
    public function index()
    {
        $categories = Category::all();  // Lay tat ca danh muc

        return new CategoryCollection($categories);  // Tra ve danh sach dang resource collection
    }

    /**
     * Store a newly created category in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request) 
    {
        $category = Category::create([
            'name' => $request->name,  // Su dung du lieu da duoc validate
            'description' => $request->description,  // Su dung du lieu da duoc validate
        ]);

        return (new CategoryResource($category))  // Tra ve danh muc da duoc tao dang resource
            ->additional([  // Them du lieu them vao phan hoi
                'status' => true, // Trang thai thanh cong
                'message' => 'Category created successfully',   // Success message
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
        // Tim danh muc theo ID
        // Neu khong tim thay, tra ve loi 404
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',  
            ], 404);
        }
        // Neu tim thay, tra ve danh muc dang resource
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
        // Tim danh muc theo ID
        $category = Category::find($id);
        // Neu khong tim thay, tra ve loi 404
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);
        // Tra ve danh muc da duoc cap nhat dang resource
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
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::find($id);
        // Tim danh muc theo ID
        // Neu khong tim thay, tra ve loi 404
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Kiem tra neu danh muc co san pham lien ket
        if ($category->products->count() > 0) {
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete category with associated products',
            ], 400);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
