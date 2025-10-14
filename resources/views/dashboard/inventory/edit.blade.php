@extends('layouts.app')
@section('title', 'Chỉnh sửa tồn kho - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Chỉnh sửa tồn kho</h2>
                    <p class="text-muted mb-0">Cập nhật thông tin tồn kho sản phẩm</p>
                </div>
            </div>

            @include('components.alerts')

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa thông tin tồn kho</h5>
                        <div>
                            <a href="{{ route('dashboard.inventory.show', $inventory->inventory_id) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>Xem chi tiết
                            </a>
                            <a href="{{ route('dashboard.inventory.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.inventory.update', $inventory->inventory_id) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Sản phẩm:</strong> {{ $inventory->product->name ?? 'N/A' }}
                                    @if($inventory->product && $inventory->product->category)
                                        <br><strong>Danh mục:</strong> {{ $inventory->product->category->name }}
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock_in" class="form-label">
                                        Tổng nhập kho <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('stock_in') is-invalid @enderror" 
                                           id="stock_in" 
                                           name="stock_in" 
                                           value="{{ old('stock_in', $inventory->stock_in) }}" 
                                           min="0"
                                           required>
                                    @error('stock_in')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tổng số lượng sản phẩm đã nhập vào kho</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="stock_out" class="form-label">
                                        Tổng xuất kho <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('stock_out') is-invalid @enderror" 
                                           id="stock_out" 
                                           name="stock_out" 
                                           value="{{ old('stock_out', $inventory->stock_out) }}" 
                                           min="0"
                                           required>
                                    @error('stock_out')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Tổng số lượng sản phẩm đã xuất khỏi kho</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_stock" class="form-label">
                                        Tồn kho hiện tại <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('current_stock') is-invalid @enderror" 
                                           id="current_stock" 
                                           name="current_stock" 
                                           value="{{ old('current_stock', $inventory->current_stock) }}" 
                                           min="0"
                                           required>
                                    @error('current_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Số lượng sản phẩm hiện có trong kho</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái tồn kho</label>
                                    <div class="p-3 border rounded">
                                        @if($inventory->current_stock == 0)
                                            <span class="badge bg-danger fs-6">Hết hàng</span>
                                        @elseif($inventory->current_stock < 10)
                                            <span class="badge bg-warning fs-6">Sắp hết (< 10)</span>
                                        @else
                                            <span class="badge bg-success fs-6">Còn hàng</span>
                                        @endif
                                        <div class="text-muted small mt-2">
                                            Cập nhật lần cuối: {{ $inventory->updated_at ? $inventory->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Lưu ý:</strong> Hãy chắc chắn rằng các số liệu nhập vào là chính xác. 
                            Việc cập nhật trực tiếp các giá trị này có thể ảnh hưởng đến báo cáo và quản lý kho.
                        </div>

                        <hr>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('dashboard.inventory.show', $inventory->inventory_id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Thông tin sản phẩm liên quan -->
            @if($inventory->product)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-box me-2"></i>Thông tin sản phẩm liên quan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            @if($inventory->product->image_url)
                                <img src="{{ $inventory->product->image_url }}" alt="{{ $inventory->product->name }}" class="img-fluid rounded">
                            @else
                                <div class="bg-light rounded text-center py-5">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-10">
                            <h5>{{ $inventory->product->name }}</h5>
                            <p class="text-muted">{{ $inventory->product->description }}</p>
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Giá:</strong> {{ number_format($inventory->product->price) }} VNĐ
                                </div>
                                <div class="col-md-3">
                                    <strong>Danh mục:</strong> 
                                    @if($inventory->product->category)
                                        <span class="badge bg-secondary">{{ $inventory->product->category->name }}</span>
                                    @else
                                        -
                                    @endif
                                </div>
                                <div class="col-md-6 text-end">
                                    <a href="{{ route('dashboard.products.show', $inventory->product->product_id) }}" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-eye me-2"></i>Xem chi tiết sản phẩm
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
