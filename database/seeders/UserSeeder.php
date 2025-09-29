<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo admin user
        User::updateOrCreate(
            ['email' => 'admin@webshop.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone' => '0987654321',
                'address' => '123 Admin Street, Ha Noi',
            ]
        );

        // Tạo manager user
        User::updateOrCreate(
            ['email' => 'manager@webshop.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('manager123'),
                'phone' => '0876543210',
                'address' => '456 Manager Ave, Ho Chi Minh City',
            ]
        );

        // Tạo test user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('test123'),
                'phone' => '1234567890',
                'address' => '123 Test Street',
            ]
        );

        // Tạo 17 customers khác (chỉ khi chưa có đủ users)
        $existingUsersCount = User::count();
        if ($existingUsersCount < 20) { // 3 users cố định + 17 random
            User::factory(20 - $existingUsersCount)->create();
        }
    }
}
