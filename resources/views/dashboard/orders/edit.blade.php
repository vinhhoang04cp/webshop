@extends('layouts.app')

@section('title', 'Cập nhật đơn hàng - WebShop Admin')

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
                    <h2>Cập nhật đơn hàng #{{ $order->order_id }}</h2>
                    <p class="text-muted mb-0">
                        <a href="{{ route('dashboard.orders.show', $order->order_id) }}" class="text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại chi tiết
                        </a>
                    </p>
                </div>
            </div>

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Cập nhật trạng thái đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('dashboard.orders.update', $order->order_id) }}">
                                @csrf
                                @method('PUT')

                                <div class="mb-4">
                                    <label class="form-label">Trạng thái hiện tại:</label>
                                    <div>
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
                                        <span class="badge bg-{{ $color }} fs-6">{{ $label }}</span>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="status" class="form-label">Trạng thái mới <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" 
                                            name="status" 
                                            required>
                                        <option value="">-- Chọn trạng thái --</option>
                                        @foreach($availableStatuses as $status => $statusLabel)
                                            <option value="{{ $status }}" {{ old('status') == $status ? 'selected' : '' }}>
                                                {{ $statusLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Chọn trạng thái mới cho đơn hàng. Chỉ hiển thị các trạng thái hợp lệ.
                                    </small>
                                </div>

                                @if(count($availableStatuses) == 0)
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Đơn hàng này đã ở trạng thái cuối cùng và không thể thay đổi.
                                    </div>
                                @endif

                                <div class="d-flex gap-2">
                                    <button type="submit" 
                                            class="btn btn-primary" 
                                            {{ count($availableStatuses) == 0 ? 'disabled' : '' }}>
                                        <i class="fas fa-save me-2"></i>Cập nhật
                                    </button>
                                    <a href="{{ route('dashboard.orders.show', $order->order_id) }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Order Timeline -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Quy trình đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-0">
                                <strong>Quy trình xử lý đơn hàng:</strong>
                                <ol class="mb-0 mt-2">
                                    <li><strong>Chờ xử lý</strong> → Có thể chuyển sang: Đang xử lý hoặc Đã hủy</li>
                                    <li><strong>Đang xử lý</strong> → Có thể chuyển sang: Đã gửi hàng hoặc Đã hủy</li>
                                    <li><strong>Đã gửi hàng</strong> → Có thể chuyển sang: Đã giao hàng</li>
                                    <li><strong>Đã giao hàng</strong> → Trạng thái cuối cùng</li>
                                    <li><strong>Đã hủy</strong> → Trạng thái cuối cùng</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin đơn hàng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Mã đơn hàng:</strong>
                                <p class="mb-0">#{{ $order->order_id }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Khách hàng:</strong>
                                <p class="mb-0">{{ $order->user->name ?? 'N/A' }}</p>
                                <small class="text-muted">{{ $order->user->email ?? '' }}</small>
                            </div>
                            <div class="mb-3">
                                <strong>Ngày đặt:</strong>
                                <p class="mb-0">{{ $order->order_date->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <strong>Tổng tiền:</strong>
                                <p class="mb-0 text-success fs-5">
                                    <strong>{{ number_format($order->total_amount) }} đ</strong>
                                </p>
                            </div>
                            <div class="mb-3">
                                <strong>Số lượng sản phẩm:</strong>
                                <p class="mb-0">{{ $order->items->count() }} sản phẩm</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
