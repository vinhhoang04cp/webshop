<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Display the specified product.
     */
    public function show(string $id)
    {
        return view('products.show', compact('id'));
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(string $id)
    {
        return view('products.edit', compact('id'));
    }
}
