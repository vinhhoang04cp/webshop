@extends('layouts.app')

@section('title', 'Chi Tiết Coupon')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Chi Tiết Coupon</h1>
                    <div>
                        @if(auth()->user()->hasRole('admin'))
                            <a href="{{ route('dashboard.coupons.edit', $coupon->coupon_id) }}" class="btn btn-sm btn-warning shadow-sm">
                                <i class="fas fa-edit"></i> Chỉnh Sửa
                            </a>
                        @endif
                        <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-sm btn-secondary shadow-sm">
                            <i class="fas fa-arrow-left"></i> Quay Lại
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Thông Tin Coupon</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="200">Mã Coupon</th>
                                        <td>
                                            <span class="badge badge-primary badge-lg" style="font-size: 1.1em;">
                                                {{ $coupon->code }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Loại Giảm Giá</th>
                                        <td>
                                            @if($coupon->discount_type === 'percentage')
                                                <span class="badge badge-info">Phần trăm</span>
                                            @else
                                                <span class="badge badge-success">Số tiền cố định</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Giá Trị Giảm</th>
                                        <td>
                                            <strong class="text-success" style="font-size: 1.2em;">
                                                {{ $coupon->discount_display }}
                                            </strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Áp Dụng Cho</th>
                                        <td>
                                            @if($coupon->product_id)
                                                <a href="{{ route('dashboard.products.show', $coupon->product_id) }}">
                                                    <i class="fas fa-box"></i> {{ $coupon->product->name }}
                                                </a>
                                            @else
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-globe"></i> Tất cả sản phẩm
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Thời Gian</th>
                                        <td>
                                            <div><i class="fas fa-calendar-alt text-primary"></i> <strong>Bắt đầu:</strong> {{ $coupon->start_date->format('d/m/Y H:i') }}</div>
                                            <div><i class="fas fa-calendar-times text-danger"></i> <strong>Kết thúc:</strong> {{ $coupon->end_date->format('d/m/Y H:i') }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Trạng Thái</th>
                                        <td>
                                            @php
                                                $status = $coupon->status_display;
                                                $badgeClass = match($status) {
                                                    'Đang hoạt động' => 'success',
                                                    'Không hoạt động' => 'secondary',
                                                    'Đã hết hạn' => 'danger',
                                                    'Chưa bắt đầu' => 'warning',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}" style="font-size: 1em;">
                                                {{ $status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Ngày Tạo</th>
                                        <td>{{ $coupon->created_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cập Nhật Cuối</th>
                                        <td>{{ $coupon->updated_at->format('d/m/Y H:i:s') }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Quick Actions -->
                        @if(auth()->user()->hasRole('admin'))
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Hành Động</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('dashboard.coupons.edit', $coupon->coupon_id) }}" class="btn btn-warning btn-block mb-2">
                                        <i class="fas fa-edit"></i> Chỉnh Sửa
                                    </a>
                                    
                                    <form action="{{ route('dashboard.coupons.toggle-status', $coupon->coupon_id) }}" method="POST" class="mb-2">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-{{ $coupon->is_active ? 'secondary' : 'success' }} btn-block">
                                            <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }}"></i>
                                            {{ $coupon->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('dashboard.coupons.destroy', $coupon->coupon_id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-block" 
                                                onclick="return confirm('Bạn có chắc muốn xóa coupon này?')">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Info Card -->
                        <div class="card shadow mb-4 border-left-primary">
                            <div class="card-body">
                                <div class="text-center">
                                    <i class="fas fa-info-circle fa-3x text-primary mb-3"></i>
                                    <h6 class="font-weight-bold">Thông Tin</h6>
                                    <p class="text-muted small">
                                        Coupon này 
                                        @if($coupon->product_id)
                                            chỉ áp dụng cho sản phẩm <strong>{{ $coupon->product->name }}</strong>
                                        @else
                                            áp dụng cho <strong>tất cả sản phẩm</strong> trong cửa hàng
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
