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
        RevenueReport::create([
            'date' => now()->toDateString(),
            'total_orders' => 1,
            'total_revenue' => 2401.00,
            'total_profit' => 500.00, // Example profit
        ]);
    }
}
