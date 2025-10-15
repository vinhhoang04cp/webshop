<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo user customer mẫu
        $customer = User::updateOrCreate(
            ['email' => 'customer@webshop.com'],
            [
                'name' => 'Customer User',
                'password' => Hash::make('customer123'),
                'phone' => '0987654321',
                'address' => '456 Đường Khách Hàng, Quận 2, TP.HCM',
            ]
        );

        // Gán role customer
        $customerRole = Role::where('role_name', 'customer')->first();

        if ($customerRole) {
            // Gán role customer cho user
            UserRole::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'role_id' => $customerRole->role_id,
                ],
                [
                    'assigned_at' => now(),
                ]
            );
        }

        $this->command->info('Created customer user: customer@webshop.com / customer123');
    }
}
