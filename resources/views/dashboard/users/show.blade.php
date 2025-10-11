@extends('layouts.app')

@section('title', 'Chi tiết User')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chi tiết User: {{ $user->name }}</h1>
        <a href="{{ route('dashboard.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>ID:</strong></td>
                            <td>{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <td><strong>Tên:</strong></td>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Số điện thoại:</strong></td>
                            <td>{{ $user->phone ?? 'Chưa có' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Địa chỉ:</strong></td>
                            <td>{{ $user->address ?? 'Chưa có' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ngày tạo:</strong></td>
                            <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Cập nhật lần cuối:</strong></td>
                            <td>{{ $user->updated_at->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Roles và quyền -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Roles và Quyền</h6>
                    @if(Auth::user()->isAdmin())
                    <a href="{{ route('dashboard.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit"></i> Sửa quyền
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <h6><strong>Roles hiện tại:</strong></h6>
                    @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                            <span class="badge badge-primary badge-pill mr-2 mb-2">
                                {{ $role->role_display_name }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-muted">User chưa có role nào.</p>
                    @endif

                    <hr>

                    <h6><strong>Quyền hiện tại:</strong></h6>
                    @php
                        $permissions = $user->getAllPermissions();
                    @endphp
                    
                    @if(in_array('*', $permissions))
                        <span class="badge badge-danger badge-pill">Tất cả quyền (Admin)</span>
                    @elseif(count($permissions) > 0)
                        <div class="row">
                            @foreach($permissions as $permission)
                                <div class="col-md-6 mb-1">
                                    <span class="badge badge-success badge-pill">{{ $permission }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">User chưa có quyền nào.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Lịch sử đơn hàng -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Lịch sử đơn hàng ({{ $user->orders->count() }} đơn)</h6>
        </div>
        <div class="card-body">
            @if($user->orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ngày đặt</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->orders->take(10) as $order)
                            <tr>
                                <td>#{{ $order->order_id }}</td>
                                <td>{{ $order->order_date->format('d/m/Y H:i') }}</td>
                                <td>{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                                <td>
                                    @switch($order->status)
                                        @case('pending')
                                            <span class="badge badge-warning">Chờ xử lý</span>
                                            @break
                                        @case('confirmed')
                                            <span class="badge badge-info">Đã xác nhận</span>
                                            @break
                                        @case('shipping')
                                            <span class="badge badge-primary">Đang giao</span>
                                            @break
                                        @case('completed')
                                            <span class="badge badge-success">Hoàn thành</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-danger">Đã hủy</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $order->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('dashboard.orders.show', $order->order_id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Xem
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($user->orders->count() > 10)
                    <p class="text-muted text-center">Hiển thị 10 đơn hàng gần nhất...</p>
                @endif
            @else
                <p class="text-muted text-center">User chưa có đơn hàng nào.</p>
            @endif
        </div>
    </div>
</div>
@endsection