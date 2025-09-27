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
     * @return \Illuminate\Http\Resources\Json\ResourceCollection
     */
    public function index()
    {
        $categories = Category::all();  // Fetch all categories

        return new CategoryCollection($categories);  // Return as a resource collection
    }

    /**
     * Store a newly created category in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create([
            'name' => $request->name,  // Use validated data
            'description' => $request->description,  // Use validated data
        ]);

        return (new CategoryResource($category))  // Return the created category as a resource
            ->additional([  // Add additional data to the response
                'status' => true, // Success status
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
        // Find the category by ID
        // If not found, return a 404 response   
        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',  
            ], 404);
        }
        // If found, return the category as a resource with additional data
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
        ]);

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

        if (! $category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found',
            ], 404);
        }

        // Check if the category has associated products
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
