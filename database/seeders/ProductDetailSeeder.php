<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductDetail;

class ProductDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $laptop = Product::where('name', 'Laptop')->first();

        ProductDetail::create([
            'product_id' => $laptop->product_id,
            'size' => '15 inch',
            'color' => 'Silver',
            'material' => 'Aluminum',
        ]);
    }
}
