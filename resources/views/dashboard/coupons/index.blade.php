@extends('layouts.app')

@section('title', 'Quản Lý Coupon')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Quản Lý Coupon</h1>
        @if(auth()->user()->hasRole('admin'))
        <a href="{{ route('dashboard.coupons.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Tạo Coupon Mới
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $error }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Search and Filter Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Tìm Kiếm & Lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.coupons.index') }}" class="row">
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Mã coupon hoặc tên...">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                        <option value="upcoming" {{ request('status') == 'upcoming' ? 'selected' : '' }}>Chưa bắt đầu</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
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

    <!-- Coupons List Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh Sách Coupon</h6>
        </div>
        <div class="card-body">
            @if($coupons && $coupons->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã Coupon</th>
                                <th>Tên</th>
                                <th>Loại</th>
                                <th>Giá Trị</th>
                                <th>Đã Sử Dụng</th>
                                <th>Thời Gian</th>
                                <th>Trạng Thái</th>
                                <th>Hành Động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coupons as $coupon)
                            <tr>
                                <td>
                                    <span class="badge badge-primary">{{ $coupon->code }}</span>
                                </td>
                                <td>{{ $coupon->name }}</td>
                                <td>
                                    @if($coupon->discount_type === 'percentage')
                                        <span class="badge badge-info">Phần trăm</span>
                                    @else
                                        <span class="badge badge-success">Số tiền</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $coupon->discount_display }}</strong>
                                    @if($coupon->discount_type === 'percentage' && $coupon->max_discount_amount)
                                        <br><small class="text-muted">Tối đa: {{ number_format($coupon->max_discount_amount, 0, ',', '.') }} VND</small>
                                    @endif
                                </td>
                                <td>
                                    {{ $coupon->used_count }}
                                    @if($coupon->usage_limit)
                                        / {{ $coupon->usage_limit }}
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        <strong>Bắt đầu:</strong> {{ $coupon->start_date->format('d/m/Y') }}<br>
                                        <strong>Kết thúc:</strong> {{ $coupon->end_date->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td>
                                    @php
                                        $status = $coupon->status_display;
                                        $badgeClass = 'secondary';
                                        
                                        switch($status) {
                                            case 'Đang hoạt động':
                                                $badgeClass = 'success';
                                                break;
                                            case 'Không hoạt động':
                                                $badgeClass = 'secondary';
                                                break;
                                            case 'Đã hết hạn':
                                                $badgeClass = 'danger';
                                                break;
                                            case 'Chưa bắt đầu':
                                                $badgeClass = 'warning';
                                                break;
                                            case 'Hết lượt sử dụng':
                                                $badgeClass = 'dark';
                                                break;
                                        }
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ $status }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('dashboard.coupons.show', $coupon->coupon_id) }}" 
                                           class="btn btn-info btn-sm" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if(auth()->user()->hasRole('admin'))
                                            <a href="{{ route('dashboard.coupons.edit', $coupon->coupon_id) }}" 
                                               class="btn btn-warning btn-sm" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            <form action="{{ route('dashboard.coupons.toggle-status', $coupon->coupon_id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        class="btn btn-{{ $coupon->is_active ? 'secondary' : 'success' }} btn-sm" 
                                                        title="{{ $coupon->is_active ? 'Vô hiệu hóa' : 'Kích hoạt' }}"
                                                        onclick="return confirm('Bạn có chắc chắn?')">
                                                    <i class="fas fa-{{ $coupon->is_active ? 'pause' : 'play' }}"></i>
                                                </button>
                                            </form>
                                            
                                            @if($coupon->used_count == 0)
                                                <form action="{{ route('dashboard.coupons.destroy', $coupon->coupon_id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" 
                                                            title="Xóa"
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa coupon này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-danger btn-sm" disabled title="Không thể xóa coupon đã được sử dụng">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($pagination && $pagination->hasPages())
                    <div class="d-flex justify-content-center">
                        {{ $pagination->links() }}
                    </div>
                @endif

            @else
                <!-- Empty State -->
                <div class="text-center py-4">
                    <i class="fas fa-ticket-alt fa-3x text-gray-300 mb-3"></i>
                    <h4 class="text-gray-500">Chưa có coupon nào</h4>
                    <p class="text-gray-400 mb-4">Tạo coupon đầu tiên để bắt đầu chương trình khuyến mãi</p>
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('dashboard.coupons.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tạo Coupon Mới
                        </a>
                    @endif
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
@endsection