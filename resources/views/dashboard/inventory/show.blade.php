@extends('layouts.app')
@section('title', 'Chi tiết tồn kho - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Chi tiết tồn kho</h2>
                    <p class="text-muted mb-0">Thông tin chi tiết về tồn kho sản phẩm</p>
                </div>
            </div>

            @include('components.alerts')

            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin tồn kho</h5>
                                <div>
                                    <a href="{{ route('dashboard.inventory.edit', $inventory->inventory_id) }}" class="btn btn-outline-primary me-2">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa
                                    </a>
                                    <a href="{{ route('dashboard.inventory.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">ID Tồn kho:</strong>
                                </div>
                                <div class="col-md-8">
                                    #{{ $inventory->inventory_id }}
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Sản phẩm:</strong>
                                </div>
                                <div class="col-md-8">
                                    @if($inventory->product)
                                        <a href="{{ route('dashboard.products.show', $inventory->product->product_id) }}" class="text-decoration-none">
                                            <strong>{{ $inventory->product->name }}</strong>
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Danh mục:</strong>
                                </div>
                                <div class="col-md-8">
                                    @if($inventory->product && $inventory->product->category)
                                        <span class="badge bg-secondary">{{ $inventory->product->category->name }}</span>
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Tổng nhập kho:</strong>
                                </div>
                                <div class="col-md-8">
                                    <span class="badge bg-info fs-6">{{ number_format($inventory->stock_in) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Tổng xuất kho:</strong>
                                </div>
                                <div class="col-md-8">
                                    <span class="badge bg-warning fs-6">{{ number_format($inventory->stock_out) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Tồn kho hiện tại:</strong>
                                </div>
                                <div class="col-md-8">
                                    <h4>
                                        <span class="badge bg-primary">{{ number_format($inventory->current_stock) }}</span>
                                    </h4>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Trạng thái:</strong>
                                </div>
                                <div class="col-md-8">
                                    @if($inventory->current_stock == 0)
                                        <span class="badge bg-danger">Hết hàng</span>
                                    @elseif($inventory->current_stock < 10)
                                        <span class="badge bg-warning">Sắp hết (< 10)</span>
                                    @else
                                        <span class="badge bg-success">Còn hàng</span>
                                    @endif
                                </div>
                            </div>

                            <hr>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <strong class="text-muted">Cập nhật lần cuối:</strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $inventory->updated_at ? $inventory->updated_at->format('d/m/Y H:i:s') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Điều chỉnh tồn kho -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Điều chỉnh tồn kho</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('dashboard.inventory.adjust', $inventory->inventory_id) }}">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Loại điều chỉnh</label>
                                    <select name="adjustment_type" class="form-select" required>
                                        <option value="in">Nhập kho</option>
                                        <option value="out">Xuất kho</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Số lượng</label>
                                    <input type="number" name="quantity" class="form-control" min="1" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Ghi chú (tùy chọn)</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Lý do điều chỉnh..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-2"></i>Thực hiện điều chỉnh
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Thông tin sản phẩm -->
                    @if($inventory->product)
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-box me-2"></i>Thông tin sản phẩm</h5>
                        </div>
                        <div class="card-body">
                            @if($inventory->product->image_url)
                                <img src="{{ $inventory->product->image_url }}" alt="{{ $inventory->product->name }}" class="img-fluid rounded mb-3" style="max-height: 200px; width: 100%; object-fit: cover;">
                            @endif
                            
                            <h6>{{ $inventory->product->name }}</h6>
                            <p class="text-muted small mb-2">{{ Str::limit($inventory->product->description, 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold">{{ number_format($inventory->product->price) }} VNĐ</span>
                                <a href="{{ route('dashboard.products.show', $inventory->product->product_id) }}" class="btn btn-sm btn-outline-info">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
