<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;

class OrderItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $order = Order::first();
        $product = Product::first();

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'price' => $product->price,
        ]);
    }
}
