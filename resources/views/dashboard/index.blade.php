@extends('layouts.app')

@section('title', 'Dashboard - WebShop Admin')

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
                <a class="nav-link active" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="#products">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="#orders">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
                <a class="nav-link" href="#users">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                <a class="nav-link" href="#reports">
                    <i class="fas fa-chart-bar"></i> Báo cáo
                </a>
            </nav>
            
            <div class="user-info mt-auto">
                <div class="user-name">{{ $user->name }}</div>
                <div class="user-role">{{ $user->hasRole('admin') ? 'Administrator' : 'Manager' }}</div>
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
            <!-- Header -->
            <div class="dashboard-header">
                <div>
                    <h2>Tổng quan hệ thống</h2>
                    <p class="text-muted mb-0">Chào mừng trở lại, {{ $user->name }}!</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Stats Cards -->
            <div class="row g-4 mb-4">
                @php
                    $statCards = [ 
                        ['value' => $productsCount, 'label' => 'Sản phẩm', 'icon' => 'fa-box', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                        ['value' => $ordersCount, 'label' => 'Đơn hàng', 'icon' => 'fa-shopping-cart', 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
                        ['value' => $usersCount, 'label' => 'Khách hàng', 'icon' => 'fa-users', 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)'],
                        ['value' => number_format($totalRevenue) . ' đ', 'label' => 'Doanh thu', 'icon' => 'fa-chart-line', 'gradient' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)'],
                    ];
                @endphp

                                @foreach ($statCards as $card)
                    <div class="col-md-6 col-lg-3">
                        <div class="stat-card" style="background: {{ $card['gradient'] }};">
                            <div class="stat-icon">
                                <i class="fas {{ $card['icon'] }}"></i>
                            </div>
                            <h3>{{ $card['value'] }}</h3>
                            <p>{{ $card['label'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Recent Activities -->
            <div class="row g-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Đơn hàng gần đây</h5>
                            <a href="#orders" class="btn btn-sm btn-outline-primary">
                                Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Trạng thái</th>
                                            <th>Tổng tiền</th>
                                            <th>Ngày tạo</th>
                                        </tr>
                                    </thead>
            </div>

            <!-- Recent Activities -->
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Đơn hàng gần đây</h5>
                            <a href="#orders" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Trạng thái</th>
                                            <th>Tổng tiền</th>
                                            <th>Ngày tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            @php
                                                $statusMap = [
                                                    \App\Models\Order::STATUS_PENDING => ['label' => 'warning', 'text' => 'Chờ xử lý'],
                                                    \App\Models\Order::STATUS_PROCESSING => ['label' => 'primary', 'text' => 'Đang xử lý'],
                                                    \App\Models\Order::STATUS_SHIPPED => ['label' => 'info', 'text' => 'Đang giao'],
                                                    \App\Models\Order::STATUS_DELIVERED => ['label' => 'success', 'text' => 'Hoàn thành'],
                                                    \App\Models\Order::STATUS_CANCELLED => ['label' => 'danger', 'text' => 'Đã huỷ'],
                                                ];
                                            @endphp

                                            @forelse($recentOrders as $order)
                                            <tr>
                                                <td><strong>#{{ $order->order_id }}</strong></td>
                                                <td>{{ optional($order->user)->name ?? 'Khách vãng lai' }}</td>
                                                <td>
                                                        @php $s = $statusMap[$order->status] ?? null; @endphp
                                                        <span class="badge bg-{{ $s['label'] ?? 'secondary' }} rounded-pill">{{ $s['text'] ?? ucfirst($order->status ?? 'unknown') }}</span>
                                                </td>
                                                <td><strong>{{ number_format($order->total_amount) }} đ</strong></td>
                                                <td class="text-muted">{{ optional($order->order_date)->format('d/m/Y H:i') ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                                    Không có đơn hàng nào gần đây
                                                </td>
                                            </tr>
                                            @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Hoạt động hệ thống</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-user-plus text-success"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Người dùng mới</h6>
                                    <p class="mb-0 text-muted small">5 người dùng mới đăng ký</p>
                                </div>
                                <small class="text-muted">2h trước</small>
                            </div>
                            
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-box text-primary"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Sản phẩm mới</h6>
                                    <p class="mb-0 text-muted small">3 sản phẩm được thêm</p>
                                </div>
                                <small class="text-muted">4h trước</small>
                            </div>
                            
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-chart-line text-warning"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1">Báo cáo tuần</h6>
                                    <p class="mb-0 text-muted small">Báo cáo doanh thu tuần đã sẵn sàng</p>
                                </div>
                                <small class="text-muted">1 ngày trước</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

