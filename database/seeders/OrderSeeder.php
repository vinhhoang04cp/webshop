<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        Order::create([
            'user_id' => $user->id,
            'order_date' => now(),
            'status' => 'pending',
            'total_amount' => 2401.00,
        ]);
    }
}
