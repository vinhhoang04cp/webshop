@extends('layouts.app')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo doanh thu chi tiết</h1>
        <a href="{{ route('dashboard.reports.export', array_merge(request()->all(), ['type' => 'revenue'])) }}" class="btn btn-primary">
            <i class="fas fa-download fa-sm text-white-50"></i> Xuất CSV
        </a>
    </div>

    <!-- Bộ lọc -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.reports.revenue') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label for="start_date">Từ ngày:</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">Đến ngày:</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3">
                        <label for="group_by">Nhóm theo:</label>
                        <select class="form-control" id="group_by" name="group_by">
                            <option value="day" {{ $groupBy == 'day' ? 'selected' : '' }}>Ngày</option>
                            <option value="week" {{ $groupBy == 'week' ? 'selected' : '' }}>Tuần</option>
                            <option value="month" {{ $groupBy == 'month' ? 'selected' : '' }}>Tháng</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary form-control">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
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

        <div class="col-xl-4 col-md-6 mb-4">
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

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Giá trị đơn hàng TB
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($averageOrderValue, 0, ',', '.') }} đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Biểu đồ doanh thu -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Biểu đồ doanh thu từ {{ $startDate }} đến {{ $endDate }}
                        (Nhóm theo {{ $groupBy == 'day' ? 'ngày' : ($groupBy == 'week' ? 'tuần' : 'tháng') }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueChart" style="height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng dữ liệu chi tiết -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Dữ liệu chi tiết</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Kỳ</th>
                                    <th>Doanh thu</th>
                                    <th>Số đơn hàng</th>
                                    <th>Đơn hàng TB/ngày</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($revenueData as $data)
                                <tr>
                                    <td>{{ $data->period }}</td>
                                    <td>{{ number_format($data->revenue, 0, ',', '.') }} đ</td>
                                    <td>{{ number_format($data->order_count) }}</td>
                                    <td>
                                        @if($groupBy == 'week')
                                            {{ number_format($data->order_count / 7, 1) }}
                                        @elseif($groupBy == 'month')
                                            {{ number_format($data->order_count / 30, 1) }}
                                        @else
                                            {{ number_format($data->order_count) }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
// Biểu đồ doanh thu
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: @json($revenueData->pluck('period')),
        datasets: [{
            label: 'Doanh thu (VNĐ)',
            data: @json($revenueData->pluck('revenue')),
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }, {
            label: 'Số đơn hàng',
            data: @json($revenueData->pluck('order_count')),
            type: 'line',
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
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
                    text: '{{ $groupBy == "day" ? "Ngày" : ($groupBy == "week" ? "Tuần" : "Tháng") }}'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Doanh thu (VNĐ)'
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
                },
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        if (context.datasetIndex === 0) {
                            return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
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