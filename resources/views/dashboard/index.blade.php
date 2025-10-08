@extends('layouts.app')

@section('title', 'Dashboard - WebShop Admin')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar"> <!-- col-md-3: chiem 3/12 kich thuoc tren man hinh -->
            <div class="text-center text-white mb-4 p-3">
                <h4><i class="fas fa-shield-alt"></i> WebShop</h4>
                <small>Admin Panel</small>
            </div>
            
            <nav class="nav flex-column">
                <a class="nav-link active" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a class="nav-link" href="#products">
                    <i class="fas fa-box me-2"></i> Sản phẩm
                </a>
                <a class="nav-link" href="#categories">
                    <i class="fas fa-tags me-2"></i> Danh mục
                </a>
                <a class="nav-link" href="#orders">
                    <i class="fas fa-shopping-cart me-2"></i> Đơn hàng
                </a>
                <a class="nav-link" href="#users">
                    <i class="fas fa-users me-2"></i> Người dùng
                </a>
                <a class="nav-link" href="#reports">
                    <i class="fas fa-chart-bar me-2"></i> Báo cáo
                </a>
                
                <hr class="text-white mx-3">
                
                <a class="nav-link" href="#settings">
                    <i class="fas fa-cog me-2"></i> Cài đặt
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mx-3 mt-3"> <!-- Logout Form, gui http POST request, route logout tu controller -->
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </button>
                </form>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-1">Dashboard</h2>
                    <p class="text-muted mb-0">Chào mừng quay trở lại, {{ $user->name }}!</p>
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2">
                        {{ $user->hasRole('admin') ? 'Admin' : 'Manager' }} 
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ $user->name }}
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#profile"><i class="fas fa-user me-2"></i>Hồ sơ</a></li>
                            <li><a class="dropdown-item" href="#settings"><i class="fas fa-cog me-2"></i>Cài đặt</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
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
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($productsCount) }}</h4>
                                    <p class="card-text">Sản phẩm</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-box fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($ordersCount) }}</h4>
                                    <p class="card-text">Đơn hàng</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-shopping-cart fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">{{ number_format($usersCount) }}</h4>
                                    <p class="card-text">Khách hàng</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="card-title">₫{{ number_format($totalRevenue) }}</h4>
                                    <p class="card-text">Doanh thu</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-chart-line fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
                                            @forelse($recentOrders as $order)
                                            <tr>
                                                <td>#{{ $order->order_id }}</td>
                                                <td>{{ optional($order->user)->name ?? 'Khách vãng lai' }}</td>
                                                <td>
                                                    @php
                                                        $statusLabel = 'secondary';
                                                        switch ($order->status) {
                                                            case \App\Models\Order::STATUS_PENDING:
                                                                $statusLabel = 'warning';
                                                                $statusText = 'Chờ xử lý';
                                                                break;
                                                            case \App\Models\Order::STATUS_PROCESSING:
                                                                $statusLabel = 'primary';
                                                                $statusText = 'Đang xử lý';
                                                                break;
                                                            case \App\Models\Order::STATUS_SHIPPED:
                                                                $statusLabel = 'info';
                                                                $statusText = 'Đang giao';
                                                                break;
                                                            case \App\Models\Order::STATUS_DELIVERED:
                                                                $statusLabel = 'success';
                                                                $statusText = 'Hoàn thành';
                                                                break;
                                                            case \App\Models\Order::STATUS_CANCELLED:
                                                                $statusLabel = 'danger';
                                                                $statusText = 'Đã huỷ';
                                                                break;
                                                            default:
                                                                $statusText = ucfirst($order->status ?? 'unknown');
                                                        }
                                                    @endphp
                                                    <span class="badge bg-{{ $statusLabel }}">{{ $statusText }}</span>
                                                </td>
                                                <td>₫{{ number_format($order->total_amount) }}</td>
                                                <td>{{ optional($order->order_date)->format('d/m/Y') ?? '-' }}</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Không có đơn hàng nào gần đây.</td>
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

@section('scripts')
<script>
    // Dashboard interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                if (alert && !alert.classList.contains('show')) return;
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        // Add active state to sidebar links
        const sidebarLinks = document.querySelectorAll('.nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (this.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    // Remove active class from all links
                    sidebarLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked link
                    this.classList.add('active');
                }
            });
        });
    });
</script>
@endsection