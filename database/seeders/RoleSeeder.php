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
        Role::create(['role_name' => 'admin', 'role_display_name' => 'Administrator']);
        Role::create(['role_name' => 'customer', 'role_display_name' => 'Customer']);
    }
}
