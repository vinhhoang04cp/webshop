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
        $users = User::all();
        $faker = \Faker\Factory::create('vi_VN');
        $statuses = ['pending', 'paid', 'shipped', 'completed', 'cancelled'];

        // Tạo 30 orders
        for ($i = 0; $i < 30; $i++) {
            $randomUser = $users->random();
            $orderDate = $faker->dateTimeBetween('-6 months', 'now');
            $status = $faker->randomElement($statuses);

            Order::create([
                'user_id' => $randomUser->id,
                'order_date' => $orderDate,
                'status' => $status,
                'total_amount' => 0, // Sẽ được cập nhật sau khi thêm order items
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);
        }
    }
}
