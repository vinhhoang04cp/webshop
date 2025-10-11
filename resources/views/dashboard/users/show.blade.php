@extends('layouts.app')

@section('title', 'Chi tiết người dùng - WebShop Admin')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar d-flex flex-column">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> WebShop</h3>
                <small class="text-muted" style="color: #9ca3af !important;">Admin Panel</small>
            </div>
            
            <nav class="nav flex-column sidebar-menu">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="{{ route('dashboard.products.index') }}">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="{{ route('dashboard.orders.index') }}">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
                @if(Auth::user()->isAdmin())
                <a class="nav-link active" href="{{ route('dashboard.users.index') }}">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                @endif
            </nav>
            
            <div class="user-info mt-auto">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->hasRole('admin') ? 'Administrator' : 'Manager' }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Chi tiết người dùng</h2>
                    <p class="text-muted mb-0">Thông tin chi tiết về "{{ $user->name }}"</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Thông tin chính -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin người dùng</h5>
                                <div>
                                    @if(Auth::user()->isAdmin())
                                    <a href="{{ route('dashboard.users.edit', $user->id) }}" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa quyền
                                    </a>
                                    @endif
                                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12 text-center mb-3">
                                    <div class="avatar-circle-large mx-auto">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <h3 class="mt-3">{{ $user->name }}</h3>
                                    <p class="text-muted">ID: #{{ $user->id }}</p>
                                </div>
                            </div>

                            <div class="info-grid">
                                <div class="info-item">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <div>
                                        <small class="text-muted">Email</small>
                                        <div>{{ $user->email }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fas fa-phone text-success"></i>
                                    <div>
                                        <small class="text-muted">Số điện thoại</small>
                                        <div>{{ $user->phone ?? 'Chưa có' }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    <div>
                                        <small class="text-muted">Địa chỉ</small>
                                        <div>{{ $user->address ?? 'Chưa có' }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fas fa-calendar-plus text-info"></i>
                                    <div>
                                        <small class="text-muted">Ngày tạo</small>
                                        <div>{{ $user->created_at->format('d/m/Y H:i:s') }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <i class="fas fa-calendar-check text-warning"></i>
                                    <div>
                                        <small class="text-muted">Cập nhật lần cuối</small>
                                        <div>{{ $user->updated_at->format('d/m/Y H:i:s') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lịch sử đơn hàng -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Lịch sử đơn hàng ({{ $user->orders->count() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($user->orders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4">Mã đơn</th>
                                                <th>Ngày đặt</th>
                                                <th>Tổng tiền</th>
                                                <th>Trạng thái</th>
                                                <th class="text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->orders->take(10) as $order)
                                            <tr>
                                                <td class="ps-4">#{{ $order->order_id }}</td>
                                                <td>{{ $order->order_date->format('d/m/Y H:i') }}</td>
                                                <td><strong>{{ number_format($order->total_amount, 0, ',', '.') }}đ</strong></td>
                                                <td>
                                                    @switch($order->status)
                                                        @case('pending')
                                                            <span class="badge bg-warning">Chờ xử lý</span>
                                                            @break
                                                        @case('confirmed')
                                                            <span class="badge bg-info">Đã xác nhận</span>
                                                            @break
                                                        @case('shipping')
                                                            <span class="badge bg-primary">Đang giao</span>
                                                            @break
                                                        @case('completed')
                                                            <span class="badge bg-success">Hoàn thành</span>
                                                            @break
                                                        @case('cancelled')
                                                            <span class="badge bg-danger">Đã hủy</span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $order->status }}</span>
                                                    @endswitch
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route('dashboard.orders.show', $order->order_id) }}" class="btn btn-sm btn-outline-info">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if($user->orders->count() > 10)
                                    <div class="card-footer text-center text-muted">
                                        Hiển thị 10 đơn hàng gần nhất
                                    </div>
                                @endif
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block"></i>
                                    Người dùng chưa có đơn hàng nào
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar thông tin phụ -->
                <div class="col-lg-4">
                    <!-- Roles và quyền -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Roles & Quyền</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Roles hiện tại:</h6>
                            @if($user->roles->count() > 0)
                                <div class="mb-3">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-1 mb-1">
                                            {{ $role->role_display_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted fst-italic">Chưa có role nào</p>
                            @endif

                            <hr>

                            <h6 class="text-muted mb-2">Quyền hiện tại:</h6>
                            @php
                                $permissions = $user->getAllPermissions();
                            @endphp
                            
                            @if(in_array('*', $permissions))
                                <span class="badge bg-danger">Tất cả quyền (Admin)</span>
                            @elseif(count($permissions) > 0)
                                <div>
                                    @foreach($permissions as $permission)
                                        <span class="badge bg-success me-1 mb-1">{{ $permission }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted fst-italic">Chưa có quyền nào</p>
                            @endif
                        </div>
                    </div>

                    <!-- Thống kê -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Thống kê</h5>
                        </div>
                        <div class="card-body">
                            <div class="stats-item">
                                <i class="fas fa-shopping-cart text-primary"></i>
                                <div>
                                    <h4 class="mb-0">{{ $user->orders->count() }}</h4>
                                    <small class="text-muted">Tổng đơn hàng</small>
                                </div>
                            </div>
                            <hr>
                            <div class="stats-item">
                                <i class="fas fa-check-circle text-success"></i>
                                <div>
                                    <h4 class="mb-0">{{ $user->orders->where('status', 'completed')->count() }}</h4>
                                    <small class="text-muted">Đơn hoàn thành</small>
                                </div>
                            </div>
                            <hr>
                            <div class="stats-item">
                                <i class="fas fa-dollar-sign text-warning"></i>
                                <div>
                                    <h4 class="mb-0">{{ number_format($user->orders->sum('total_amount'), 0, ',', '.') }}đ</h4>
                                    <small class="text-muted">Tổng chi tiêu</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle-large {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.info-grid {
    display: grid;
    gap: 1rem;
}

.info-item {
    display: flex;
    align-items: start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
}

.info-item i {
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.stats-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.stats-item i {
    font-size: 2rem;
}
</style>
@endsection