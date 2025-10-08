@extends('layouts.app')

@section('title', 'Đăng ký - WebShop Admin')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-user-plus text-primary"></i> WebShop</h1>
            <p>Đăng ký tài khoản quản lý</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" id="registerForm">
            @csrf
            
            <div class="form-floating mb-3">
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       placeholder="Họ và tên"
                       value="{{ old('name') }}"
                       required>
                <label for="name"><i class="fas fa-user me-2"></i>Họ và tên</label>
                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com"
                       value="{{ old('email') }}"
                       required>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       placeholder="Mật khẩu"
                       required>
                <label for="password"><i class="fas fa-lock me-2"></i>Mật khẩu</label>
                <div class="form-text">Tối thiểu 8 ký tự</div>
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <input type="password" 
                       class="form-control" 
                       id="password_confirmation" 
                       name="password_confirmation" 
                       placeholder="Xác nhận mật khẩu"
                       required>
                <label for="password_confirmation"><i class="fas fa-lock me-2"></i>Xác nhận mật khẩu</label>
            </div>

            <div class="form-floating mb-3">
                <input type="tel" 
                       class="form-control @error('phone') is-invalid @enderror" 
                       id="phone" 
                       name="phone" 
                       placeholder="Số điện thoại"
                       value="{{ old('phone') }}">
                <label for="phone"><i class="fas fa-phone me-2"></i>Số điện thoại (tùy chọn)</label>
                @error('phone')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-floating mb-3">
                <textarea class="form-control @error('address') is-invalid @enderror" 
                          id="address" 
                          name="address" 
                          placeholder="Địa chỉ"
                          style="height: 100px">{{ old('address') }}</textarea>
                <label for="address"><i class="fas fa-map-marker-alt me-2"></i>Địa chỉ (tùy chọn)</label>
                @error('address')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Lưu ý:</strong> Tài khoản mới cần được Admin phân quyền trước khi có thể truy cập hệ thống.
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-user-plus me-2"></i>Đăng ký
                </button>
            </div>
        </form>

        <div class="auth-links">
            <p class="mb-0">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập ngay</a></p>
        </div>
    </div>
</div>
@endsection

