@extends('layouts.app')

@section('title', 'Báo cáo tổng quan')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo tổng quan</h1>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-download fa-sm text-white-50"></i> Xuất báo cáo
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('dashboard.reports.export', ['type' => 'revenue']) }}">Báo cáo doanh thu</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.reports.export', ['type' => 'products']) }}">Báo cáo sản phẩm</a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.reports.export', ['type' => 'customers']) }}">Báo cáo khách hàng</a></li>
            </ul>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng doanh thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($totalRevenue, 0, ',', '.') }} đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Tổng đơn hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalOrders) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Khách hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCustomers) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Sản phẩm
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalProducts) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu hôm nay</h6>
                </div>
                <div class="card-body">
                    <div class="h4 mb-0 font-weight-bold text-success">
                        {{ number_format($todayRevenue, 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu tháng này</h6>
                </div>
                <div class="card-body">
                    <div class="h4 mb-0 font-weight-bold text-info">
                        {{ number_format($thisMonthRevenue, 0, ',', '.') }} đ
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Biểu đồ doanh thu theo tháng -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu 12 tháng gần nhất</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                            <a class="dropdown-item" href="{{ route('dashboard.reports.revenue') }}">Xem chi tiết</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyRevenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Đơn hàng theo trạng thái -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Đơn hàng theo trạng thái</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($ordersByStatus as $status)
                        <span class="mr-2">
                            <i class="fas fa-circle" style="color: {{ $loop->index == 0 ? '#1cc88a' : ($loop->index == 1 ? '#36b9cc' : ($loop->index == 2 ? '#f6c23e' : ($loop->index == 3 ? '#e74a3b' : '#858796'))) }}"></i>
                            {{ ucfirst($status->status) }} ({{ $status->count }})
                        </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top sản phẩm bán chạy -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 sản phẩm bán chạy</h6>
                    <a href="{{ route('dashboard.reports.products') }}" class="btn btn-primary btn-sm">Xem tất cả</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Đã bán</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topProducts as $product)
                                <tr>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ number_format($product->product_price, 0, ',', '.') }} đ</td>
                                    <td>{{ number_format($product->total_sold) }}</td>
                                    <td>{{ number_format($product->total_revenue, 0, ',', '.') }} đ</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation buttons -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card-body text-center">
                <a href="{{ route('dashboard.reports.revenue') }}" class="btn btn-primary mr-2">
                    <i class="fas fa-chart-line"></i> Báo cáo doanh thu chi tiết
                </a>
                <a href="{{ route('dashboard.reports.products') }}" class="btn btn-success mr-2">
                    <i class="fas fa-box"></i> Báo cáo sản phẩm
                </a>
                <a href="{{ route('dashboard.reports.customers') }}" class="btn btn-info">
                    <i class="fas fa-users"></i> Báo cáo khách hàng
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Biểu đồ doanh thu theo tháng
const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
const monthlyRevenueChart = new Chart(monthlyRevenueCtx, {
    type: 'line',
    data: {
        labels: @json($monthlyRevenue->pluck('period')),
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: @json($monthlyRevenue->pluck('revenue')),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
                    }
                }
            }
        }
    }
});

// Biểu đồ đơn hàng theo trạng thái
const orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
const orderStatusChart = new Chart(orderStatusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($ordersByStatus->pluck('status')),
        datasets: [{
            data: @json($ordersByStatus->pluck('count')),
            backgroundColor: ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
@endpush
            </div>
        </div>
    </div>
</div>
@endsection