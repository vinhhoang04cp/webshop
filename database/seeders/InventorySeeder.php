<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Inventory;

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
