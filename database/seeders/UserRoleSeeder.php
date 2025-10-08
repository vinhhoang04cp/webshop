<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

/**
 * UserRoleSeeder
 *
 * Seeder này tạo dữ liệu mẫu cho bảng user_roles
 * Gán role cho các user dựa trên email pattern
 * Giúp test và development có dữ liệu role sẵn sàng
 */
class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Chạy seeder để tạo dữ liệu mẫu user-role assignments
     */
    public function run(): void
    {
        // Bước 1: Lấy tất cả users và roles từ database
        // all() - lấy toàn bộ records từ bảng users và roles
        // Cần chạy UserSeeder và RoleSeeder trước seeder này
        $users = User::all();
        $roles = Role::all();

        // Tạo instance Faker để generate dữ liệu ngẫu nhiên
        // Dùng để tạo thời gian assigned_at ngẫu nhiên
        $faker = \Faker\Factory::create();

        // Bước 2: Lặp qua từng user để gán role phù hợp
        foreach ($users as $index => $user) {
            // Khởi tạo mảng chứa các role_id sẽ được gán cho user này
            $roleAssignment = [];

            // Bước 3: Gán role dựa trên email pattern
            // Kiểm tra email có chứa từ khóa nào để gán role tương ứng

            if (strpos($user->email, 'admin') !== false) {
                // Nếu email chứa 'admin' (vd: admin@example.com, admin1@test.com)
                // where('role_name', 'admin') - tìm role có tên là 'admin'
                // first() - lấy record đầu tiên tìm thấy
                $adminRole = $roles->where('role_name', 'admin')->first();
                $roleAssignment[] = $adminRole->role_id; // Thêm admin role_id vào mảng

            } elseif (strpos($user->email, 'manager') !== false) {
                // Nếu email chứa 'manager' (vd: manager@example.com)
                $managerRole = $roles->where('role_name', 'manager')->first();
                $roleAssignment[] = $managerRole->role_id; // Thêm manager role_id

            } else {
                // Tất cả user khác (email bình thường) đều được gán role customer
                // Đây là default role cho user thông thường
                $customerRole = $roles->where('role_name', 'customer')->first();
                $roleAssignment[] = $customerRole->role_id; // Thêm customer role_id
            }

            // Bước 4: Tạo user role assignments trong database
            // Lặp qua các role_id đã được chọn cho user này
            foreach ($roleAssignment as $roleId) {
                // firstOrCreate() - tìm record với điều kiện, nếu không có thì tạo mới
                // Điều kiện tìm: user_id và role_id khớp
                // Dữ liệu tạo mới: thêm assigned_at timestamp
                UserRole::firstOrCreate(
                    [
                        // Điều kiện WHERE để tìm record
                        'user_id' => $user->id,   // ID của user hiện tại
                        'role_id' => $roleId,     // ID của role được gán
                    ],
                    [
                        // Dữ liệu sẽ được insert nếu record chưa tồn tại
                        // dateTimeBetween() - tạo datetime ngẫu nhiên trong khoảng thời gian
                        'assigned_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    ]
                );
            }
        }
    }
}
