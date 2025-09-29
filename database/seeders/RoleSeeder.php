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
            ['role_name' => 'customer', 'role_display_name' => 'Customer'],
            ['role_name' => 'guest', 'role_display_name' => 'Guest User'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_name' => $role['role_name']],
                ['role_display_name' => $role['role_display_name']]
            );
        }
    }
}
