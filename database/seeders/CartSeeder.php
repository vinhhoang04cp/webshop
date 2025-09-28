<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::limit(15)->get(); // Lấy 15 users đầu tiên
        $faker = \Faker\Factory::create();

        foreach ($users as $user) {
            Cart::create([
                'user_id' => $user->id,
                'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now(),
            ]);
        }
    }
}
