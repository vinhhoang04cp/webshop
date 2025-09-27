@extends('layouts.app')

@section('title', 'Chi tiết danh mục: ' . $category->name)

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-eye me-2"></i>Chi tiết danh mục: <strong>{{ $category->name }}</strong>
            </h1>
            <div>
                <a href="{{ route('categories.edit', $category->category_id) }}" class="btn btn-warning me-2">
                    <i class="fas fa-edit me-1"></i>Chỉnh sửa
                </a>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Category Information -->
            <div class="col-md-8">
                <!-- Basic Info Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">ID Danh mục</h6>
                                <p class="h5 text-primary">{{ $category->category_id }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">Tên danh mục</h6>
                                <p class="h5">{{ $category->name }}</p>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">Mô tả</h6>
                            @if($category->description)
                                <p class="lead">{{ $category->description }}</p>
                            @else
                                <p class="text-muted fst-italic">Chưa có mô tả cho danh mục này.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Products in Category -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-boxes me-2"></i>Sản phẩm trong danh mục
                            <span class="badge bg-info ms-2">{{ $category->products->count() }}</span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($category->products->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên sản phẩm</th>
                                            <th>Giá</th>
                                            <th>Tồn kho</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($category->products as $product)
                                            <tr>
                                                <td class="text-muted">#{{ $product->product_id }}</td>
                                                <td>
                                                    <strong>{{ $product->name }}</strong>
                                                    @if($product->description)
                                                        <br><small class="text-muted">{{ Str::limit($product->description, 60) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($product->price)
                                                        <span class="text-success fw-bold">{{ number_format($product->price, 0, ',', '.') }}đ</span>
                                                    @else
                                                        <span class="text-muted">Chưa có giá</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($product->inventory && $product->inventory->quantity !== null)
                                                        @if($product->inventory->quantity > 0)
                                                            <span class="badge bg-success">{{ $product->inventory->quantity }}</span>
                                                        @else
                                                            <span class="badge bg-danger">Hết hàng</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">Chưa xác định</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có sản phẩm nào</h5>
                                <p class="text-muted">Danh mục này chưa có sản phẩm nào. Hãy thêm sản phẩm vào danh mục.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Side Panel -->
            <div class="col-md-4">
                <!-- Statistics Card -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Thống kê
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12 mb-3">
                                <div class="border rounded p-3">
                                    <h3 class="text-primary mb-1">{{ $category->products->count() }}</h3>
                                    <small class="text-muted">Tổng sản phẩm</small>
                                </div>
                            </div>
                        </div>
                        
                        @if($category->products->count() > 0)
                            <hr>
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <h5 class="text-success mb-1">{{ $category->products->where('inventory.quantity', '>', 0)->count() }}</h5>
                                        <small class="text-muted">Còn hàng</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="border rounded p-2">
                                        <h5 class="text-danger mb-1">{{ $category->products->where('inventory.quantity', '<=', 0)->count() }}</h5>
                                        <small class="text-muted">Hết hàng</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Thao tác
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('categories.edit', $category->category_id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Chỉnh sửa danh mục
                            </a>
                            
                            @if($category->products->count() == 0)
                                <form action="{{ route('categories.destroy', $category->category_id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-1"></i>Xóa danh mục
                                    </button>
                                </form>
                            @else
                                <button type="button" 
                                        class="btn btn-danger" 
                                        disabled
                                        title="Không thể xóa danh mục có sản phẩm">
                                    <i class="fas fa-trash me-1"></i>Không thể xóa
                                </button>
                            @endif
                            
                            <a href="{{ route('categories.create') }}" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Thêm danh mục mới
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status Card -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info me-2"></i>Trạng thái
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($category->products->count() > 0)
                            <div class="alert alert-success mb-2">
                                <i class="fas fa-check-circle me-2"></i>
                                <strong>Đang hoạt động</strong><br>
                                <small>Danh mục này đang được sử dụng</small>
                            </div>
                        @else
                            <div class="alert alert-warning mb-2">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Chưa có sản phẩm</strong><br>
                                <small>Danh mục này chưa có sản phẩm nào</small>
                            </div>
                        @endif
                        
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Xem lúc: {{ now()->format('d/m/Y H:i:s') }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Refresh statistics every 30 seconds
    setInterval(function() {
        // In a real application, you might want to use AJAX to refresh statistics
        // For now, we'll just update the view timestamp
        document.querySelector('small.text-muted:last-child').innerHTML = 
            '<i class="fas fa-clock me-1"></i>Xem lúc: ' + new Date().toLocaleString('vi-VN');
    }, 30000);
    
    // Enable tooltips for disabled buttons
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"], [title]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush
