<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['role_name' => 'admin', 'role_display_name' => 'Administrator'],
            ['role_name' => 'manager', 'role_display_name' => 'Manager'],
            ['role_name' => 'user', 'role_display_name' => 'User'],
            ['role_name' => 'customer', 'role_display_name' => 'Customer'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_name' => $role['role_name']],
                [
                    'role_display_name' => $role['role_display_name'],
                    'role_created_at' => now(),
                    'role_updated_at' => now()
                ]
            );
        }
    }
}
