@extends('layouts.app')

@section('title', 'Đăng nhập - WebShop Admin')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        {{-- Header --}}
        <div class="auth-header">
            <h1><i class="fas fa-shield-alt text-primary"></i> WebShop</h1>
            <p>Đăng nhập hệ thống quản lý</p>
        </div>

        {{-- Alerts --}}
        @include('components.alerts')

        {{-- Form đăng nhập --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope me-2"></i>Email
                </label>
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       value="{{ old('email') }}" 
                       required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Mật khẩu --}}
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>Mật khẩu
                </label>
                <input type="password" 
                       class="form-control @error('password') is-invalid @enderror" 
                       id="password" 
                       name="password" 
                       required>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember & Forgot Password --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="remember" 
                           name="remember">
                    <label class="form-check-label" for="remember">
                        Ghi nhớ đăng nhập
                    </label>
                </div>
                <a href="{{ route('password.request') }}" class="text-decoration-none small">
                    Quên mật khẩu?
                </a>
            </div>

            {{-- Submit Button --}}
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                </button>
            </div>
        </form>
        
        {{-- Divider --}}
        <div class="social-divider mt-4 mb-3">
            <span>HOẶC</span>
        </div>
        
        {{-- Social Login --}}
        <div class="social-login-buttons">
            <a href="{{ route('social.redirect', 'google') }}" class="btn btn-outline-danger btn-lg w-100 mb-2">
                <i class="fab fa-google me-2"></i>Đăng nhập với Google
            </a>
            <a href="{{ route('social.redirect', 'facebook') }}" class="btn btn-outline-primary btn-lg w-100 mb-2">
                <i class="fab fa-facebook me-2"></i>Đăng nhập với Facebook
            </a>
        </div>
        
        {{-- Register Link --}}
        <div class="text-center mt-3">
            <p class="mb-0">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>Chỉ Admin và Manager được phép truy cập
            </small>
        </div>
    </div>
</div>
@endsection
