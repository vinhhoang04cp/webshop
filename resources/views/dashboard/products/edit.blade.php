@extends('layouts.app')

@section('title', 'Chỉnh sửa sản phẩm - WebShop Admin')

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
                @if(auth()->user()->isAdmin())
                <a class="nav-link" href="{{ route('dashboard.users.index') }}">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                @endif
            </nav>
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
                    <h2>Chỉnh sửa sản phẩm</h2>
                    <p class="text-muted mb-0">Cập nhật thông tin sản phẩm "{{ $product->name }}"</p>
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
                        <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Chỉnh sửa thông tin sản phẩm</h5>
                        <div>
                            <a href="{{ route('dashboard.products.show', $product->product_id) }}" class="btn btn-outline-info me-2">
                                <i class="fas fa-eye me-2"></i>Xem chi tiết
                            </a>
                            <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab">
                                <i class="fas fa-cogs me-2"></i>Chi tiết sản phẩm
                            </button>
                        </li>
                    </ul>

                    <form method="POST" action="{{ route('dashboard.products.update', $product->product_id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Tab content -->
                        <div class="tab-content" id="productTabContent">
                            <!-- Tab 1: Thông tin cơ bản -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $product->name) }}" 
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
                                              placeholder="Nhập mô tả chi tiết về sản phẩm">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="image" class="form-label">Tải lên ảnh mới</label>
                                            <input type="file" 
                                                   class="form-control @error('image') is-invalid @enderror" 
                                                   id="image" 
                                                   name="image" 
                                                   accept="image/*">
                                            <div class="form-text">Chấp nhận: JPG, JPEG, PNG, GIF. Tối đa: 2MB</div>
                                            @error('image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="image_url" class="form-label">Hoặc URL hình ảnh</label>
                                            <input type="url" 
                                                   class="form-control @error('image_url') is-invalid @enderror" 
                                                   id="image_url" 
                                                   name="image_url" 
                                                   value="{{ old('image_url', $product->image_url) }}"
                                                   placeholder="https://example.com/image.jpg">
                                            @error('image_url')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Hoặc nhập URL trực tiếp đến hình ảnh</div>
                                        </div>
                                    </div>
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
                                               value="{{ old('price', $product->price) }}" 
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
                                                    {{ old('category_id', $product->category_id) == $category->category_id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho <span class="text-danger">*</span></label>
                                    <input type="number" 
                                           class="form-control @error('stock_quantity') is-invalid @enderror" 
                                           id="stock_quantity" 
                                           name="stock_quantity" 
                                           value="{{ old('stock_quantity', $product->stock_quantity) }}" 
                                           required 
                                           min="0"
                                           step="1"
                                           placeholder="0">
                                    @error('stock_quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Nhập số lượng sản phẩm có sẵn trong kho</small>
                                </div>

                                <!-- Preview hình ảnh -->
                                <div class="mb-3">
                                    <label class="form-label">Xem trước hình ảnh</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        <img id="image-preview" 
                                             src="{{ $product->image_url }}" 
                                             alt="Preview" 
                                             class="img-fluid rounded"
                                             style="max-height: 200px; {{ $product->image_url ? '' : 'display: none;' }}">
                                        <div id="no-image" class="text-muted" style="{{ $product->image_url ? 'display: none;' : '' }}">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <p>Nhập URL để xem trước</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Thông tin bổ sung -->
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title">Thông tin</h6>
                                        <small class="text-muted">
                                            <strong>ID:</strong> {{ $product->product_id }}<br>
                                            <strong>Tạo lúc:</strong> {{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : 'N/A' }}<br>
                                            <strong>Cập nhật:</strong> {{ $product->updated_at ? $product->updated_at->format('d/m/Y H:i') : 'N/A' }}
                                        </small>
                                    </div>
                                </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Chi tiết sản phẩm -->
                            <div class="tab-pane fade" id="details" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="color" class="form-label">Màu sắc</label>
                                            <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                                   id="color" name="color" value="{{ old('color', $product->details->color ?? '') }}" 
                                                   placeholder="VD: Đen, Trắng, Xanh">
                                            @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="storage" class="form-label">Bộ nhớ trong</label>
                                            <input type="text" class="form-control @error('storage') is-invalid @enderror" 
                                                   id="storage" name="storage" value="{{ old('storage', $product->details->storage ?? '') }}" 
                                                   placeholder="VD: 128GB, 256GB, 512GB">
                                            @error('storage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="ram" class="form-label">RAM</label>
                                            <input type="text" class="form-control @error('ram') is-invalid @enderror" 
                                                   id="ram" name="ram" value="{{ old('ram', $product->details->ram ?? '') }}" 
                                                   placeholder="VD: 4GB, 8GB, 12GB">
                                            @error('ram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="screen_size" class="form-label">Kích thước màn hình</label>
                                            <input type="text" class="form-control @error('screen_size') is-invalid @enderror" 
                                                   id="screen_size" name="screen_size" value="{{ old('screen_size', $product->details->screen_size ?? '') }}" 
                                                   placeholder="VD: 6.1 inch, 6.7 inch">
                                            @error('screen_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="chip" class="form-label">Chip xử lý</label>
                                            <input type="text" class="form-control @error('chip') is-invalid @enderror" 
                                                   id="chip" name="chip" value="{{ old('chip', $product->details->chip ?? '') }}" 
                                                   placeholder="VD: A17 Pro, Snapdragon 8 Gen 3">
                                            @error('chip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="battery" class="form-label">Pin</label>
                                            <input type="text" class="form-control @error('battery') is-invalid @enderror" 
                                                   id="battery" name="battery" value="{{ old('battery', $product->details->battery ?? '') }}" 
                                                   placeholder="VD: 4500mAh, 5000mAh">
                                            @error('battery')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="camera_main" class="form-label">Camera chính</label>
                                            <input type="text" class="form-control @error('camera_main') is-invalid @enderror" 
                                                   id="camera_main" name="camera_main" value="{{ old('camera_main', $product->details->camera_main ?? '') }}" 
                                                   placeholder="VD: 48MP, 108MP">
                                            @error('camera_main')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="camera_front" class="form-label">Camera trước</label>
                                            <input type="text" class="form-control @error('camera_front') is-invalid @enderror" 
                                                   id="camera_front" name="camera_front" value="{{ old('camera_front', $product->details->camera_front ?? '') }}" 
                                                   placeholder="VD: 12MP, 32MP">
                                            @error('camera_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="os" class="form-label">Hệ điều hành</label>
                                            <input type="text" class="form-control @error('os') is-invalid @enderror" 
                                                   id="os" name="os" value="{{ old('os', $product->details->os ?? '') }}" 
                                                   placeholder="VD: iOS 17, Android 14">
                                            @error('os')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="special_features" class="form-label">Tính năng đặc biệt</label>
                                            <textarea class="form-control @error('special_features') is-invalid @enderror" 
                                                      id="special_features" name="special_features" rows="3" 
                                                      placeholder="VD: Face ID, Chống nước IP68, Sạc nhanh 67W">{{ old('special_features', $product->details->special_features ?? '') }}</textarea>
                                            @error('special_features')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                        <i class="fas fa-save me-2"></i>Cập nhật sản phẩm
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
    const imageInput = document.getElementById('image');
    const imageUrlInput = document.getElementById('image_url');
    const imagePreview = document.getElementById('image-preview');
    const noImageDiv = document.getElementById('no-image');

    // Preview ảnh từ file upload
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                imagePreview.style.display = 'block';
                noImageDiv.style.display = 'none';
            };
            reader.readAsDataURL(file);
            // Xóa URL khi chọn file
            imageUrlInput.value = '';
        }
    });

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
            // Xóa file khi nhập URL
            imageInput.value = '';
        } else {
            imagePreview.style.display = 'none';
            noImageDiv.innerHTML = `
                <i class="fas fa-image fa-3x mb-2"></i>
                <p>Chọn file hoặc nhập URL để xem trước</p>
            `;
            noImageDiv.style.display = 'block';
        }
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