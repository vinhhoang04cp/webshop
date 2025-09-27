<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

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
