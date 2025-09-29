<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $orders = Order::all();
        $products = Product::all();
        $faker = \Faker\Factory::create();

        foreach ($orders as $order) {
            $totalAmount = 0;
            // Tạo 1-5 items cho mỗi order
            $itemCount = $faker->numberBetween(1, 5);
            $usedProducts = [];

            for ($i = 0; $i < $itemCount; $i++) {
                // Đảm bảo không trùng sản phẩm trong cùng order
                $availableProducts = $products->whereNotIn('product_id', $usedProducts);
                if ($availableProducts->count() > 0) {
                    $randomProduct = $availableProducts->random();
                    $usedProducts[] = $randomProduct->product_id;

                    $quantity = $faker->numberBetween(1, 3);
                    $unitPrice = $randomProduct->price;
                    $subtotal = $quantity * $unitPrice;
                    $totalAmount += $subtotal;

                    OrderItem::create([
                        'order_id' => $order->order_id,
                        'product_id' => $randomProduct->product_id,
                        'quantity' => $quantity,
                        'price' => $unitPrice, // Chỉ sử dụng price, không có unit_price và subtotal
                        'created_at' => $order->order_date,
                        'updated_at' => $order->order_date,
                    ]);
                }
            }

            // Cập nhật tổng tiền của order
            $order->update(['total_amount' => $totalAmount]);
        }
    }
}
