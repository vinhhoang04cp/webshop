@extends('layouts.app')

@section('title', 'Đơn hàng - WebShop Admin')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        
        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
            <div class="dashboard-header">
                <div>
                    <h2>Quản lý đơn hàng</h2>
                    <p class="text-muted mb-0">Theo dõi và quản lý đơn hàng của khách hàng</p>
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

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách đơn hàng</h5>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="search-box">
                                <input name="search" class="form-control form-control-sm" 
                                       placeholder="Tìm kiếm theo mã đơn, tên hoặc email..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                                @if(request('search') || request('status') || request('payment_status'))
                                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times"></i> Xóa
                                    </a>
                                @endif
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" action="{{ route('dashboard.orders.index') }}">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                                @if(request('payment_status'))
                                    <input type="hidden" name="payment_status" value="{{ request('payment_status') }}">
                                @endif
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Tất cả trạng thái</option>
                                    @foreach($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET" action="{{ route('dashboard.orders.index') }}">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                                @if(request('status'))
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                @endif
                                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">Tất cả thanh toán</option>
                                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thất bại</option>
                                    <option value="refunded" {{ request('payment_status') == 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-2 text-end">
                            <small class="text-muted">
                                Tổng: {{ $orders->total() }} đơn hàng
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($orders) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Khách hàng</th>
                                        <th>Ngày đặt</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thanh toán</th>
                                        <th>PT Thanh toán</th>
                                        <th class="text-center">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong>#{{ $order->order_id }}</strong>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $order->user->name ?? 'N/A' }}</strong>
                                                    @if($order->shipping_name && $order->shipping_name != $order->user->name)
                                                        <span class="badge bg-info ms-1" style="font-size: 0.7rem;">
                                                            <i class="fas fa-shipping-fast"></i> {{ $order->shipping_name }}
                                                        </span>
                                                    @endif
                                                </div>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope me-1"></i>{{ $order->user->email ?? '' }}
                                                </small>
                                                @if($order->shipping_phone)
                                                    <br>
                                                    <small class="text-success">
                                                        <i class="fas fa-phone me-1"></i>{{ $order->shipping_phone }}
                                                    </small>
                                                @endif
                                                @if($order->shipping_address)
                                                    <br>
                                                    <small class="text-muted" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top" 
                                                           title="{{ $order->shipping_address }}">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        {{ Str::limit($order->shipping_address, 30) }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $order->order_date->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                <strong>{{ number_format($order->total_amount) }} đ</strong>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'shipped' => 'primary',
                                                        'delivered' => 'success',
                                                        'cancelled' => 'danger',
                                                    ];
                                                    $color = $statusColors[$order->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ $statuses[$order->status] ?? $order->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @php
                                                    $paymentStatusColors = [
                                                        'pending' => 'warning',
                                                        'paid' => 'success',
                                                        'failed' => 'danger',
                                                        'refunded' => 'secondary',
                                                    ];
                                                    $paymentStatusLabels = [
                                                        'pending' => 'Chưa thanh toán',
                                                        'paid' => 'Đã thanh toán',
                                                        'failed' => 'Thất bại',
                                                        'refunded' => 'Đã hoàn tiền',
                                                    ];
                                                    $paymentColor = $paymentStatusColors[$order->payment_status] ?? 'secondary';
                                                    $paymentLabel = $paymentStatusLabels[$order->payment_status] ?? $order->payment_status;
                                                @endphp
                                                <span class="badge bg-{{ $paymentColor }}">
                                                    {{ $paymentLabel }}
                                                </span>
                                                @if($order->paid_at)
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock me-1"></i>{{ $order->paid_at->format('d/m/Y H:i') }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $paymentMethodLabels = [
                                                        'cod' => 'COD',
                                                        'vnpay' => 'VNPay',
                                                        'bank_transfer' => 'Chuyển khoản',
                                                        'momo' => 'MoMo',
                                                    ];
                                                    $methodLabel = $paymentMethodLabels[$order->payment_method] ?? ($order->payment_method ?: 'COD');
                                                @endphp
                                                @if($order->payment_method == 'cod' || !$order->payment_method)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-money-bill-wave me-1"></i>{{ $methodLabel }}
                                                    </span>
                                                @elseif($order->payment_method == 'vnpay')
                                                    <span class="badge bg-primary">
                                                        <i class="fas fa-credit-card me-1"></i>{{ $methodLabel }}
                                                    </span>
                                                @elseif($order->payment_method == 'momo')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-mobile-alt me-1"></i>{{ $methodLabel }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-university me-1"></i>{{ $methodLabel }}
                                                    </span>
                                                @endif
                                                @if($order->transaction_id)
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-hashtag me-1"></i>{{ Str::limit($order->transaction_id, 15) }}
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('dashboard.orders.show', $order->order_id) }}" 
                                                       class="btn btn-outline-info" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('dashboard.orders.edit', $order->order_id) }}" 
                                                       class="btn btn-outline-primary" title="Cập nhật trạng thái">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(in_array($order->status, ['cancelled', 'delivered']))
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                title="Xóa"
                                                                onclick="confirmDelete({{ $order->order_id }})">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    Hiển thị {{ $orders->firstItem() }} - {{ $orders->lastItem() }} 
                                    trong tổng số {{ $orders->total() }} đơn hàng
                                </div>
                                <div>
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Không tìm thấy đơn hàng nào.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa đơn hàng này?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(orderId) {
    const form = document.getElementById('deleteForm');
    form.action = `/dashboard/orders/${orderId}`;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

// Kích hoạt Bootstrap tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

<style>
    /* Styling cho thông tin khách hàng trong bảng */
    table td small {
        display: block;
        line-height: 1.4;
        margin-top: 2px;
    }
    
    table td small i {
        width: 14px;
        text-align: center;
    }
    
    .text-success {
        color: #10b981 !important;
    }
    
    .badge.bg-info {
        background-color: #3b82f6 !important;
    }
    
    /* Styling cho cột thanh toán */
    table th:nth-child(6),
    table th:nth-child(7) {
        min-width: 130px;
    }
    
    table td:nth-child(6) small,
    table td:nth-child(7) small {
        font-size: 0.75rem;
        margin-top: 4px;
    }
    
    /* Badges trong payment */
    .badge {
        font-size: 0.75rem;
        padding: 0.35rem 0.65rem;
        font-weight: 500;
    }
    
    .badge i {
        font-size: 0.7rem;
    }
</style>
            </div>
        </div>
    </div>
</div>
@endsection
