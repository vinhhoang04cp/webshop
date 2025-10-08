<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for admin UI.
     */
    public function index(Request $request)
    {
        $query = Category::query();
        
        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        $categories = $query->orderBy('name')->paginate(10);

        return view('dashboard.categories.index', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150|unique:categories,name',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Danh mục đã được tạo thành công!');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:150|unique:categories,name,' . $id . ',category_id',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Danh mục đã được cập nhật thành công!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('dashboard.categories.index')
            ->with('success', 'Danh mục đã được xóa thành công!');
    }
}
