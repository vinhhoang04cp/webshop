<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $roles = Role::all();
        $faker = \Faker\Factory::create();

        foreach ($users as $index => $user) {
            $roleAssignment = [];

            // Gán role theo user email
            if (strpos($user->email, 'admin') !== false) {
                $adminRole = $roles->where('role_name', 'admin')->first();
                $roleAssignment[] = $adminRole->role_id;
            } elseif (strpos($user->email, 'manager') !== false) {
                $managerRole = $roles->where('role_name', 'manager')->first();
                $roleAssignment[] = $managerRole->role_id;
            } else {
                // Tất cả user khác đều là customer
                $customerRole = $roles->where('role_name', 'customer')->first();
                $roleAssignment[] = $customerRole->role_id;
            }

            // Tạo user role assignments
            foreach ($roleAssignment as $roleId) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                    'assigned_at' => $faker->dateTimeBetween('-1 year', 'now'),
                ]);
            }
        }
    }
}
