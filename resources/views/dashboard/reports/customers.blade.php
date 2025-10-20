@extends('layouts.app')

@section('title', 'Báo cáo khách hàng')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo khách hàng</h1>
        <a href="{{ route('dashboard.reports.export', array_merge(request()->all(), ['type' => 'customers'])) }}" class="btn btn-primary">
            <i class="fas fa-download fa-sm text-white-50"></i> Xuất CSV
        </a>
    </div>

    <!-- Bộ lọc -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc thời gian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.reports.customers') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="start_date">Từ ngày:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date">Đến ngày:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-4">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng quan khách hàng -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Khách hàng mới
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($newCustomers) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                KH có đơn hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($activeCustomers) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tỷ lệ chuyển đổi
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $newCustomers > 0 ? number_format(($activeCustomers / $newCustomers) * 100, 1) : 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top khách hàng -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Top 20 khách hàng VIP (từ {{ $startDate }} đến {{ $endDate }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="topCustomersTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên khách hàng</th>
                                    <th>Email</th>
                                    <th>Số đơn hàng</th>
                                    <th>Tổng chi tiêu</th>
                                    <th>TB/đơn hàng</th>
                                    <th>Hạng KH</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topCustomers as $index => $customer)
                                @php
                                    $avgOrder = $customer->total_orders > 0 ? $customer->total_spent / $customer->total_orders : 0;
                                    
                                    // Phân hạng khách hàng
                                    if ($customer->total_spent >= 50000000) {
                                        $rank = 'VIP';
                                        $badgeClass = 'badge-warning';
                                    } elseif ($customer->total_spent >= 20000000) {
                                        $rank = 'Gold';
                                        $badgeClass = 'badge-success';
                                    } elseif ($customer->total_spent >= 10000000) {
                                        $rank = 'Silver';
                                        $badgeClass = 'badge-info';
                                    } else {
                                        $rank = 'Bronze';
                                        $badgeClass = 'badge-secondary';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $customer->name }}</strong>
                                    </td>
                                    <td>{{ $customer->email }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ number_format($customer->total_orders) }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($customer->total_spent, 0, ',', '.') }} đ</strong>
                                    </td>
                                    <td>{{ number_format($avgOrder, 0, ',', '.') }} đ</td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $rank }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Phân tích khách hàng -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Phân bố khách hàng theo hạng</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="customerRankChart"></canvas>
                    </div>
                    @php
                        $vipCount = $topCustomers->where('total_spent', '>=', 50000000)->count();
                        $goldCount = $topCustomers->where('total_spent', '>=', 20000000)->where('total_spent', '<', 50000000)->count();
                        $silverCount = $topCustomers->where('total_spent', '>=', 10000000)->where('total_spent', '<', 20000000)->count();
                        $bronzeCount = $topCustomers->where('total_spent', '<', 10000000)->count();
                    @endphp
                    <div class="mt-4 text-center small">
                        <span class="mr-2">
                            <i class="fas fa-circle text-warning"></i> VIP ({{ $vipCount }})
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-success"></i> Gold ({{ $goldCount }})
                        </span>
                        <br>
                        <span class="mr-2">
                            <i class="fas fa-circle text-info"></i> Silver ({{ $silverCount }})
                        </span>
                        <span class="mr-2">
                            <i class="fas fa-circle text-secondary"></i> Bronze ({{ $bronzeCount }})
                        </span>
                    </div>
                </div>
            </div>

            <!-- Chi tiêu trung bình -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê chi tiêu</h6>
                </div>
                <div class="card-body">
                    @php
                        $totalSpent = $topCustomers->sum('total_spent');
                        $avgSpentPerCustomer = $topCustomers->count() > 0 ? $totalSpent / $topCustomers->count() : 0;
                        $maxSpent = $topCustomers->max('total_spent');
                        $minSpent = $topCustomers->min('total_spent');
                    @endphp
                    <div class="mb-3">
                        <div class="small text-gray-500">Chi tiêu trung bình:</div>
                        <div class="h6 text-primary">{{ number_format($avgSpentPerCustomer, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">Chi tiêu cao nhất:</div>
                        <div class="h6 text-success">{{ number_format($maxSpent, 0, ',', '.') }} đ</div>
                    </div>
                    <div class="mb-3">
                        <div class="small text-gray-500">Chi tiêu thấp nhất:</div>
                        <div class="h6 text-info">{{ number_format($minSpent, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ chi tiêu khách hàng -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Biểu đồ chi tiêu top 10 khách hàng</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="customerSpendingChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back button -->
    <div class="row">
        <div class="col-lg-12">
            <a href="{{ route('dashboard.reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại tổng quan
            </a>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Dữ liệu khách hàng
const topCustomersData = @json($topCustomers->take(10));

// Biểu đồ phân hạng khách hàng
const customerRankCtx = document.getElementById('customerRankChart').getContext('2d');
const customerRankChart = new Chart(customerRankCtx, {
    type: 'doughnut',
    data: {
        labels: ['VIP', 'Gold', 'Silver', 'Bronze'],
        datasets: [{
            data: [{{ $vipCount }}, {{ $goldCount }}, {{ $silverCount }}, {{ $bronzeCount }}],
            backgroundColor: ['#f6c23e', '#1cc88a', '#36b9cc', '#858796']
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

// Biểu đồ chi tiêu khách hàng
const customerSpendingCtx = document.getElementById('customerSpendingChart').getContext('2d');
const customerSpendingChart = new Chart(customerSpendingCtx, {
    type: 'bar',
    data: {
        labels: topCustomersData.map(c => c.name.length > 15 ? c.name.substring(0, 15) + '...' : c.name),
        datasets: [{
            label: 'Tổng chi tiêu (VNĐ)',
            data: topCustomersData.map(c => c.total_spent),
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            borderColor: 'rgba(28, 200, 138, 1)',
            borderWidth: 1
        }, {
            label: 'Số đơn hàng',
            data: topCustomersData.map(c => c.total_orders),
            type: 'line',
            borderColor: 'rgba(231, 74, 59, 1)',
            backgroundColor: 'rgba(231, 74, 59, 0.1)',
            yAxisID: 'y1',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                display: true,
                title: {
                    display: true,
                    text: 'Khách hàng'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Tổng chi tiêu (VNĐ)'
                },
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                    }
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Số đơn hàng'
                },
                grid: {
                    drawOnChartArea: false,
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return 'Chi tiêu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
                        } else {
                            return 'Đơn hàng: ' + context.parsed.y;
                        }
                    }
                }
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