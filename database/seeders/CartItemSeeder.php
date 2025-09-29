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
        $carts = Cart::all();
        $products = Product::all();
        $faker = \Faker\Factory::create();

        foreach ($carts as $cart) {
            // Tạo 1-4 items cho mỗi giỏ hàng
            $itemCount = $faker->numberBetween(1, 4);
            $usedProducts = [];

            for ($i = 0; $i < $itemCount; $i++) {
                // Đảm bảo không trùng sản phẩm trong cùng giỏ hàng
                $availableProducts = $products->whereNotIn('product_id', $usedProducts);
                if ($availableProducts->count() > 0) {
                    $randomProduct = $availableProducts->random();
                    $usedProducts[] = $randomProduct->product_id;

                    CartItem::updateOrCreate(
                        [
                            'cart_id' => $cart->cart_id,
                            'product_id' => $randomProduct->product_id,
                        ],
                        [
                            'quantity' => $faker->numberBetween(1, 3),
                            'created_at' => $faker->dateTimeBetween($cart->created_at, 'now'),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }
}
