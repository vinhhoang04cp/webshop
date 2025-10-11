@extends('layouts.app')

@section('title', 'Chỉnh sửa quyền người dùng - WebShop Admin')

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
                <a class="nav-link" href="{{ route('dashboard.products.index') }}">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="{{ route('dashboard.orders.index') }}">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
                @if(Auth::user()->isAdmin())
                <a class="nav-link active" href="{{ route('dashboard.users.index') }}">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                @endif
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
                    <h2>Chỉnh sửa quyền người dùng</h2>
                    <p class="text-muted mb-0">Cập nhật roles cho "{{ $user->name }}"</p>
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

            <div class="row">
                <!-- Form chỉnh sửa quyền -->
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user-shield me-2"></i>Cập nhật Roles</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('dashboard.users.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-4">
                                    <label class="form-label"><strong>Chọn Roles cho người dùng:</strong></label>
                                    <div class="row">
                                        @foreach($roles as $role)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check role-checkbox">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       id="role_{{ $role->role_id }}" 
                                                       name="roles[]" 
                                                       value="{{ $role->role_id }}"
                                                       {{ $user->roles->contains('role_id', $role->role_id) ? 'checked' : '' }}>
                                                <label class="form-check-label w-100" for="role_{{ $role->role_id }}">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-shield-alt text-primary me-2"></i>
                                                        <div>
                                                            <strong>{{ $role->role_display_name }}</strong>
                                                            <br>
                                                            <small class="text-muted">{{ $role->role_name }}</small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Cập nhật quyền
                                    </button>
                                    <a href="{{ route('dashboard.users.show', $user->id) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Hủy bỏ
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar thông tin -->
                <div class="col-lg-4">
                    <!-- Thông tin User -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Thông tin người dùng</h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <div class="avatar-circle-large mx-auto">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            </div>
                            
                            <div class="info-item-simple mb-2">
                                <i class="fas fa-user text-primary"></i>
                                <div>
                                    <small class="text-muted">Tên</small>
                                    <div><strong>{{ $user->name }}</strong></div>
                                </div>
                            </div>
                            
                            <div class="info-item-simple mb-2">
                                <i class="fas fa-envelope text-success"></i>
                                <div>
                                    <small class="text-muted">Email</small>
                                    <div>{{ $user->email }}</div>
                                </div>
                            </div>
                            
                            <div class="info-item-simple">
                                <i class="fas fa-calendar text-info"></i>
                                <div>
                                    <small class="text-muted">Ngày tạo</small>
                                    <div>{{ $user->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Roles hiện tại và quyền -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Roles hiện tại</h5>
                        </div>
                        <div class="card-body">
                            @if($user->roles->count() > 0)
                                <div class="mb-3">
                                    @foreach($user->roles as $role)
                                        <span class="badge bg-primary me-1 mb-1">
                                            {{ $role->role_display_name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted fst-italic">Chưa có role nào</p>
                            @endif

                            <hr>

                            <h6 class="text-muted mb-2">Quyền hiện tại:</h6>
                            @php
                                $permissions = $user->getAllPermissions();
                            @endphp
                            
                            @if(in_array('*', $permissions))
                                <span class="badge bg-danger">Tất cả quyền (Admin)</span>
                            @elseif(count($permissions) > 0)
                                <div>
                                    @foreach($permissions as $permission)
                                        <span class="badge bg-success me-1 mb-1">{{ $permission }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted fst-italic">Chưa có quyền nào</p>
                            @endif
                        </div>
                    </div>

                    <!-- Gán role nhanh -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Thao tác nhanh</h5>
                        </div>
                        <div class="card-body">
                            @if($user->roles->count() > 0)
                                <h6 class="text-muted mb-2">Gỡ Role:</h6>
                                @foreach($user->roles as $role)
                                    <form action="{{ route('dashboard.users.remove-role', [$user->id, $role->role_id]) }}" 
                                          method="POST" 
                                          class="d-inline-block mb-1"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn gỡ role {{ $role->role_display_name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times me-1"></i>{{ $role->role_display_name }}
                                        </button>
                                    </form>
                                @endforeach
                            @else
                                <p class="text-muted fst-italic">Chưa có role để gỡ</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.role-checkbox {
    padding: 1rem;
    border: 2px solid #e9ecef;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.role-checkbox:hover {
    border-color: #0d6efd;
    background-color: #f8f9fa;
}

.form-check-input:checked ~ .form-check-label .role-checkbox {
    border-color: #0d6efd;
    background-color: #e7f1ff;
}

.avatar-circle-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.info-item-simple {
    display: flex;
    align-items: start;
    gap: 0.75rem;
}

.info-item-simple i {
    font-size: 1.25rem;
    margin-top: 0.25rem;
}
</style>
@endsection