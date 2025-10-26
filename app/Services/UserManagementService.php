<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\DB;

class UserManagementService
{
    /**
     * Lấy danh sách users với tìm kiếm và phân trang
     */
    public function getUsersForAdmin($searchTerm = null, $perPage = 15)
    {
        $query = User::with('roles');

        if ($searchTerm) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('email', 'like', "%{$searchTerm}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Lấy chi tiết user với relationships
     */
    public function getUserDetail($userId)
    {
        return User::with('roles', 'orders')->findOrFail($userId);
    }

    /**
     * Cập nhật roles của user
     */
    public function updateUserRoles($userId, $roleIds)
    {
        DB::beginTransaction();

        try {
            // Xóa tất cả roles cũ
            UserRole::where('user_id', $userId)->delete();

            // Thêm roles mới
            if (! empty($roleIds)) {
                foreach ($roleIds as $roleId) {
                    UserRole::create([
                        'user_id' => $userId,
                        'role_id' => $roleId,
                        'assigned_at' => now(),
                    ]);
                }
            }

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Gán role cho user
     */
    public function assignRole($userId, $roleId)
    {
        // Kiểm tra user đã có role chưa
        $exists = UserRole::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();

        if ($exists) {
            throw new \Exception('User đã có role này rồi!');
        }

        UserRole::create([
            'user_id' => $userId,
            'role_id' => $roleId,
            'assigned_at' => now(),
        ]);

        return Role::find($roleId);
    }

    /**
     * Gỡ bỏ role khỏi user
     */
    public function removeRole($userId, $roleId)
    {
        $userRole = UserRole::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->first();

        if (! $userRole) {
            throw new \Exception('User không có role này!');
        }

        $userRole->delete();

        return true;
    }

    /**
     * Xóa user
     */
    public function deleteUser($userId, $currentUserId)
    {
        // Không cho phép xóa chính mình
        if ($userId === $currentUserId) {
            throw new \Exception('Bạn không thể xóa tài khoản của chính mình!');
        }

        DB::beginTransaction();

        try {
            $user = User::findOrFail($userId);

            // Xóa các roles của user
            UserRole::where('user_id', $userId)->delete();

            // Xóa user
            $userName = $user->name;
            $user->delete();

            DB::commit();

            return $userName;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Lấy danh sách roles với số lượng users
     */
    public function getRolesWithUserCount()
    {
        return Role::withCount('users')->get();
    }

    /**
     * Lấy thống kê users theo roles
     */
    public function getUserStatsByRole()
    {
        return [
            'total_users' => User::count(),
            'admin_count' => User::whereHas('roles', function ($q) {
                $q->where('role_name', 'admin');
            })->count(),
            'manager_count' => User::whereHas('roles', function ($q) {
                $q->where('role_name', 'manager');
            })->count(),
            'user_count' => User::whereHas('roles', function ($q) {
                $q->where('role_name', 'user');
            })->count(),
        ];
    }
}
