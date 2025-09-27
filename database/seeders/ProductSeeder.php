<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $electronics = Category::where('name', 'Electronics')->first();
        $books = Category::where('name', 'Books')->first();

        Product::create([
            'name' => 'Laptop',
            'description' => 'A powerful laptop',
            'price' => 1200.50,
            'category_id' => $electronics->category_id,
            'stock_quantity' => 50,
            'image_url' => 'http://example.com/laptop.jpg',
        ]);

        Product::create([
            'name' => 'Laravel Book',
            'description' => 'A book about Laravel',
            'price' => 45.99,
            'category_id' => $books->category_id,
            'stock_quantity' => 100,
            'image_url' => 'http://example.com/laravel-book.jpg',
        ]);
    }
}
