<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create(['name' => 'Electronics', 'description' => 'Electronic devices']);
        Category::create(['name' => 'Books', 'description' => 'Various kinds of books']);
    }
}
