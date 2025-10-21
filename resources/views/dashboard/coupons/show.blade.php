@extends('layouts.app')

@section('title', 'Chi Tiết Coupon')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Chi Tiết Coupon: {{ $coupon->code }}</h1>
                    <div>
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('dashboard.coupons.edit', $coupon->coupon_id) }}" class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm">
                                <i class="fas fa-edit fa-sm text-white-50"></i> Chỉnh Sửa
                            </a>
                        @endif
                        <a href="{{ route('dashboard.coupons.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm ml-2">
                            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Quay Lại
                        </a>
                    </div>
                </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Coupon Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông Tin Coupon</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Mã Coupon:</label>
                            <p class="text-dark">
                                <span class="badge badge-primary badge-lg">{{ $coupon->code }}</span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Tên Coupon:</label>
                            <p class="text-dark">{{ $coupon->name }}</p>
                        </div>
                    </div>

                    @if($coupon->description)
                        <div class="mb-3">
                            <label class="font-weight-bold text-gray-700">Mô Tả:</label>
                            <p class="text-dark">{{ $coupon->description }}</p>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-gray-700">Loại Giảm Giá:</label>
                            <p class="text-dark">
                                @if($coupon->discount_type === 'percentage')
                                    <span class="badge badge-info">Phần trăm</span>
                                @else
                                    <span class="badge badge-success">Số tiền cố định</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold text-gray-700">Giá Trị Giảm:</label>
                            <p class="text-dark">
                                <span class="h5 text-primary">{{ $coupon->discount_display }}</span>
                            </p>
                        </div>
                        @if($coupon->discount_type === 'percentage' && $coupon->max_discount_amount)
                            <div class="col-md-4 mb-3">
                                <label class="font-weight-bold text-gray-700">Giảm Tối Đa:</label>
                                <p class="text-dark">{{ number_format($coupon->max_discount_amount, 0, ',', '.') }} VND</p>
                            </div>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Đơn Hàng Tối Thiểu:</label>
                            <p class="text-dark">{{ number_format($coupon->min_order_amount, 0, ',', '.') }} VND</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Giới Hạn Sử Dụng:</label>
                            <p class="text-dark">
                                @if($coupon->usage_limit)
                                    {{ number_format($coupon->usage_limit) }} lần
                                @else
                                    Không giới hạn
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Ngày Bắt Đầu:</label>
                            <p class="text-dark">{{ $coupon->start_date->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Ngày Kết Thúc:</label>
                            <p class="text-dark">{{ $coupon->end_date->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Ngày Tạo:</label>
                            <p class="text-dark">{{ $coupon->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold text-gray-700">Cập Nhật Cuối:</label>
                            <p class="text-dark">{{ $coupon->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status & Statistics -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng Thái</h6>
                </div>
                <div class="card-body text-center">
                    @php
                        $status = $coupon->status_display;
                        $badgeClass = 'secondary';
                        $iconClass = 'fas fa-question-circle';
                        
                        switch($status) {
                            case 'Đang hoạt động':
                                $badgeClass = 'success';
                                $iconClass = 'fas fa-check-circle';
                                break;
                            case 'Không hoạt động':
                                $badgeClass = 'secondary';
                                $iconClass = 'fas fa-pause-circle';
                                break;
                            case 'Đã hết hạn':
                                $badgeClass = 'danger';
                                $iconClass = 'fas fa-times-circle';
                                break;
                            case 'Chưa bắt đầu':
                                $badgeClass = 'warning';
                                $iconClass = 'fas fa-clock';
                                break;
                            case 'Hết lượt sử dụng':
                                $badgeClass = 'dark';
                                $iconClass = 'fas fa-ban';
                                break;
                        }
                    @endphp
                    
                    <div class="mb-3">
                        <i class="{{ $iconClass }} fa-3x text-{{ $badgeClass }}"></i>
                    </div>
                    <h5>
                        <span class="badge badge-{{ $badgeClass }} badge-lg">{{ $status }}</span>
                    </h5>
                    
                    @if(auth()->user()->hasRole('admin'))
                        <div class="mt-3">
                            <form action="{{ route('dashboard.coupons.toggle-status', $coupon->coupon_id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="btn btn-{{ $coupon->is_active ? 'secondary' : 'success' }} btn-sm" 
                                        onclick="return confirm('Bạn có chắc chắn muốn {{ $coupon->is_active ? 'vô hiệu hóa' : 'kích hoạt' }} coupon này?')">
                                    <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }}"></i> 
                                    {{ $coupon->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Usage Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống Kê Sử Dụng</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="h2 font-weight-bold text-primary">{{ number_format($coupon->used_count) }}</div>
                        <div class="text-gray-600">Lần đã sử dụng</div>
                    </div>
                    
                    @if($coupon->usage_limit)
                        <div class="progress mb-3">
                            @php
                                $percentage = min(($coupon->used_count / $coupon->usage_limit) * 100, 100);
                                $progressClass = $percentage >= 90 ? 'bg-danger' : ($percentage >= 70 ? 'bg-warning' : 'bg-success');
                            @endphp
                            <div class="progress-bar {{ $progressClass }}" role="progressbar" 
                                 style="width: {{ $percentage }}%" 
                                 aria-valuenow="{{ $coupon->used_count }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="{{ $coupon->usage_limit }}">
                                {{ round($percentage, 1) }}%
                            </div>
                        </div>
                        <div class="text-center text-gray-600">
                            {{ number_format($coupon->usage_limit - $coupon->used_count) }} lần còn lại
                        </div>
                    @else
                        <div class="text-center text-gray-600">
                            Không giới hạn số lần sử dụng
                        </div>
                    @endif
                </div>
            </div>

            <!-- Validity Check -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Kiểm Tra Tính Hợp Lệ</h6>
                </div>
                <div class="card-body">
                    @php
                        $validationResult = $coupon->isValid(0);
                    @endphp
                    
                    <div class="text-center">
                        @if($validationResult['valid'])
                            <div class="text-success mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <div class="text-success font-weight-bold">Hợp lệ</div>
                        @else
                            <div class="text-danger mb-2">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <div class="text-danger font-weight-bold">Không hợp lệ</div>
                        @endif
                        <p class="text-sm text-gray-600 mt-2">{{ $validationResult['message'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if(auth()->user()->hasRole('admin'))
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Hành Động</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('dashboard.coupons.edit', $coupon->coupon_id) }}" 
                               class="btn btn-warning btn-block">
                                <i class="fas fa-edit"></i> Chỉnh Sửa
                            </a>
                            
                            @if($coupon->used_count == 0)
                                <form action="{{ route('dashboard.coupons.destroy', $coupon->coupon_id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block"
                                            onclick="return confirm('Bạn có chắc chắn muốn xóa coupon này? Hành động này không thể hoàn tác!')">
                                        <i class="fas fa-trash"></i> Xóa Coupon
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-danger btn-block" disabled title="Không thể xóa coupon đã được sử dụng">
                                    <i class="fas fa-trash"></i> Không thể xóa
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
            </div>
        </div>
    </div>
</div>
@endsection