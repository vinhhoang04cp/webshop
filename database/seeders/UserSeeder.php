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
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@webshop.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone' => '0987654321',
                'address' => '123 Admin Street, Ha Noi',
            ]
        );

        // Gán role admin cho user
        $adminRole = \App\Models\Role::where('role_name', 'admin')->first();
        if ($adminRole && ! $adminUser->roles()->where('user_roles.role_id', $adminRole->role_id)->exists()) {
            $adminUser->roles()->attach($adminRole->role_id, ['assigned_at' => now()]);
        }

        // Tạo manager user
        $managerUser = User::updateOrCreate(
            ['email' => 'manager@webshop.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('manager123'),
                'phone' => '0876543210',
                'address' => '456 Manager Ave, Ho Chi Minh City',
            ]
        );

        // Gán role manager cho user
        $managerRole = \App\Models\Role::where('role_name', 'manager')->first();
        if ($managerRole && ! $managerUser->roles()->where('user_roles.role_id', $managerRole->role_id)->exists()) {
            $managerUser->roles()->attach($managerRole->role_id, ['assigned_at' => now()]);
        }

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
