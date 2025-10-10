@extends('layouts.app')

@section('title', 'Thêm sản phẩm - WebShop Admin')

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
                <a class="nav-link" href="{{ route('dashboard.orders.index') }}">
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
                    <h2>Thêm sản phẩm mới</h2>
                    <p class="text-muted mb-0">Tạo sản phẩm mới cho cửa hàng</p>
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
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Thông tin sản phẩm</h5>
                        <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.products.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required 
                                           maxlength="255"
                                           placeholder="Nhập tên sản phẩm">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả sản phẩm</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description"
                                              rows="4"
                                              placeholder="Nhập mô tả chi tiết về sản phẩm">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="image_url" class="form-label">URL hình ảnh</label>
                                    <input type="url" 
                                           class="form-control @error('image_url') is-invalid @enderror" 
                                           id="image_url" 
                                           name="image_url" 
                                           value="{{ old('image_url') }}"
                                           placeholder="https://example.com/image.jpg">
                                    @error('image_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Nhập URL trực tiếp đến hình ảnh của sản phẩm</div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" 
                                               class="form-control @error('price') is-invalid @enderror" 
                                               id="price" 
                                               name="price" 
                                               value="{{ old('price') }}" 
                                               required 
                                               min="0"
                                               step="1000"
                                               placeholder="0">
                                        <span class="input-group-text">VNĐ</span>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                            id="category_id" 
                                            name="category_id" 
                                            required>
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}" 
                                                    {{ old('category_id') == $category->category_id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Preview hình ảnh -->
                                <div class="mb-3">
                                    <label class="form-label">Xem trước hình ảnh</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        <img id="image-preview" 
                                             src="" 
                                             alt="Preview" 
                                             class="img-fluid rounded"
                                             style="max-height: 200px; display: none;">
                                        <div id="no-image" class="text-muted">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <p>Nhập URL để xem trước</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Lưu sản phẩm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const imageUrlInput = document.getElementById('image_url');
    const imagePreview = document.getElementById('image-preview');
    const noImageDiv = document.getElementById('no-image');

    function updateImagePreview() {
        const url = imageUrlInput.value.trim();
        if (url) {
            imagePreview.src = url;
            imagePreview.style.display = 'block';
            noImageDiv.style.display = 'none';
            
            // Xử lý lỗi khi không thể tải hình ảnh
            imagePreview.onerror = function() {
                imagePreview.style.display = 'none';
                noImageDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i>
                    <p class="text-warning">Không thể tải hình ảnh</p>
                `;
                noImageDiv.style.display = 'block';
            };
        } else {
            imagePreview.style.display = 'none';
            noImageDiv.innerHTML = `
                <i class="fas fa-image fa-3x mb-2"></i>
                <p>Nhập URL để xem trước</p>
            `;
            noImageDiv.style.display = 'block';
        }
    }

    // Kiểm tra nếu có giá trị cũ khi load trang
    if (imageUrlInput.value) {
        updateImagePreview();
    }

    // Cập nhật preview khi người dùng nhập URL
    imageUrlInput.addEventListener('input', updateImagePreview);
    imageUrlInput.addEventListener('blur', updateImagePreview);

    // Format giá tiền
    const priceInput = document.getElementById('price');
    priceInput.addEventListener('input', function() {
        // Chỉ cho phép số
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});
</script>
@endsection