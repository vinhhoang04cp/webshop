@extends('layouts.app')
@section('title', 'Thêm sản phẩm - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Thêm sản phẩm mới</h2>
                    <p class="text-muted mb-0">Tạo sản phẩm mới cho cửa hàng</p>
                </div>
            </div>
            @include('components.alerts')

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Thông tin sản phẩm</h5>
                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
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

                    <form method="POST" action="{{ route('dashboard.products.store') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Tab content -->
                        <div class="tab-content" id="productTabContent">
                            <!-- Tab 1: Thông tin cơ bản -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả sản phẩm</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="image" class="form-label">Tải lên ảnh sản phẩm</label>
                                            <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                            <small class="text-muted">Chấp nhận: JPG, JPEG, PNG, GIF. Tối đa: 2MB</small>
                                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="image_url" class="form-label">Hoặc URL hình ảnh</label>
                                            <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" value="{{ old('image_url') }}">
                                            <small class="text-muted">Nếu không tải file, có thể dùng URL</small>
                                            @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required min="0" step="1000">
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}" {{ old('category_id') == $category->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="stock_quantity" class="form-label">Số lượng tồn kho <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" required min="0" step="1">
                                    @error('stock_quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <small class="text-muted">Nhập số lượng sản phẩm có sẵn trong kho</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Xem trước</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        <img id="preview" src="" alt="Preview" class="img-fluid rounded" style="max-height:150px;display:none">
                                        <div id="no-image" class="text-muted"><i class="fas fa-image fa-2x mb-2"></i><p>Chọn file hoặc nhập URL để xem</p></div>
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
                                                   id="color" name="color" value="{{ old('color') }}" 
                                                   placeholder="VD: Đen, Trắng, Xanh">
                                            @error('color')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="storage" class="form-label">Bộ nhớ trong</label>
                                            <input type="text" class="form-control @error('storage') is-invalid @enderror" 
                                                   id="storage" name="storage" value="{{ old('storage') }}" 
                                                   placeholder="VD: 128GB, 256GB, 512GB">
                                            @error('storage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="ram" class="form-label">RAM</label>
                                            <input type="text" class="form-control @error('ram') is-invalid @enderror" 
                                                   id="ram" name="ram" value="{{ old('ram') }}" 
                                                   placeholder="VD: 4GB, 8GB, 12GB">
                                            @error('ram')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="screen_size" class="form-label">Kích thước màn hình</label>
                                            <input type="text" class="form-control @error('screen_size') is-invalid @enderror" 
                                                   id="screen_size" name="screen_size" value="{{ old('screen_size') }}" 
                                                   placeholder="VD: 6.1 inch, 6.7 inch">
                                            @error('screen_size')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="chip" class="form-label">Chip xử lý</label>
                                            <input type="text" class="form-control @error('chip') is-invalid @enderror" 
                                                   id="chip" name="chip" value="{{ old('chip') }}" 
                                                   placeholder="VD: A17 Pro, Snapdragon 8 Gen 3">
                                            @error('chip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="battery" class="form-label">Pin</label>
                                            <input type="text" class="form-control @error('battery') is-invalid @enderror" 
                                                   id="battery" name="battery" value="{{ old('battery') }}" 
                                                   placeholder="VD: 4500mAh, 5000mAh">
                                            @error('battery')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="camera_main" class="form-label">Camera chính</label>
                                            <input type="text" class="form-control @error('camera_main') is-invalid @enderror" 
                                                   id="camera_main" name="camera_main" value="{{ old('camera_main') }}" 
                                                   placeholder="VD: 48MP, 108MP">
                                            @error('camera_main')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="camera_front" class="form-label">Camera trước</label>
                                            <input type="text" class="form-control @error('camera_front') is-invalid @enderror" 
                                                   id="camera_front" name="camera_front" value="{{ old('camera_front') }}" 
                                                   placeholder="VD: 12MP, 32MP">
                                            @error('camera_front')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="os" class="form-label">Hệ điều hành</label>
                                            <input type="text" class="form-control @error('os') is-invalid @enderror" 
                                                   id="os" name="os" value="{{ old('os') }}" 
                                                   placeholder="VD: iOS 17, Android 14">
                                            @error('os')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="special_features" class="form-label">Tính năng đặc biệt</label>
                                            <textarea class="form-control @error('special_features') is-invalid @enderror" 
                                                      id="special_features" name="special_features" rows="3" 
                                                      placeholder="VD: Face ID, Chống nước IP68, Sạc nhanh 67W">{{ old('special_features') }}</textarea>
                                            @error('special_features')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('dashboard.products.index') }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Hủy</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
const imageInput = document.getElementById('image');
const imageUrlInput = document.getElementById('image_url');
const preview = document.getElementById('preview');
const noImage = document.getElementById('no-image');

// Preview ảnh từ file upload
imageInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            noImage.style.display = 'none';
        };
        reader.readAsDataURL(file);
        // Xóa URL khi chọn file
        imageUrlInput.value = '';
    }
});

// Preview ảnh từ URL
imageUrlInput.addEventListener('input', function() {
    const url = this.value.trim();
    if (url) {
        preview.src = url;
        preview.style.display = 'block';
        noImage.style.display = 'none';
        preview.onerror = function() {
            preview.style.display = 'none';
            noImage.innerHTML = '<i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i><p>Không thể tải ảnh</p>';
            noImage.style.display = 'block';
        };
        // Xóa file khi nhập URL
        imageInput.value = '';
    } else {
        preview.style.display = 'none';
        noImage.innerHTML = '<i class="fas fa-image fa-2x mb-2"></i><p>Chọn file hoặc nhập URL để xem</p>';
        noImage.style.display = 'block';
    }
});
</script>
@endsection