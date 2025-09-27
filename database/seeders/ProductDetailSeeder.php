<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductDetail;
use Illuminate\Database\Seeder;

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
