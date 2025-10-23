@extends('layouts.app')

@section('title', 'Tạo Coupon Mới')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Tạo Coupon Mới</h1>
                    <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Có lỗi:</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông Tin Coupon</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dashboard.coupons.store') }}" method="POST">
                            @csrf
                            
                            <!-- Mã Coupon -->
                            <div class="form-group">
                                <label for="code">Mã Coupon <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" 
                                       placeholder="VD: SALE20, NEWUSER..." style="text-transform: uppercase;" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Mã sẽ được chuyển thành chữ hoa tự động</small>
                            </div>

                            <div class="row">
                                <!-- Loại Giảm Giá -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_type">Loại Giảm Giá <span class="text-danger">*</span></label>
                                        <select class="form-control @error('discount_type') is-invalid @enderror" 
                                                id="discount_type" name="discount_type" onchange="updateDiscountUnit()" required>
                                            <option value="">-- Chọn --</option>
                                            <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                                            <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Số tiền cố định (VND)</option>
                                        </select>
                                        @error('discount_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Giá Trị -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="discount_value">Giá Trị <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                                   id="discount_value" name="discount_value" value="{{ old('discount_value') }}" 
                                                   min="0" step="0.01" placeholder="0" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text" id="discount-unit">VND</span>
                                            </div>
                                        </div>
                                        @error('discount_value')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Áp Dụng Cho -->
                            <div class="form-group">
                                <label for="product_id">Áp Dụng Cho</label>
                                <select class="form-control @error('product_id') is-invalid @enderror" 
                                        id="product_id" name="product_id">
                                    <option value="">-- Tất Cả Sản Phẩm --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->product_id }}" {{ (old('product_id', $selectedProductId ?? null) == $product->product_id) ? 'selected' : '' }}>
                                            {{ $product->name }} - {{ number_format($product->price, 0, ',', '.') }} VND
                                        </option>
                                    @endforeach
                                </select>
                                @error('product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Để trống nếu muốn áp dụng cho tất cả sản phẩm</small>
                            </div>

                            <div class="row">
                                <!-- Ngày Bắt Đầu -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="start_date">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                                               id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Ngày Kết Thúc -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="end_date">Ngày Kết Thúc <span class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                                               id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Trạng Thái -->
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" 
                                           value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="is_active">
                                        Kích hoạt ngay
                                    </label>
                                </div>
                            </div>

                            <hr>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Tạo Coupon
                                </button>
                                <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set default dates
        const now = new Date();
        const startInput = document.getElementById('start_date');
        if (!startInput.value) {
            startInput.value = now.toISOString().slice(0, 16);
        }
        
        const endDate = new Date(now.getTime() + (30 * 24 * 60 * 60 * 1000));
        const endInput = document.getElementById('end_date');
        if (!endInput.value) {
            endInput.value = endDate.toISOString().slice(0, 16);
        }
        
        updateDiscountUnit();
    });

    function updateDiscountUnit() {
        const type = document.getElementById('discount_type').value;
        const unit = document.getElementById('discount-unit');
        unit.textContent = type === 'percentage' ? '%' : 'VND';
    }

    // KHÔNG dùng JavaScript để uppercase nữa - để server xử lý
    // CSS text-transform: uppercase sẽ hiển thị chữ hoa cho user
</script>
@endsection
