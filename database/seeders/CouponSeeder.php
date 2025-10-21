<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Chào mừng khách hàng mới',
                'description' => 'Giảm giá 10% cho khách hàng mới đăng ký',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_order_amount' => 100000,
                'max_discount_amount' => 50000,
                'usage_limit' => 100,
                'used_count' => 5,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SALE50K',
                'name' => 'Giảm 50K cho đơn từ 500K',
                'description' => 'Áp dụng cho tất cả sản phẩm, giảm 50.000 VND cho đơn hàng từ 500.000 VND',
                'discount_type' => 'fixed',
                'discount_value' => 50000.00,
                'min_order_amount' => 500000,
                'max_discount_amount' => null,
                'usage_limit' => 500,
                'used_count' => 23,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(20),
                'is_active' => true,
            ],
            [
                'code' => 'BLACKFRIDAY',
                'name' => 'Black Friday - Giảm 30%',
                'description' => 'Ưu đãi đặc biệt Black Friday - Giảm 30% tối đa 200K',
                'discount_type' => 'percentage',
                'discount_value' => 30.00,
                'min_order_amount' => 200000,
                'max_discount_amount' => 200000,
                'usage_limit' => null, // Không giới hạn
                'used_count' => 0,
                'start_date' => Carbon::parse('2025-11-29 00:00:00'),
                'end_date' => Carbon::parse('2025-12-01 23:59:59'),
                'is_active' => true,
            ],
            [
                'code' => 'EXPIRED20',
                'name' => 'Coupon đã hết hạn',
                'description' => 'Coupon này đã hết hạn để test chức năng',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_order_amount' => 100000,
                'max_discount_amount' => 100000,
                'usage_limit' => 50,
                'used_count' => 12,
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->subDays(5), // Đã hết hạn
                'is_active' => true,
            ],
            [
                'code' => 'DISABLED15',
                'name' => 'Coupon đã vô hiệu hóa',
                'description' => 'Coupon này đã bị vô hiệu hóa để test chức năng',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'min_order_amount' => 150000,
                'max_discount_amount' => 75000,
                'usage_limit' => 100,
                'used_count' => 8,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(30),
                'is_active' => false, // Đã vô hiệu hóa
            ],
            [
                'code' => 'FREESHIP',
                'name' => 'Miễn phí vận chuyển',
                'description' => 'Giảm 25.000 VND phí vận chuyển cho đơn hàng từ 300K',
                'discount_type' => 'fixed',
                'discount_value' => 25000.00,
                'min_order_amount' => 300000,
                'max_discount_amount' => null,
                'usage_limit' => 200,
                'used_count' => 45,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addWeeks(2),
                'is_active' => true,
            ],
            [
                'code' => 'MAXUSED',
                'name' => 'Coupon đã hết lượt sử dụng',
                'description' => 'Coupon này đã đạt giới hạn sử dụng để test chức năng',
                'discount_type' => 'percentage',
                'discount_value' => 25.00,
                'min_order_amount' => 200000,
                'max_discount_amount' => 150000,
                'usage_limit' => 10,
                'used_count' => 10, // Đã đạt giới hạn
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->addDays(10),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
