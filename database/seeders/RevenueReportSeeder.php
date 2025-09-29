<?php

namespace Database\Seeders;

use App\Models\RevenueReport;
use Illuminate\Database\Seeder;

class RevenueReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        // Tạo báo cáo doanh thu cho 12 tháng gần đây, mỗi tháng chỉ 1 báo cáo
        for ($i = 12; $i >= 1; $i--) {
            $date = now()->subMonths($i)->startOfMonth();
            $totalOrders = $faker->numberBetween(50, 200);
            $totalRevenue = $faker->numberBetween(50000000, 500000000); // 50M - 500M VND
            $totalProfit = $totalRevenue * $faker->randomFloat(2, 0.1, 0.3); // 10-30% profit

            // Sử dụng updateOrCreate để tránh duplicate entry
            RevenueReport::updateOrCreate(
                ['date' => $date->toDateString()], // Điều kiện tìm kiếm
                [ // Dữ liệu để tạo hoặc cập nhật
                    'total_orders' => $totalOrders,
                    'total_revenue' => $totalRevenue,
                    'total_profit' => $totalProfit,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]
            );
        }
    }
}
