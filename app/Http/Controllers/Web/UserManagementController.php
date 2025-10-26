<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest;
use App\Models\Role;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    protected $userManagementService;

    public function __construct(UserManagementService $userManagementService)
    {
        $this->userManagementService = $userManagementService;
    }

    /**
     * Hiển thị danh sách người dùng cho admin
     */
    public function index(Request $request)
    {
        $searchTerm = $request->get('search');
        $users = $this->userManagementService->getUsersForAdmin($searchTerm);

        return view('dashboard.users.index', compact('users'));
    }

    /**
     * Hiển thị chi tiết người dùng
     */
    public function show(User $user)
    {
        $user = $this->userManagementService->getUserDetail($user->id);

        return view('dashboard.users.show', compact('user'));
    }

    /**
     * Hiển thị form chỉnh sửa quyền người dùng
     */
    public function edit(User $user)
    {
        $user->load('roles');
        $roles = Role::all();

        return view('dashboard.users.edit', compact('user', 'roles'));
    }

    /**
     * Cập nhật quyền của người dùng
     */
    public function update(UserRoleRequest $request, User $user)
    {
        try {
            $this->userManagementService->updateUserRoles($user->id, $request->roles);

            return redirect()->route('dashboard.users.index')
                ->with('success', "Cập nhật quyền cho user {$user->name} thành công!");
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật quyền: '.$e->getMessage());
        }
    }

    /**
     * Gán role mới cho người dùng
     */
    public function assignRole(UserRoleRequest $request, User $user)
    {
        try {
            $role = $this->userManagementService->assignRole($user->id, $request->role_id);

            return back()->with('success', "Đã gán role {$role->role_display_name} cho user {$user->name}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Gỡ bỏ role khỏi người dùng
     */
    public function removeRole(User $user, Role $role)
    {
        try {
            $this->userManagementService->removeRole($user->id, $role->role_id);

            return back()->with('success', "Đã gỡ role {$role->role_display_name} khỏi user {$user->name}!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa người dùng khỏi hệ thống
     */
    public function destroy(User $user)
    {
        try {
            $userName = $this->userManagementService->deleteUser($user->id, Auth::id());

            return redirect()->route('dashboard.users.index')
                ->with('success', "Đã xóa người dùng {$userName} thành công!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hiển thị danh sách roles
     */
    public function roles()
    {
        $roles = $this->userManagementService->getRolesWithUserCount();

        return view('dashboard.roles.index', compact('roles'));
    }

    /**
     * Hiển thị thống kê phân quyền
     */
    public function permissions()
    {
        /** @var \App\Models\User $currentUser */
        $currentUser = Auth::user();
        $permissions = $currentUser->getAllPermissions();

        $userStats = $this->userManagementService->getUserStatsByRole();

        return view('dashboard.permissions.index', compact('permissions', 'userStats'));
    }
}
