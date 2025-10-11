@extends('layouts.app')

@section('title', 'Đơn hàng - WebShop Admin')

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
                <a class="nav-link active" href="{{ route('dashboard.orders.index') }}">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
                @if(auth()->user()->isAdmin())
                <a class="nav-link" href="{{ route('dashboard.users.index') }}">
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
                        <div class="col-md-5">
                            <form method="GET" action="{{ route('dashboard.orders.index') }}" class="search-box">
                                <input name="search" class="form-control form-control-sm" 
                                       placeholder="Tìm kiếm theo mã đơn, tên hoặc email..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                                @if(request('search') || request('status'))
                                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-times"></i> Xóa
                                    </a>
                                @endif
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form method="GET" action="{{ route('dashboard.orders.index') }}">
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
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
                        <div class="col-md-3 text-end">
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
                                                <div>{{ $order->user->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $order->user->email ?? '' }}</small>
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
</script>
@endsection
