@extends('layouts.app')

@section('title', 'Chi tiết sản phẩm - WebShop Admin')

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
                <a class="nav-link active" href="{{ route('dashboard.products.index') }}">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="#orders">
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
                    <h2>Chi tiết sản phẩm</h2>
                    <p class="text-muted mb-0">Thông tin chi tiết về "{{ $product['name'] }}"</p>
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

            <div class="row">
                <!-- Thông tin chính -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin sản phẩm</h5>
                                <div>
                                    <a href="{{ route('dashboard.products.edit', $product['id']) }}" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                    </a>
                                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h3>{{ $product['name'] }}</h3>
                                    <p class="text-muted mb-3">ID: #{{ $product['id'] }}</p>
                                    
                                    <div class="mb-3">
                                        <h5 class="text-primary">{{ number_format($product['price'], 0, ',', '.') }} VNĐ</h5>
                                    </div>

                                    @if($product['description'])
                                    <div class="mb-3">
                                        <h6>Mô tả:</h6>
                                        <p class="text-muted">{{ $product['description'] }}</p>
                                    </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <strong>Danh mục:</strong><br>
                                            <span class="badge bg-secondary">{{ $product['category']['name'] ?? 'Chưa có danh mục' }}</span>
                                        </div>
                                        <div class="col-sm-6">
                                            <strong>Trạng thái:</strong><br>
                                            <span class="badge bg-success">Đang bán</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    @if($product['image_url'])
                                    <div class="text-center">
                                        <img src="{{ $product['image_url'] }}" 
                                             alt="{{ $product['name'] }}" 
                                             class="img-fluid rounded border"
                                             style="max-height: 300px;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <div class="text-muted mt-3" style="display: none;">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <p>Không thể tải hình ảnh</p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p>Chưa có hình ảnh</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin bổ sung -->
                <div class="col-lg-4">
                    <!-- Thông tin hệ thống -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Thông tin hệ thống</h6>
                        </div>
                        <div class="card-body">
                            <small>
                                <strong>Ngày tạo:</strong><br>
                                {{ isset($product['created_at']) ? \Carbon\Carbon::parse($product['created_at'])->format('d/m/Y H:i:s') : 'N/A' }}<br><br>
                                
                                <strong>Cập nhật lần cuối:</strong><br>
                                {{ isset($product['updated_at']) ? \Carbon\Carbon::parse($product['updated_at'])->format('d/m/Y H:i:s') : 'N/A' }}<br><br>

                                @if(isset($product['image_url']) && $product['image_url'])
                                <strong>URL hình ảnh:</strong><br>
                                <a href="{{ $product['image_url'] }}" target="_blank" class="text-truncate d-block" style="max-width: 200px;">
                                    {{ $product['image_url'] }}
                                </a>
                                @endif
                            </small>
                        </div>
                    </div>

                    <!-- Hành động -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-tools me-2"></i>Hành động</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('dashboard.products.edit', $product['id']) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa sản phẩm
                                </a>
                                
                                <button class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteProductModal">
                                    <i class="fas fa-trash me-2"></i>Xóa sản phẩm
                                </button>
                                
                                <hr>
                                
                                <a href="{{ route('dashboard.products.create') }}" class="btn btn-outline-success">
                                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
                                </a>
                                
                                <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list me-2"></i>Danh sách sản phẩm
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.products.destroy', $product['id']) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                        Xác nhận xóa sản phẩm
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-warning me-2"></i>
                        <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!
                    </div>
                    
                    <p>Bạn có chắc chắn muốn xóa sản phẩm <strong>"{{ $product['name'] }}"</strong>?</p>
                    
                    <div class="bg-light p-3 rounded">
                        <small class="text-muted">
                            <strong>Thông tin sản phẩm sẽ bị xóa:</strong><br>
                            • ID: #{{ $product['id'] }}<br>
                            • Tên: {{ $product['name'] }}<br>
                            • Giá: {{ number_format($product['price'], 0, ',', '.') }} VNĐ<br>
                            • Danh mục: {{ $product['category']['name'] ?? 'N/A' }}
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Hủy
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Xóa sản phẩm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection