@extends('layouts.app')

@section('title', 'Chi tiết danh mục - WebShop Admin')

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
                <a class="nav-link active" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="{{ route('dashboard.orders.index') }}">
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
                    <h2>Chi tiết danh mục</h2>
                    <p class="text-muted mb-0">Thông tin chi tiết về "{{ $category->name }}"</p>
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
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin danh mục</h5>
                                <div>
                                    <a href="{{ route('dashboard.categories.edit', $category->category_id) }}" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                    </a>
                                    <a href="{{ route('dashboard.categories.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h3>{{ $category->name }}</h3>
                            <p class="text-muted mb-3">ID: #{{ $category->category_id }}</p>
                            
                            @if($category->description)
                            <div class="mb-3">
                                <h6>Mô tả:</h6>
                                <p class="text-muted">{{ $category->description }}</p>
                            </div>
                            @else
                            <div class="mb-3">
                                <p class="text-muted fst-italic">Chưa có mô tả</p>
                            </div>
                            @endif

                            <hr>

                            <div class="row">
                                <div class="col-sm-6">
                                    <strong>Ngày tạo:</strong><br>
                                    <span class="text-muted">{{ $category->created_at ? $category->created_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                                </div>
                                <div class="col-sm-6">
                                    <strong>Cập nhật lần cuối:</strong><br>
                                    <span class="text-muted">{{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i:s') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách sản phẩm trong danh mục -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Sản phẩm trong danh mục ({{ $category->products->count() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($category->products->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Chưa có sản phẩm nào trong danh mục này
                            </div>
                            @else
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th style="width: 80px;">ID</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Giá</th>
                                            <th style="width: 150px;" class="text-center">Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->products as $product)
                                        <tr>
                                            <td><strong>{{ $product->product_id }}</strong></td>
                                            <td>{{ $product->name }}</td>
                                            <td><span class="text-primary fw-bold">{{ number_format($product->price, 0, ',', '.') }} VNĐ</span></td>
                                            <td class="text-center">
                                                <a href="{{ route('dashboard.products.show', $product->product_id) }}" 
                                                   class="btn btn-sm btn-outline-info"
                                                   title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('dashboard.products.edit', $product->product_id) }}" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Thông tin bổ sung -->
                <div class="col-lg-4">
                    <!-- Thống kê -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Thống kê</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tổng sản phẩm:</span>
                                <strong class="text-primary">{{ $category->products->count() }}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Giá thấp nhất:</span>
                                <strong>{{ $category->products->count() > 0 ? number_format($category->products->min('price'), 0, ',', '.') . ' VNĐ' : 'N/A' }}</strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Giá cao nhất:</span>
                                <strong>{{ $category->products->count() > 0 ? number_format($category->products->max('price'), 0, ',', '.') . ' VNĐ' : 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin hệ thống -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Thông tin hệ thống</h6>
                        </div>
                        <div class="card-body">
                            <small>
                                <div class="mb-2">
                                    <strong>ID danh mục:</strong><br>
                                    <code>{{ $category->category_id }}</code>
                                </div>
                                <div class="mb-2">
                                    <strong>Ngày tạo:</strong><br>
                                    {{ $category->created_at ? $category->created_at->format('d/m/Y H:i:s') : 'N/A' }}
                                </div>
                                <div>
                                    <strong>Cập nhật lần cuối:</strong><br>
                                    {{ $category->updated_at ? $category->updated_at->format('d/m/Y H:i:s') : 'N/A' }}
                                </div>
                            </small>
                        </div>
                    </div>

                    <!-- Hành động nhanh -->
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Hành động nhanh</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('dashboard.categories.edit', $category->category_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa danh mục
                                </a>
                                <a href="{{ route('dashboard.products.create') }}" class="btn btn-outline-success btn-sm">
                                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                    <i class="fas fa-trash me-2"></i>Xóa danh mục
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('dashboard.categories.destroy', $category->category_id) }}">
                @csrf
                @method('DELETE')
                <div class="modal-header">
                    <h5 class="modal-title">Xóa danh mục</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Bạn có chắc chắn muốn xóa danh mục <strong>{{ $category->name }}</strong>?</p>
                    @if($category->products->count() > 0)
                    <p class="text-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Danh mục này có {{ $category->products->count() }} sản phẩm. Vui lòng xem xét trước khi xóa.
                    </p>
                    @endif
                    <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Lưu ý: Hành động này không thể hoàn tác!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
