@extends('layouts.app')

@section('title', 'Chi tiết đơn hàng - WebShop Admin')

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
                <a class="nav-link" href="#users">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                <a class="nav-link" href="#reports">
                    <i class="fas fa-chart-bar"></i> Báo cáo
                </a>
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
                    <h2>Chi tiết đơn hàng #{{ $order->order_id }}</h2>
                    <p class="text-muted mb-0">
                        <a href="{{ route('dashboard.orders.index') }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                        </a>
                    </p>
                </div>
                <div>
                    <a href="{{ route('dashboard.orders.edit', $order->order_id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Cập nhật trạng thái
                    </a>
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

            <div class="row g-4">
                <!-- Order Information -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Mã đơn hàng:</strong>
                                    <p class="mb-0">#{{ $order->order_id }}</p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Ngày đặt hàng:</strong>
                                    <p class="mb-0">{{ $order->order_date->format('d/m/Y H:i:s') }}</p>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Trạng thái:</strong>
                                    <p class="mb-0">
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger',
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Chờ xử lý',
                                                'processing' => 'Đang xử lý',
                                                'shipped' => 'Đã gửi hàng',
                                                'delivered' => 'Đã giao hàng',
                                                'cancelled' => 'Đã hủy',
                                            ];
                                            $color = $statusColors[$order->status] ?? 'secondary';
                                            $label = $statusLabels[$order->status] ?? $order->status;
                                        @endphp
                                        <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Tổng tiền:</strong>
                                    <p class="mb-0 text-success fs-5">
                                        <strong>{{ number_format($order->total_amount) }} đ</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Sản phẩm trong đơn hàng</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Sản phẩm</th>
                                            <th class="text-center">Số lượng</th>
                                            <th class="text-end">Đơn giá</th>
                                            <th class="text-end">Thành tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div>
                                                            <strong>{{ $item->product->name ?? 'N/A' }}</strong>
                                                            @if($item->productDetail)
                                                                <br>
                                                                <small class="text-muted">
                                                                    Size: {{ $item->productDetail->size }}, 
                                                                    Màu: {{ $item->productDetail->color }}
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    {{ $item->quantity }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($item->price) }} đ
                                                </td>
                                                <td class="text-end">
                                                    <strong>{{ number_format($item->quantity * $item->price) }} đ</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                            <td class="text-end">
                                                <strong class="text-success fs-5">
                                                    {{ number_format($order->total_amount) }} đ
                                                </strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin khách hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Tên khách hàng:</strong>
                                <p class="mb-0">{{ $order->user->name ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Email:</strong>
                                <p class="mb-0">{{ $order->user->email ?? 'N/A' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Số điện thoại:</strong>
                                <p class="mb-0">{{ $order->user->phone ?? 'Chưa cập nhật' }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Địa chỉ:</strong>
                                <p class="mb-0">{{ $order->user->address ?? 'Chưa cập nhật' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Status Transitions -->
                    @if(count($availableStatuses) > 0)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-tasks me-2"></i>Hành động</h5>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">Các trạng thái có thể chuyển đổi:</p>
                                @foreach($availableStatuses as $status => $label)
                                    <form method="POST" action="{{ route('dashboard.orders.update', $order->order_id) }}" 
                                          class="mb-2"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn chuyển trạng thái đơn hàng sang {{ $label }}?')">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                                            <i class="fas fa-arrow-right me-2"></i>Chuyển sang: {{ $label }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
