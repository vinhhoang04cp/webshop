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
            
            <nav class="nav flex-column"> <!-- flex-column: chuyen cac item thanh cot doc -->
                <a class="nav-link active" href="{{ route('dashboard') }}"> <!-- route('dashboard'): tra ve url cua route dashboard -->
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
                <a class="nav-link" href="#products"> <!-- href="#products": lien ket den phan san pham tren trang -->
                    <i class="fas fa-box me-2"></i> Sản phẩm <!-- fas fa-box: icon hop -->
                </a>
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags me-2"></i> Danh mục <!-- fas fa-tags: icon the loai -->
                </a>
                <a class="nav-link" href="#orders">
                    <i class="fas fa-shopping-cart me-2"></i> Đơn hàng <!-- fas fa-shopping-cart: icon gio hang -->
                </a>
                <a class="nav-link" href="#users">
                    <i class="fas fa-users me-2"></i> Người dùng <!-- fas fa-users: icon nhieu nguoi -->
                </a>
                <a class="nav-link" href="#reports">
                    <i class="fas fa-chart-bar me-2"></i> Báo cáo <!-- fas fa-chart-bar: icon bieu do -->
                </a>
                
                <hr class="text-white mx-3">
                
                <a class="nav-link" href="#settings">
                    <i class="fas fa-cog me-2"></i> Cài đặt <!-- fas fa-cog: icon cai dat -->
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
                    <p class="text-muted mb-0">Welcome Back!</p>
                </div>
                <div class="d-flex align-items-center"> <!-- align-items-center: can giua theo chieu doc -->
                    <span class="badge bg-primary me-2"> <!-- me-2: margin-end 2 -->
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
            <div class="row g-4 mb-4"> <!-- $statCards la mang chua du lieu cho cac the thong ke -->
                @php
                    $statCards = [ 
                        ['value' => $productsCount, 'label' => 'Sản phẩm', 'bg' => 'primary', 'icon' => 'fa-box'],
                        ['value' => $ordersCount, 'label' => 'Đơn hàng', 'bg' => 'success', 'icon' => 'fa-shopping-cart'],
                        ['value' => $usersCount, 'label' => 'Khách hàng', 'bg' => 'warning', 'icon' => 'fa-users'],
                        ['value' => $totalRevenue, 'label' => 'Doanh thu', 'bg' => 'info', 'icon' => 'fa-chart-line', 'is_currency' => true],
                    ];
                @endphp

                @foreach ($statCards as $card)
                    <div class="col-md-3">
                        <div class="card text-white bg-{{ $card['bg'] }}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="card-title">
                                            @if(!empty($card['is_currency']))
                                                ₫{{ number_format($card['value']) }}
                                            @else
                                                {{ number_format($card['value']) }}
                                            @endif
                                        </h4>
                                        <p class="card-text">{{ $card['label'] }}</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas {{ $card['icon'] }} fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
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
                                                <td>#{{ $order->order_id }}</td>
                                                <td>{{ optional($order->user)->name ?? 'Khách vãng lai' }}</td>
                                                <td>
                                                        @php $s = $statusMap[$order->status] ?? null; @endphp
                                                        <span class="badge bg-{{ $s['label'] ?? 'secondary' }}">{{ $s['text'] ?? ucfirst($order->status ?? 'unknown') }}</span>
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

