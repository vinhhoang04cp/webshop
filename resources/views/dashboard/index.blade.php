@extends('layouts.app')
@section('title', 'Dashboard - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Tổng quan hệ thống</h2>
                    <p class="text-muted mb-0">Chào mừng trở lại, {{ $user->name }}!</p>
                </div>
            </div>
            @include('components.alerts')
            <div class="row g-4 mb-4">
                @php
                    $statCards = [ 
                        ['value' => $productsCount, 'label' => 'Sản phẩm', 'icon' => 'fa-box', 'gradient' => 'linear-gradient(135deg, #667eea, #764ba2)'],
                        ['value' => $ordersCount, 'label' => 'Đơn hàng', 'icon' => 'fa-shopping-cart', 'gradient' => 'linear-gradient(135deg, #f093fb, #f5576c)'],
                        ['value' => $usersCount, 'label' => 'Khách hàng', 'icon' => 'fa-users', 'gradient' => 'linear-gradient(135deg, #4facfe, #00f2fe)'],
                        ['value' => number_format($totalRevenue) . ' đ', 'label' => 'Doanh thu', 'icon' => 'fa-chart-line', 'gradient' => 'linear-gradient(135deg, #43e97b, #38f9d7)'],
                    ];
                @endphp
                @foreach ($statCards as $card)
                <div class="col-md-6 col-lg-3">
                    <div class="stat-card" style="background: {{ $card['gradient'] }};">
                        <div class="stat-icon"><i class="fas {{ $card['icon'] }}"></i></div>
                        <h3>{{ $card['value'] }}</h3>
                        <p>{{ $card['label'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Đơn hàng gần đây</h5>
                            <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả <i class="fas fa-arrow-right ms-1"></i></a>
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
                                        @if(isset($error))
                                            <tr><td colspan="5" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>{{ $error }}</td></tr>
                                        @elseif(empty($recentOrders))
                                            <tr><td colspan="5" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Không có đơn hàng nào gần đây</td></tr>
                                        @else
                                            @foreach($recentOrders as $order)
                                            <tr>
                                                <td><strong>#{{ $order['order_id'] ?? $order['id'] }}</strong></td>
                                                <td>{{ $order['user']['name'] ?? 'Khách vãng lai' }}</td>
                                                <td>
                                                    @php 
                                                        $status = $order['status'] ?? 'pending';
                                                        $s = $statusMap[$status] ?? null; 
                                                    @endphp
                                                    <span class="badge bg-{{ $s['label'] ?? 'secondary' }}">{{ $s['text'] ?? ucfirst($status) }}</span>
                                                </td>
                                                <td><strong>{{ number_format($order['total_amount'] ?? 0) }} đ</strong></td>
                                                <td class="text-muted">{{ isset($order['order_date']) ? \Carbon\Carbon::parse($order['order_date'])->format('d/m/Y H:i') : '-' }}</td>
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bell me-2"></i>Hoạt động hệ thống</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 p-2 border rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-plus text-success me-2"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-semibold">Người dùng mới</h6>
                                        <small class="text-muted">5 người dùng mới đăng ký</small>
                                    </div>
                                    <small class="text-muted">2h</small>
                                </div>
                            </div>
                            <div class="mb-3 p-2 border rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-box text-primary me-2"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-semibold">Sản phẩm mới</h6>
                                        <small class="text-muted">3 sản phẩm được thêm</small>
                                    </div>
                                    <small class="text-muted">4h</small>
                                </div>
                            </div>
                            <div class="p-2 border rounded">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-chart-line text-warning me-2"></i>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-semibold">Báo cáo tuần</h6>
                                        <small class="text-muted">Báo cáo doanh thu tuần đã sẵn sàng</small>
                                    </div>
                                    <small class="text-muted">1d</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

