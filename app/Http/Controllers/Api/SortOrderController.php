<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SortOrderController extends Controller
{
    /**
     * Cập nhật thứ tự sắp xếp của sản phẩm
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProductOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,product_id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        \DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                \App\Models\Product::where('product_id', $item['product_id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }
            \DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Product sort order updated successfully',
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update product sort order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật thứ tự sắp xếp của danh mục
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCategoryOrder(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.category_id' => 'required|exists:categories,category_id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        \DB::beginTransaction();
        try {
            foreach ($request->items as $item) {
                \App\Models\Category::where('category_id', $item['category_id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }
            \DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Category sort order updated successfully',
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to update category sort order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
