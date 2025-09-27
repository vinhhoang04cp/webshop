<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Seeder;

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
