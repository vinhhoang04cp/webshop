<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Hiển thị danh sách users
     */
    public function index()
    {
        $users = User::with('roles')->paginate(15);
        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Hiển thị chi tiết user
     */
    public function show(User $user)
    {
        $user->load('roles', 'orders');
        return view('dashboard.users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa quyền user
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();
        return view('dashboard.users.edit', compact('user', 'roles'));
    }

    /**
     * Cập nhật quyền user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'roles' => 'array',
            'roles.*' => 'exists:roles,role_id'
        ]);

        try {
            DB::beginTransaction();

            // Xóa tất cả role cũ
            UserRole::where('user_id', $user->id)->delete();

            // Thêm role mới
            if ($request->has('roles')) {
                foreach ($request->roles as $roleId) {
                    UserRole::create([
                        'user_id' => $user->id,
                        'role_id' => $roleId,
                        'assigned_at' => now()
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('dashboard.users.index')
                ->with('success', "Cập nhật quyền cho user {$user->name} thành công!");

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật quyền: ' . $e->getMessage());
        }
    }

    /**
     * Gán role cho user
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,role_id'
        ]);

        // Kiểm tra user đã có role này chưa
        if (UserRole::where('user_id', $user->id)
                   ->where('role_id', $request->role_id)
                   ->exists()) {
            return back()->with('error', 'User đã có role này rồi!');
        }

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
            'assigned_at' => now()
        ]);

        $role = Role::find($request->role_id);
        return back()->with('success', "Đã gán role {$role->role_display_name} cho user {$user->name}!");
    }

    /**
     * Gỡ role khỏi user
     */
    public function removeRole(User $user, Role $role)
    {
        $userRole = UserRole::where('user_id', $user->id)
                          ->where('role_id', $role->role_id)
                          ->first();

        if (!$userRole) {
            return back()->with('error', 'User không có role này!');
        }

        $userRole->delete();

        return back()->with('success', "Đã gỡ role {$role->role_display_name} khỏi user {$user->name}!");
    }

    /**
     * Hiển thị danh sách roles
     */
    public function roles()
    {
        $roles = Role::withCount('users')->get();
        return view('dashboard.roles.index', compact('roles'));
    }

    /**
     * Tạo role mới
     */
    public function createRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:100|unique:roles,role_name',
            'role_display_name' => 'required|string|max:150'
        ]);

        Role::create([
            'role_name' => $request->role_name,
            'role_display_name' => $request->role_display_name,
            'role_created_at' => now()
        ]);

        return back()->with('success', 'Tạo role mới thành công!');
    }

    /**
     * Xóa role
     */
    public function deleteRole(Role $role)
    {
        // Kiểm tra role có đang được sử dụng không
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Không thể xóa role đang được sử dụng!');
        }

        $role->delete();
        return back()->with('success', 'Xóa role thành công!');
    }

    /**
     * Hiển thị thống kê phân quyền
     */
    public function permissions()
    {
        $currentUser = Auth::user();
        $permissions = $currentUser->getAllPermissions();
        
        $userStats = [
            'total_users' => User::count(),
            'admin_count' => User::whereHas('roles', function($q) {
                $q->where('role_name', 'admin');
            })->count(),
            'manager_count' => User::whereHas('roles', function($q) {
                $q->where('role_name', 'manager');
            })->count(),
            'user_count' => User::whereHas('roles', function($q) {
                $q->where('role_name', 'user');
            })->count(),
        ];

        return view('dashboard.permissions.index', compact('permissions', 'userStats'));
    }
}
