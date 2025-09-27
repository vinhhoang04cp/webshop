<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

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
