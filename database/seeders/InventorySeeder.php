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
        foreach ($products as $product) {
            Inventory::create([
                'product_id' => $product->product_id,
                'current_stock' => $product->stock_quantity,
                'updated_at' => now(),
            ]);
        }
    }
}
