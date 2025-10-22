<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy một vài sản phẩm để test
        $products = Product::limit(3)->get();

        // Coupon 1: Giảm 10% cho tất cả sản phẩm
        Coupon::create([
            'code' => 'WELCOME10',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'product_id' => null, // null = áp dụng cho tất cả
            'start_date' => now(),
            'end_date' => now()->addDays(30),
            'is_active' => true,
        ]);

        // Coupon 2: Giảm 50,000 VND cho tất cả sản phẩm
        Coupon::create([
            'code' => 'SAVE50K',
            'discount_type' => 'fixed',
            'discount_value' => 50000.00,
            'product_id' => null,
            'start_date' => now(),
            'end_date' => now()->addDays(60),
            'is_active' => true,
        ]);

        // Coupon 3: Giảm 20% cho sản phẩm cụ thể (nếu có sản phẩm)
        if ($products->count() > 0) {
            Coupon::create([
                'code' => 'PRODUCT20',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'product_id' => $products->first()->product_id,
                'start_date' => now(),
                'end_date' => now()->addDays(15),
                'is_active' => true,
            ]);
        }

        // Coupon 4: Giảm 100,000 VND cho sản phẩm cụ thể
        if ($products->count() > 1) {
            Coupon::create([
                'code' => 'SPECIAL100K',
                'discount_type' => 'fixed',
                'discount_value' => 100000.00,
                'product_id' => $products->get(1)->product_id,
                'start_date' => now(),
                'end_date' => now()->addDays(45),
                'is_active' => true,
            ]);
        }

        // Coupon 5: Coupon không hoạt động (để test)
        Coupon::create([
            'code' => 'EXPIRED',
            'discount_type' => 'percentage',
            'discount_value' => 15.00,
            'product_id' => null,
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1), // Đã hết hạn
            'is_active' => false,
        ]);
    }
}
