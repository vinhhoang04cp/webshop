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
    public function index(Request $request) // Request $request de lay du lieu tim kiem
    {
        $query = User::with('roles'); // Query builder de truy van users voi roles

        // Tìm kiếm theo tên hoặc email nếu có
        if ($request->has('search') && $request->search) { // Kiem tra neu co tham so search
            $search = $request->search; // Luu gia tri tim kiem
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15); // Phan trang 15 user/trang
        
        return view('dashboard.users.index', compact('users')); // Truyen users vao view
    }

    /**
     * Hiển thị chi tiết user
     */ 
    public function show(User $user) // User $user: user can hien thi
    {
        $user->load('roles', 'orders');
        return view('dashboard.users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa quyền user
     */
    public function edit(User $user)
    {
        $user->load('roles'); // Load roles cua user
        $roles = Role::all();   // Lay tat ca roles de hien thi trong form
        return view('dashboard.users.edit', compact('user', 'roles'));  // Truyen user va roles vao view
    }

    /**
     * Cập nhật quyền user
     */
    public function update(Request $request, User $user) // User $user: user can cap nhat
    {
        $request->validate([
            'roles' => 'array', // roles phai la mang
            'roles.*' => 'exists:roles,role_id' // moi phan tu trong mang phai ton tai trong bang roles
        ]);

        try {
            DB::beginTransaction(); // Bat dau giao dich

            // Xóa tất cả role cũ
            UserRole::where('user_id', $user->id)->delete();   // Xoa cac role hien tai cua user

            // Thêm role mới
            if ($request->has('roles')) { // Neu co roles moi
                foreach ($request->roles as $roleId) { // Lap qua tung role
                    UserRole::create([ // Tao moi UserRole
                        'user_id' => $user->id, // ID cua user
                        'role_id' => $roleId, // ID cua role
                        'assigned_at' => now() // Thoi gian gan role
                    ]);
                }
            }

            DB::commit(); // Luu user voi roles moi

            return redirect()->route('dashboard.users.index')
                ->with('success', "Cập nhật quyền cho user {$user->name} thành công!"); // Chuyen huong ve danh sach user voi thong bao thanh cong

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật quyền: ' . $e->getMessage()); // Quay lai voi thong bao loi
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
     * Xóa user
     */
    public function destroy(User $user) // User $user lay tu Model 
    {
        // Không cho phép xóa chính mình
        if ($user->id === Auth::id()) { // Auth::id(): lay id cua user dang dang nhap
            return back()->with('error', 'Bạn không thể xóa tài khoản của chính mình!');
        }

        try { // Bat dau giao dich
            DB::beginTransaction();

            // Xóa các roles của user
            UserRole::where('user_id', $user->id)->delete();

            // Xóa user (các quan hệ khác sẽ được xử lý bởi foreign key constraints)
            $userName = $user->name;
            $user->delete();

            DB::commit(); // Luu user sau khi xoa

            return redirect()->route('dashboard.users.index')
                           ->with('success', "Đã xóa người dùng {$userName} thành công!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi xóa người dùng: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị danh sách roles
     */
    public function roles()
    {
        $roles = Role::withCount('users')->get(); // Dem so user trong moi role
        return view('dashboard.roles.index', compact('roles')); // Truyen roles vao view
    } 

    /**
     * Xóa role
     */

    /**
     * Hiển thị thống kê phân quyền
     */
    public function permissions() 
    {
        $currentUser = Auth::user(); // Lấy user hiện tại
        $permissions = $currentUser->getAllPermissions(); // Lấy tất cả quyền của user hiện tại
        
        $userStats = [
            'total_users' => User::count(), // Tổng số user
            'admin_count' => User::whereHas('roles', function($q) {     // Đếm số user có role admin
                $q->where('role_name', 'admin'); // Lọc role admin
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
