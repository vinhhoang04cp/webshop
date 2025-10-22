@extends('layouts.app')

@section('title', 'Quản Lý Coupon')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="container-fluid">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Quản Lý Coupon</h1>
                    @if(auth()->user()->hasRole('admin'))
                    <a href="{{ route('dashboard.coupons.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-plus fa-sm text-white-50"></i> Tạo Coupon
                    </a>
                    @endif
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

                @if(session('error') || isset($error))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') ?? $error }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Search Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Tìm Kiếm</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard.coupons.index') }}" class="row">
                            <div class="col-md-8 mb-3">
                                <input type="text" class="form-control" name="search" 
                                       value="{{ request('search') }}" placeholder="Tìm theo mã coupon...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                                <a href="{{ route('dashboard.coupons.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Đặt lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Coupons List -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Danh Sách Coupon</h6>
                    </div>
                    <div class="card-body">
                        @if($coupons && $coupons->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã Coupon</th>
                                            <th>Loại</th>
                                            <th>Giá Trị</th>
                                            <th>Áp Dụng</th>
                                            <th>Thời Gian</th>
                                            <th>Trạng Thái</th>
                                            <th>Hành Động</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($coupons as $coupon)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">{{ $coupon->code }}</strong>
                                            </td>
                                            <td>
                                                @if($coupon->discount_type === 'percentage')
                                                    <span class="badge badge-info">Phần trăm</span>
                                                @else
                                                    <span class="badge badge-success">Số tiền</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $coupon->discount_display }}</strong>
                                            </td>
                                            <td>
                                                <small>{{ $coupon->scope_display }}</small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $coupon->start_date->format('d/m/Y') }}<br>
                                                    → {{ $coupon->end_date->format('d/m/Y') }}
                                                </small>
                                            </td>
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
                                                <span class="badge badge-{{ $badgeClass }}">{{ $status }}</span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('dashboard.coupons.show', $coupon->coupon_id) }}" 
                                                       class="btn btn-info" title="Xem">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    @if(auth()->user()->hasRole('admin'))
                                                        <a href="{{ route('dashboard.coupons.edit', $coupon->coupon_id) }}" 
                                                           class="btn btn-warning" title="Sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <form action="{{ route('dashboard.coupons.toggle-status', $coupon->coupon_id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="btn btn-{{ $coupon->is_active ? 'secondary' : 'success' }}" 
                                                                    title="{{ $coupon->is_active ? 'Tắt' : 'Bật' }}">
                                                                <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }}"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <form action="{{ route('dashboard.coupons.destroy', $coupon->coupon_id) }}" 
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger" 
                                                                    title="Xóa"
                                                                    onclick="return confirm('Bạn có chắc muốn xóa?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if($pagination && $pagination->hasPages())
                                <div class="d-flex justify-content-center">
                                    {{ $pagination->links() }}
                                </div>
                            @endif

                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-ticket-alt fa-3x text-gray-300 mb-3"></i>
                                <h4 class="text-gray-500">Chưa có coupon</h4>
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('dashboard.coupons.create') }}" class="btn btn-primary mt-3">
                                        <i class="fas fa-plus"></i> Tạo Coupon Đầu Tiên
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
    });
</script>
@endsection
