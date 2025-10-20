@extends('layouts.app')

@section('title', 'Báo cáo sản phẩm')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
    <!-- Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Báo cáo sản phẩm</h1>
        <a href="{{ route('dashboard.reports.export', array_merge(request()->all(), ['type' => 'products'])) }}" class="btn btn-primary">
            <i class="fas fa-download fa-sm text-white-50"></i> Xuất CSV
        </a>
    </div>

    <!-- Bộ lọc -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc thời gian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.reports.products') }}">
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

    <div class="row">
        <!-- Top sản phẩm bán chạy -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Top 20 sản phẩm bán chạy (từ {{ $startDate }} đến {{ $endDate }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="topProductsTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Đã bán</th>
                                    <th>Doanh thu</th>
                                    <th>% Tổng doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalRevenue = $topProducts->sum('total_revenue');
                                @endphp
                                @foreach($topProducts as $index => $product)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td>{{ number_format($product->product_price, 0, ',', '.') }} đ</td>
                                    <td>
                                        <span class="badge badge-success">{{ number_format($product->total_sold) }}</span>
                                    </td>
                                    <td>{{ number_format($product->total_revenue, 0, ',', '.') }} đ</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" 
                                                style="width: {{ $totalRevenue > 0 ? ($product->total_revenue / $totalRevenue * 100) : 0 }}%">
                                                {{ $totalRevenue > 0 ? number_format($product->total_revenue / $totalRevenue * 100, 1) : 0 }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biểu đồ top 10 sản phẩm -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 sản phẩm (Biểu đồ)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sản phẩm theo danh mục -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Thống kê theo danh mục (từ {{ $startDate }} đến {{ $endDate }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-bordered" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Danh mục</th>
                                            <th>Số lượng bán</th>
                                            <th>Doanh thu</th>
                                            <th>% Tổng doanh thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalCategoryRevenue = $productsByCategory->sum('total_revenue');
                                        @endphp
                                        @foreach($productsByCategory as $category)
                                        <tr>
                                            <td>{{ $category->category_name }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ number_format($category->total_quantity) }}</span>
                                            </td>
                                            <td>{{ number_format($category->total_revenue, 0, ',', '.') }} đ</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                        style="width: {{ $totalCategoryRevenue > 0 ? ($category->total_revenue / $totalCategoryRevenue * 100) : 0 }}%">
                                                        {{ $totalCategoryRevenue > 0 ? number_format($category->total_revenue / $totalCategoryRevenue * 100, 1) : 0 }}%
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chart-pie">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
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
// Màu sắc cho biểu đồ
const colors = [
    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
];

// Biểu đồ top sản phẩm
const topProductsData = @json($topProducts->take(10));
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
const topProductsChart = new Chart(topProductsCtx, {
    type: 'doughnut',
    data: {
        labels: topProductsData.map(p => p.product_name.length > 20 ? p.product_name.substring(0, 20) + '...' : p.product_name),
        datasets: [{
            data: topProductsData.map(p => p.total_sold),
            backgroundColor: colors
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const product = topProductsData[context.dataIndex];
                        return product.product_name + ': ' + new Intl.NumberFormat('vi-VN').format(product.total_sold) + ' sản phẩm';
                    }
                }
            },
            legend: {
                display: false
            }
        }
    }
});

// Biểu đồ theo danh mục
const categoryData = @json($productsByCategory);
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'pie',
    data: {
        labels: categoryData.map(c => c.category_name),
        datasets: [{
            data: categoryData.map(c => c.total_revenue),
            backgroundColor: colors
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const category = categoryData[context.dataIndex];
                        return category.category_name + ': ' + new Intl.NumberFormat('vi-VN').format(category.total_revenue) + ' đ';
                    }
                }
            },
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
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