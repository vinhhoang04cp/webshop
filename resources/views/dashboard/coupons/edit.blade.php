@extends('layouts.app')

@section('title', 'Chỉnh Sửa Coupon')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Chỉnh Sửa Coupon: {{ $coupon->code }}</h1>
                    <a href="{{ route('dashboard.coupons.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
                        <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay Lại
                    </a>
                </div>

    <!-- Alert Messages -->
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Có lỗi xảy ra:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Edit Coupon Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Thông Tin Coupon</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.coupons.update', $coupon->coupon_id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Mã Coupon -->
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Mã Coupon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('code') is-invalid @enderror" 
                               id="code" name="code" value="{{ old('code', $coupon->code) }}" 
                               placeholder="VD: SALE20, NEWUSER, etc." style="text-transform: uppercase;">
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Mã coupon sẽ được chuyển thành chữ hoa tự động</small>
                    </div>

                    <!-- Tên Coupon -->
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Tên Coupon <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $coupon->name) }}" 
                               placeholder="VD: Giảm giá 20%, Khuyến mãi người dùng mới">
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Mô Tả -->
                <div class="mb-3">
                    <label for="description" class="form-label">Mô Tả</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              id="description" name="description" rows="3" 
                              placeholder="Mô tả chi tiết về coupon...">{{ old('description', $coupon->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <!-- Loại Giảm Giá -->
                    <div class="col-md-4 mb-3">
                        <label for="discount_type" class="form-label">Loại Giảm Giá <span class="text-danger">*</span></label>
                        <select class="form-control @error('discount_type') is-invalid @enderror" 
                                id="discount_type" name="discount_type" onchange="toggleDiscountFields()">
                            <option value="">Chọn loại giảm giá</option>
                            <option value="percentage" {{ old('discount_type', $coupon->discount_type) == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                            <option value="fixed" {{ old('discount_type', $coupon->discount_type) == 'fixed' ? 'selected' : '' }}>Số tiền cố định (VND)</option>
                        </select>
                        @error('discount_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Giá Trị Giảm Giá -->
                    <div class="col-md-4 mb-3">
                        <label for="discount_value" class="form-label">Giá Trị Giảm Giá <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                   id="discount_value" name="discount_value" value="{{ old('discount_value', $coupon->discount_value) }}" 
                                   min="0" step="0.01" placeholder="0">
                            <div class="input-group-append">
                                <span class="input-group-text" id="discount-unit">VND</span>
                            </div>
                        </div>
                        @error('discount_value')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Giảm Tối Đa (chỉ hiển thị khi chọn phần trăm) -->
                    <div class="col-md-4 mb-3" id="max_discount_field" style="display: none;">
                        <label for="max_discount_amount" class="form-label">Giảm Tối Đa (VND)</label>
                        <input type="number" class="form-control @error('max_discount_amount') is-invalid @enderror" 
                               id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount', $coupon->max_discount_amount) }}" 
                               min="0" step="1" placeholder="Không giới hạn">
                        @error('max_discount_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Để trống nếu không giới hạn</small>
                    </div>
                </div>

                <div class="row">
                    <!-- Đơn Hàng Tối Thiểu -->
                    <div class="col-md-6 mb-3">
                        <label for="min_order_amount" class="form-label">Giá Trị Đơn Hàng Tối Thiểu (VND) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('min_order_amount') is-invalid @enderror" 
                               id="min_order_amount" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" 
                               min="0" step="1000" placeholder="0">
                        @error('min_order_amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Giới Hạn Sử Dụng -->
                    <div class="col-md-6 mb-3">
                        <label for="usage_limit" class="form-label">Giới Hạn Số Lần Sử Dụng</label>
                        <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" 
                               id="usage_limit" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit) }}" 
                               min="1" placeholder="Không giới hạn">
                        @error('usage_limit')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Để trống nếu không giới hạn. Đã sử dụng: {{ $coupon->used_count }} lần</small>
                    </div>
                </div>

                <div class="row">
                    <!-- Ngày Bắt Đầu -->
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Ngày Bắt Đầu <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('start_date') is-invalid @enderror" 
                               id="start_date" name="start_date" value="{{ old('start_date', $coupon->start_date->format('Y-m-d\TH:i')) }}">
                        @error('start_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ngày Kết Thúc -->
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Ngày Kết Thúc <span class="text-danger">*</span></label>
                        <input type="datetime-local" class="form-control @error('end_date') is-invalid @enderror" 
                               id="end_date" name="end_date" value="{{ old('end_date', $coupon->end_date->format('Y-m-d\TH:i')) }}">
                        @error('end_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Trạng Thái -->
                <div class="mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="is_active" name="is_active" 
                               {{ old('is_active', $coupon->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Coupon đang hoạt động
                        </label>
                    </div>
                </div>

                <!-- Usage Statistics -->
                @if($coupon->used_count > 0)
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-info-circle"></i> Thống Kê Sử Dụng</h6>
                        <p class="mb-0">
                            Coupon này đã được sử dụng <strong>{{ $coupon->used_count }}</strong> lần
                            @if($coupon->usage_limit)
                                trong tổng số <strong>{{ $coupon->usage_limit }}</strong> lần được phép.
                            @else
                                (không giới hạn).
                            @endif
                        </p>
                    </div>
                @endif

                <!-- Buttons -->
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập Nhật Coupon
                        </button>
                        <a href="{{ route('dashboard.coupons.show', $coupon->coupon_id) }}" class="btn btn-info ml-2">
                            <i class="fas fa-eye"></i> Xem Chi Tiết
                        </a>
                        <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize discount fields
        toggleDiscountFields();
    });

    function toggleDiscountFields() {
        const discountType = document.getElementById('discount_type').value;
        const discountUnit = document.getElementById('discount-unit');
        const maxDiscountField = document.getElementById('max_discount_field');
        
        if (discountType === 'percentage') {
            discountUnit.textContent = '%';
            maxDiscountField.style.display = 'block';
        } else if (discountType === 'fixed') {
            discountUnit.textContent = 'VND';
            maxDiscountField.style.display = 'none';
        } else {
            discountUnit.textContent = 'VND';
            maxDiscountField.style.display = 'none';
        }
    }

    // Convert code to uppercase
    document.getElementById('code').addEventListener('input', function() {
        this.value = this.value.toUpperCase();
    });
</script>
            </div>
        </div>
    </div>
</div>
@endsection