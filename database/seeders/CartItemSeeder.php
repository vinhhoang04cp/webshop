<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Cart;
use App\Models\Product;
use App\Models\CartItem;

class CartItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cart = Cart::first();
        $product = Product::first();

        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
        ]);
    }
}
