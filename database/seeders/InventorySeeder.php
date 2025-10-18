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
            // Tạo dữ liệu inventory với logic đúng: current_stock = stock_in - stock_out
            // Vì OrderSeeder chưa trừ stock, nên stock_out = 0
            // current_stock phải bằng stock_quantity của product
            $stockIn = $product->stock_quantity; // Nhập kho = tồn kho hiện tại
            $stockOut = 0; // Chưa có đơn hàng nào được xử lý (seeder tạo order không trừ stock)

            Inventory::updateOrCreate(
                ['product_id' => $product->product_id],
                [
                    'stock_in' => $stockIn,
                    'stock_out' => $stockOut,
                    'current_stock' => $product->stock_quantity, // current_stock = stock_in - stock_out
                    'updated_at' => now(),
                ]
            );
        }
    }
}
