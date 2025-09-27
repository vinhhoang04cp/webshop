<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::with('products')->get();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     *
     * @param  \App\Http\Requests\CategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        try {
            $category = Category::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()
                ->route('categories.index')
                ->with('success', 'Danh mục "' . $category->name . '" đã được tạo thành công!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $category = Category::with(['products', 'products.inventory'])->find($id);

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Không tìm thấy danh mục với ID: ' . $id);
        }

        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Không tìm thấy danh mục với ID: ' . $id);
        }

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     *
     * @param  \App\Http\Requests\CategoryRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Không tìm thấy danh mục với ID: ' . $id);
        }

        try {
            $oldName = $category->name;
            
            $category->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            $message = $oldName !== $request->name 
                ? 'Danh mục đã được cập nhật thành công! (Đã đổi tên từ "' . $oldName . '" thành "' . $request->name . '")'
                : 'Danh mục "' . $category->name . '" đã được cập nhật thành công!';

            return redirect()
                ->route('categories.show', $category->category_id)
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật danh mục: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified category from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $category = Category::with('products')->find($id);

        if (!$category) {
            return redirect()
                ->route('categories.index')
                ->with('error', 'Không tìm thấy danh mục với ID: ' . $id);
        }

        // Check if category has products
        if ($category->products->count() > 0) {
            return redirect()
                ->back()
                ->with('error', 'Không thể xóa danh mục "' . $category->name . '" vì còn có ' . $category->products->count() . ' sản phẩm. Hãy di chuyển hoặc xóa các sản phẩm trước.');
        }

        try {
            $categoryName = $category->name;
            $category->delete();

            return redirect()
                ->route('categories.index')
                ->with('success', 'Danh mục "' . $categoryName . '" đã được xóa thành công!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Có lỗi xảy ra khi xóa danh mục: ' . $e->getMessage());
        }
    }
}
