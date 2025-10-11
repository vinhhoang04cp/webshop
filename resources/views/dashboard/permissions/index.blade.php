@extends('layouts.app')

@section('title', 'Quyền và Thống kê')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quyền và Thống kê</h1>
    </div>

    <!-- Thống kê Users theo Role -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['total_users'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Admins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['admin_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-crown fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Managers</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['manager_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userStats['user_count'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quyền của User hiện tại -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quyền của bạn</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6><strong>User:</strong> {{ Auth::user()->name }}</h6>
                        <h6><strong>Roles:</strong></h6>
                        @foreach(Auth::user()->roles as $role)
                            <span class="badge badge-primary mr-1">{{ $role->role_display_name }}</span>
                        @endforeach
                    </div>

                    <hr>

                    <h6><strong>Quyền hiện có:</strong></h6>
                    @if(in_array('*', $permissions))
                        <div class="alert alert-danger">
                            <i class="fas fa-crown"></i> <strong>Bạn có tất cả quyền trong hệ thống (Admin)</strong>
                        </div>
                    @else
                        <div class="row">
                            @foreach($permissions as $permission)
                            <div class="col-md-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="perm_{{ $loop->index }}" checked disabled>
                                    <label class="custom-control-label" for="perm_{{ $loop->index }}">
                                        <code>{{ $permission }}</code>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ma trận quyền -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ma trận quyền hệ thống</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th>Quyền</th>
                                    <th class="text-center">Admin</th>
                                    <th class="text-center">Manager</th>
                                    <th class="text-center">User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><small>view_products</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td><small>create_product</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>edit_product</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>delete_product</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>view_orders</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>create_order</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td><small>view_own_orders</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                </tr>
                                <tr>
                                    <td><small>view_categories</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>create_category</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>view_reports</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                                <tr>
                                    <td><small>view_users</small></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-check text-success"></i></td>
                                    <td class="text-center"><i class="fas fa-times text-danger"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hướng dẫn sử dụng -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Hướng dẫn sử dụng hệ thống phân quyền</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6><strong>Các bước quản lý quyền:</strong></h6>
                    <ol>
                        <li>Vào <strong>Quản lý Users</strong> để xem danh sách users</li>
                        <li>Chọn user cần phân quyền và click <strong>"Sửa quyền"</strong></li>
                        <li>Chọn roles phù hợp cho user</li>
                        <li>Lưu thay đổi</li>
                    </ol>

                    <h6><strong>Lưu ý quan trọng:</strong></h6>
                    <ul>
                        <li><span class="badge badge-danger">Admin</span> có tất cả quyền</li>
                        <li><span class="badge badge-warning">Manager</span> có quyền quản lý sản phẩm, đơn hàng</li>
                        <li><span class="badge badge-success">User</span> chỉ có quyền cơ bản</li>
                        <li>Một user có thể có nhiều roles</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6><strong>Middleware sử dụng:</strong></h6>
                    <div class="bg-light p-3 rounded">
                        <code>
                            // Kiểm tra role cụ thể<br>
                            Route::middleware('role:admin')<br><br>
                            
                            // Kiểm tra quyền cụ thể<br>
                            Route::middleware('permission:edit_product')<br><br>
                            
                            // Kiểm tra quyền dashboard<br>
                            Route::middleware('role:dashboard')
                        </code>
                    </div>

                    <h6 class="mt-3"><strong>Methods trong User model:</strong></h6>
                    <div class="bg-light p-3 rounded">
                        <code>
                            $user->isAdmin();<br>
                            $user->isManager();<br>
                            $user->hasRole('manager');<br>
                            $user->hasPermission('edit_product');<br>
                            $user->canAccessDashboard();<br>
                            $user->getAllPermissions();
                        </code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection