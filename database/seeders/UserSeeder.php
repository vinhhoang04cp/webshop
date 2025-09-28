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
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@webshop.com',
            'password' => Hash::make('admin123'),
            'phone' => '0987654321',
            'address' => '123 Admin Street, Ha Noi',
        ]);

        // Tạo manager user
        User::factory()->create([
            'name' => 'Manager User',
            'email' => 'manager@webshop.com',
            'password' => Hash::make('manager123'),
            'phone' => '0876543210',
            'address' => '456 Manager Ave, Ho Chi Minh City',
        ]);

        // Tạo test user
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('test123'),
            'phone' => '1234567890',
            'address' => '123 Test Street',
        ]);

        // Tạo 17 customers khác
        User::factory(17)->create();
    }
}
