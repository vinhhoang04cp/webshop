<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class UpdateCouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cập nhật hoặc tạo mới các coupon mẫu với đầy đủ thông tin

        Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'name' => 'Chào mừng khách hàng mới',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_order_amount' => 500000, // Đơn tối thiểu 500k
                'max_discount_amount' => 100000, // Giảm tối đa 100k
                'usage_limit' => 100,
                'used_count' => 0,
                'product_id' => null, // Áp dụng cho tất cả
                'start_date' => now(),
                'end_date' => now()->addMonths(3),
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'SALE50K'],
            [
                'name' => 'Giảm 50K cho đơn từ 1 triệu',
                'discount_type' => 'fixed',
                'discount_value' => 50000,
                'min_order_amount' => 1000000,
                'max_discount_amount' => null,
                'usage_limit' => 50,
                'used_count' => 0,
                'product_id' => null,
                'start_date' => now(),
                'end_date' => now()->addMonths(1),
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'FLASHSALE'],
            [
                'name' => 'Flash Sale 20%',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'min_order_amount' => 300000,
                'max_discount_amount' => 200000,
                'usage_limit' => 200,
                'used_count' => 0,
                'product_id' => null,
                'start_date' => now(),
                'end_date' => now()->addDays(7),
                'is_active' => true,
            ]
        );

        Coupon::updateOrCreate(
            ['code' => 'VIP500'],
            [
                'name' => 'Giảm 500K cho VIP',
                'discount_type' => 'fixed',
                'discount_value' => 500000,
                'min_order_amount' => 5000000,
                'max_discount_amount' => null,
                'usage_limit' => 10,
                'used_count' => 0,
                'product_id' => null,
                'start_date' => now(),
                'end_date' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

        $this->command->info('Đã tạo/cập nhật 4 coupon mẫu!');
    }
}
