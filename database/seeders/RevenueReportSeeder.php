<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RevenueReport;

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
