<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserManagementController extends Controller
{
    /**
     * Hiển thị danh sách người dùng cho admin
     *
     * Chức năng: Hiển thị tất cả người dùng trong hệ thống với tính năng tìm kiếm
     * Hoạt động:
     * - Query users với eager loading roles
     * - Tìm kiếm theo tên hoặc email (LIKE search) nếu có tham số search
     * - Sắp xếp theo created_at giảm dần (mới nhất trước)
     * - Phân trang 15 users mỗi trang
     * - Trả về view với danh sách users đã phân trang
     *
     * @param  \Illuminate\Http\Request  $request  Chứa tham số search
     * @return \Illuminate\View\View
     */
    public function index(Request $request) // Request $request de lay du lieu tim kiem
    {
        $query = User::with('roles'); // Query builder de truy van users voi roles

        // Tìm kiếm theo tên hoặc email nếu có
        if ($request->has('search') && $request->search) { // Kiem tra neu co tham so search
            $search = $request->search; // Luu gia tri tim kiem
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15); // Phan trang 15 user/trang

        return view('dashboard.users.index', compact('users')); // Truyen users vao view
    }

    /**
     * Hiển thị chi tiết người dùng
     *
     * Chức năng: Hiển thị thông tin chi tiết của một người dùng cụ thể
     * Hoạt động:
     * - Sử dụng route model binding để tự động load user
     * - Eager load relationships: roles và orders
     * - Trả về view chi tiết với thông tin user, roles, orders
     *
     * @param  User  $user  Instance của user (tự động inject bởi route model binding)
     * @return \Illuminate\View\View
     */
    public function show(User $user) // User $user: user can hien thi
    {
        $user->load('roles', 'orders');

        return view('dashboard.users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa quyền người dùng
     *
     * Chức năng: Hiển thị form để quản lý roles của user
     * Hoạt động:
     * - Load roles hiện tại của user
     * - Lấy tất cả roles có trong hệ thống
     * - Trả về view form edit với user và danh sách roles
     *
     * @param  User  $user  Instance của user cần chỉnh sửa
     * @return \Illuminate\View\View
     */
    public function edit(User $user)
    {
        $user->load('roles'); // Load roles cua user
        $roles = Role::all();   // Lay tat ca roles de hien thi trong form

        return view('dashboard.users.edit', compact('user', 'roles'));  // Truyen user va roles vao view
    }

    /**
     * Cập nhật quyền của người dùng
     *
     * Chức năng: Xử lý cập nhật roles cho user
     * Hoạt động:
     * - Validate dữ liệu: roles phải là mảng, mỗi role_id phải tồn tại trong bảng roles
     * - Sử dụng database transaction:
     *   + Xóa tất cả roles cũ của user
     *   + Thêm các roles mới được chọn
     *   + Ghi nhận thời gian gán role (assigned_at)
     * - Commit transaction nếu thành công
     * - Rollback nếu có lỗi
     * - Redirect về danh sách users với thông báo kết quả
     *
     * @param  \Illuminate\Http\Request  $request  Chứa danh sách role_ids mới
     * @param  User  $user  Instance của user cần cập nhật
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UserRoleRequest $request, User $user)
    {
        try {
            DB::beginTransaction();

            // Xóa tất cả role cũ
            UserRole::where('user_id', $user->id)->delete();   // Xoa cac role hien tai cua user

            // Thêm role mới
            if ($request->has('roles')) { // Neu co roles moi
                foreach ($request->roles as $roleId) { // Lap qua tung role
                    UserRole::create([ // Tao moi UserRole
                        'user_id' => $user->id, // ID cua user
                        'role_id' => $roleId, // ID cua role
                        'assigned_at' => now(), // Thoi gian gan role
                    ]);
                }
            }

            DB::commit(); // Luu user voi roles moi

            return redirect()->route('dashboard.users.index')
                ->with('success', "Cập nhật quyền cho user {$user->name} thành công!"); // Chuyen huong ve danh sach user voi thong bao thanh cong

        } catch (\Exception $e) {
            DB::rollback();

            return back()->with('error', 'Có lỗi xảy ra khi cập nhật quyền: '.$e->getMessage()); // Quay lai voi thong bao loi
        }
    }

    /**
     * Gán role mới cho người dùng
     *
     * Chức năng: Thêm một role cụ thể cho user
     * Hoạt động:
     * - Validate role_id phải tồn tại trong bảng roles
     * - Kiểm tra user đã có role này chưa
     * - Nếu đã có, trả về thông báo lỗi
     * - Nếu chưa có, tạo bản ghi UserRole mới với assigned_at = now()
     * - Quay lại trang trước với thông báo thành công
     *
     * @param  \Illuminate\Http\Request  $request  Chứa role_id cần gán
     * @param  User  $user  Instance của user cần gán role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignRole(UserRoleRequest $request, User $user)
    {
        if (UserRole::where('user_id', $user->id)
            ->where('role_id', $request->role_id)
            ->exists()) {
            return back()->with('error', 'User đã có role này rồi!');
        }

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $request->role_id,
            'assigned_at' => now(),
        ]);

        $role = Role::find($request->role_id);

        return back()->with('success', "Đã gán role {$role->role_display_name} cho user {$user->name}!");
    }

    /**
     * Gỡ bỏ role khỏi người dùng
     *
     * Chức năng: Xóa một role cụ thể của user
     * Hoạt động:
     * - Tìm bản ghi UserRole theo user_id và role_id
     * - Nếu không tìm thấy, trả về thông báo lỗi
     * - Nếu tìm thấy, xóa bản ghi UserRole
     * - Quay lại trang trước với thông báo thành công
     *
     * @param  User  $user  Instance của user cần gỡ role
     * @param  Role  $role  Instance của role cần gỡ
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeRole(User $user, Role $role)
    {
        $userRole = UserRole::where('user_id', $user->id)
            ->where('role_id', $role->role_id)
            ->first();

        if (! $userRole) {
            return back()->with('error', 'User không có role này!');
        }

        $userRole->delete();

        return back()->with('success', "Đã gỡ role {$role->role_display_name} khỏi user {$user->name}!");
    }

    /**
     * Xóa người dùng khỏi hệ thống
     *
     * Chức năng: Xóa một user cụ thể khỏi database
     * Hoạt động:
     * - Kiểm tra không được xóa chính mình (user đang đăng nhập)
     * - Sử dụng database transaction:
     *   + Xóa tất cả roles của user
     *   + Xóa user khỏi database
     * - Commit transaction nếu thành công
     * - Rollback nếu có lỗi
     * - Redirect về danh sách users với thông báo kết quả
     *
     * Lưu ý: Cần cân nhắc xử lý các dữ liệu liên quan (orders, cart) trước khi xóa
     *
     * @param  User  $user  Instance của user cần xóa
     * @return \Illuminate\Http\RedirectResponse
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

            return back()->with('error', 'Có lỗi xảy ra khi xóa người dùng: '.$e->getMessage());
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
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user(); // Lấy user hiện tại
        $permissions = $currentUser->getAllPermissions(); // Lấy tất cả quyền của user hiện tại

        $userStats = [
            'total_users' => User::count(), // Tổng số user
            'admin_count' => User::whereHas('roles', function ($q) {     // Đếm số user có role admin
                $q->where('role_name', 'admin'); // Lọc role admin
            })->count(),
            'manager_count' => User::whereHas('roles', function ($q) {
                $q->where('role_name', 'manager');
            })->count(),
            'user_count' => User::whereHas('roles', function ($q) {
                $q->where('role_name', 'user');
            })->count(),
        ];

        return view('dashboard.permissions.index', compact('permissions', 'userStats'));
    }
}
