@extends('layouts.app')

@section('title', 'Quản lý Roles')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản lý Roles</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createRoleModal">
            <i class="fas fa-plus"></i> Tạo Role mới
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Roles</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Role</th>
                            <th>Tên hiển thị</th>
                            <th>Số lượng Users</th>
                            <th>Ngày tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->role_id }}</td>
                            <td><code>{{ $role->role_name }}</code></td>
                            <td>{{ $role->role_display_name }}</td>
                            <td>
                                <span class="badge badge-info">{{ $role->users_count }} users</span>
                            </td>
                            <td>{{ $role->role_created_at ? $role->role_created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                @if($role->users_count == 0 && !in_array($role->role_name, ['admin', 'manager', 'user']))
                                <form action="{{ route('dashboard.roles.delete', $role->role_id) }}" 
                                      method="POST" 
                                      style="display: inline-block;"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa role {{ $role->role_display_name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                                @else
                                <span class="text-muted">
                                    @if(in_array($role->role_name, ['admin', 'manager', 'user']))
                                        (Role hệ thống)
                                    @else
                                        (Đang sử dụng)
                                    @endif
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Mô tả quyền của từng role -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Mô tả quyền của từng Role</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Admin</div>
                                    <div class="text-xs mb-0 font-weight-bold text-gray-800">
                                        • Tất cả quyền trong hệ thống<br>
                                        • Quản lý users và roles<br>
                                        • Quản lý sản phẩm, đơn hàng<br>
                                        • Xem báo cáo, thống kê
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-crown fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Manager</div>
                                    <div class="text-xs mb-0 font-weight-bold text-gray-800">
                                        • Quản lý sản phẩm và danh mục<br>
                                        • Quản lý đơn hàng<br>
                                        • Xem báo cáo<br>
                                        • Xem danh sách users
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-tie fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">User</div>
                                    <div class="text-xs mb-0 font-weight-bold text-gray-800">
                                        • Xem sản phẩm<br>
                                        • Đặt hàng<br>
                                        • Xem đơn hàng của mình<br>
                                        • Chỉnh sửa profile cá nhân
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal tạo role mới -->
<div class="modal fade" id="createRoleModal" tabindex="-1" role="dialog" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createRoleModalLabel">Tạo Role mới</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('dashboard.roles.create') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="role_name"><strong>Tên Role (code):</strong></label>
                        <input type="text" 
                               class="form-control" 
                               id="role_name" 
                               name="role_name" 
                               placeholder="vd: editor, viewer" 
                               required>
                        <small class="form-text text-muted">Tên role dùng trong code, chỉ chữ thường và dấu gạch dưới</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="role_display_name"><strong>Tên hiển thị:</strong></label>
                        <input type="text" 
                               class="form-control" 
                               id="role_display_name" 
                               name="role_display_name" 
                               placeholder="vd: Biên tập viên, Người xem" 
                               required>
                        <small class="form-text text-muted">Tên role hiển thị cho người dùng</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Tạo Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection