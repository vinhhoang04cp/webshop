@extends(Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager') ? 'layouts.app' : 'layouts.customer')

@section('title', 'Quản lý tài khoản')

@section('content')
<!-- Breadcrumb (only for customer layout) -->
@if(!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('manager'))
<div class="container mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
            <li class="breadcrumb-item active" aria-current="page">Quản lý tài khoản</li>
        </ol>
    </nav>
</div>
@endif

<div class="container mt-4 mb-5">
    <h2 class="mb-4">
        <i class="fas fa-user-circle text-primary"></i> Quản lý tài khoản
    </h2>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" 
                             alt="Avatar" 
                             class="rounded-circle mb-3" 
                             style="width: 150px; height: 150px; object-fit: cover; border: 4px solid #00d4aa;">
                    @else
                        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 150px; height: 150px; font-size: 48px; background: linear-gradient(135deg, #00d4aa, #26d0ce); color: white; border: 4px solid #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                    @endif
                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small mb-2">{{ Auth::user()->email }}</p>
                    @if(Auth::user()->hasRole('admin'))
                        <span class="badge bg-danger">Admin</span>
                    @elseif(Auth::user()->hasRole('manager'))
                        <span class="badge bg-warning">Manager</span>
                    @else
                        <span class="badge" style="background: linear-gradient(135deg, #00d4aa, #26d0ce);">Khách hàng</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- Tabs -->
            <ul class="nav nav-tabs mb-3" id="profileTabs" role="tablist" style="border-bottom: 2px solid #e5e7eb;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" style="border: none; color: #6b7280; font-weight: 600;">
                        <i class="fas fa-user me-2"></i>Thông tin cá nhân
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" style="border: none; color: #6b7280; font-weight: 600;">
                        <i class="fas fa-key me-2"></i>Đổi mật khẩu
                    </button>
                </li>
            </ul>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Tab Content -->
            <div class="tab-content" id="profileTabsContent">
                <!-- Thông tin cá nhân -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Cập nhật thông tin cá nhân</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label">Họ tên <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $user->name) }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" 
                                           class="form-control" 
                                           id="email" 
                                           value="{{ $user->email }}" 
                                           readonly>
                                    <small class="text-muted">Email không thể thay đổi</small>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="address" class="form-label">Địa chỉ</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Ảnh đại diện</label>
                                    <input type="file" 
                                           class="form-control @error('avatar') is-invalid @enderror" 
                                           id="avatar" 
                                           name="avatar" 
                                           accept="image/*">
                                    @error('avatar')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Định dạng: jpeg, png, jpg, gif. Kích thước tối đa: 2MB</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Cập nhật thông tin
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Đổi mật khẩu -->
                <div class="tab-pane fade" id="password" role="tabpanel">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Đổi mật khẩu</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password" 
                                           required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('new_password') is-invalid @enderror" 
                                           id="new_password" 
                                           name="new_password" 
                                           required>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">
                                        Tối thiểu 12 ký tự, bao gồm: chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&#^())
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password_confirmation" class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control" 
                                           id="new_password_confirmation" 
                                           name="new_password_confirmation" 
                                           required>
                                </div>

                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Lưu ý:</strong> Sau khi đổi mật khẩu, bạn sẽ cần đăng nhập lại.
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('styles')
<style>
    .nav-tabs .nav-link.active {
        color: #00d4aa !important;
        border-bottom: 3px solid #00d4aa !important;
        background: transparent;
    }
    
    .nav-tabs .nav-link:hover {
        color: #00d4aa !important;
        border-color: transparent;
    }
    
    .card {
        border-radius: 12px;
        border: none;
    }
    
    .card-header {
        background: linear-gradient(135deg, #00d4aa, #26d0ce);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        border: none;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #00d4aa, #26d0ce);
        border: none;
        padding: 12px 24px;
        font-weight: 600;
        border-radius: 8px;
        transition: transform 0.3s;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 212, 170, 0.4);
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #00d4aa;
        box-shadow: 0 0 0 0.2rem rgba(0, 212, 170, 0.25);
    }
    
    .breadcrumb {
        background: white;
        padding: 12px 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .breadcrumb-item a {
        color: #00d4aa;
        text-decoration: none;
    }
    
    .breadcrumb-item a:hover {
        color: #008c73;
    }
</style>
@endsection

@endsection
