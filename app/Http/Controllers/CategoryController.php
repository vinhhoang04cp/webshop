<?php

namespace App\Http\Controllers;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        return view('categories.index');
    }

    /**
     * Show the form for creating a new category.
     */
    public function create()
    {
        return view('categories.create');
    }

    /**
     * Display the specified category.
     */
    public function show(string $id)
    {
        return view('categories.show', compact('id'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(string $id)
    {
        return view('categories.edit', compact('id'));
    }
}
