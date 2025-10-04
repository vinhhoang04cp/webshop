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
    public function index(Request $request) // (Request $request) la tham so truyen vao lay du lieu tu request
    {
        $query = Category::query(); // $query la bien de thuc hien query den Bang Category thong qua model
        if ($request->has('name')) { // neu request truyen len co name
            $query->where('name', 'LIKE', '%'.$request->get('name').'%'); // => query den name
        }

        if ($request->has('description')) { // neu request truyen len co description
            $query->where('description', 'LIKE', '%'.$request->get('description').'%'); // => query den description
        }
        // Neu du lieu lon, khuyen nghi paginate
        $categories = $query->paginate(15); // Phan trang, moi trang

        return new CategoryCollection($categories); // tra ve CategoryCollection
    }

    /**
     * Store a newly created category in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request) // tham so truyen vao la CategoryRequest de validate , duoc validate truoc khi vao controller
    {
        $category = Category::create([ // $bien category de tao moi 1 category
            'name' => $request->name,  // lay tu request, tao name
            'description' => $request->description, // lay tu request, tao description
        ]);

        $category = $category->fresh();  // Tai lai doi tuong category de lay thong tin moi nhat

        return (new CategoryResource($category)) // tra ve CategoryResource
            ->additional([ // them cac thong tin khac vao json tra ve
                'status' => true,
                'message' => 'Category created successfully',
            ])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified category.
     *
     * @param  int  $id  //tham so truyen vao la id cua category can lay
     * @return \Illuminate\Http\JsonResponse // tra ve dang json
     */
    public function show($id)
    {
        $category = Category::find($id); // $bien category de tim kiem category theo id

        if (! $category) { // neu khong tim thay category
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        return (new CategoryResource($category)) // tra ve CategoryResource
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
        $category = Category::find($id); // Tim category can cap nhat theo id, $category la bien chua category can tim

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
        $category = Category::find($id); // Tim category can xoa theo id, $category la bien chua category can tim

        if (! $category) { // neu khong tim thay category
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        if ($category->products->count() > 0) { // neu category con san pham lien quan
            return response()->json([
                'status' => false,
                'message' => 'Cannot delete category with associated products',
            ], 400);
        }

        $category->delete(); // Xoa category

        return response()->json([ // tra ve json thong bao
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
