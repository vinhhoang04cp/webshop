<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $role = Role::where('role_name', 'admin')->first();

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->role_id,
            'assigned_at' => now(),
        ]);
    }
}
