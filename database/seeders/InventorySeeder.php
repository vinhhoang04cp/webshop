<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();
        $faker = \Faker\Factory::create();
        
        foreach ($products as $product) {
            // Tạo dữ liệu inventory đơn giản với các cột có sẵn
            $stockIn = $faker->numberBetween(0, 20);
            $stockOut = $faker->numberBetween(0, 10);
            
            Inventory::create([
                'product_id' => $product->product_id,
                'stock_in' => $stockIn,
                'stock_out' => $stockOut,
                'current_stock' => $product->stock_quantity,
                'updated_at' => now(),
            ]);
        }
    }
}
