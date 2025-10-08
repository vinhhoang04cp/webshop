@extends('layouts.app')

@section('title', 'Đăng nhập - WebShop Admin')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <h1><i class="fas fa-shield-alt text-primary"></i> WebShop</h1>
            <p>Đăng nhập hệ thống quản lý</p>
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

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
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
                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="remember" name="remember">
                <label class="form-check-label" for="remember">
                    Ghi nhớ đăng nhập
                </label>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                </button>
            </div>
        </form>

        <div class="auth-links">
            <p class="mb-0">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a></p>
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                Chỉ Admin và Manager được phép truy cập
            </small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Form validation và UX improvements
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        form.addEventListener('submit', function() {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...';
        });

        // Reset button nếu có lỗi
        @if ($errors->any())
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        @endif
    });
</script>
@endsection