<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo user admin mặc định
        $admin = User::updateOrCreate(
            ['email' => 'admin@webshop.com'],
            [
                'name' => 'Administrator',
                'email' => 'admin@webshop.com',
                'password' => Hash::make('admin123'),
                'phone' => '0123456789',
                'address' => 'Hà Nội, Việt Nam',
                'email_verified_at' => now(),
            ]
        );

        // Lấy role admin
        $adminRole = Role::where('role_name', 'admin')->first();

        if ($adminRole) {
            // Gán role admin cho user
            UserRole::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'role_id' => $adminRole->role_id
                ],
                [
                    'assigned_at' => now()
                ]
            );
        }

        $this->command->info('Created admin user: admin@webshop.com / admin123');
    }
}
