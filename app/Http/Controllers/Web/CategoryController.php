<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories for admin UI.
     */
    public function index()
    {
        // For now load all categories; in real app paginate/search
        $categories = Category::orderBy('name')->get();

        return view('dashboard.categories.index', compact('categories'));
    }
}
