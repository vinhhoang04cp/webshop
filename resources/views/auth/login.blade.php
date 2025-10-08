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

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            
            <!-- Global error display for AJAX -->
            <div id="globalError" class="alert alert-danger" style="display: none;"></div>
            <div id="globalSuccess" class="alert alert-success" style="display: none;"></div>
            
            <div class="form-floating mb-3">
                <input type="email" 
                       class="form-control @error('email') is-invalid @enderror" 
                       id="email" 
                       name="email" 
                       placeholder="name@example.com"
                       value="{{ old('email') }}"
                       required>
                <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                <div id="emailError" class="invalid-feedback"></div>
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
                <div id="passwordError" class="invalid-feedback"></div>
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
                <button type="submit" class="btn btn-primary btn-lg" id="loginBtn">
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
(function(){
    const form = document.getElementById('loginForm');
    const btn = document.getElementById('loginBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    function clearErrors() {
        // Clear field errors
        document.getElementById('emailError').textContent = '';
        document.getElementById('passwordError').textContent = '';
        document.getElementById('email').classList.remove('is-invalid');
        document.getElementById('password').classList.remove('is-invalid');
        
        // Clear global messages
        document.getElementById('globalError').style.display = 'none';
        document.getElementById('globalSuccess').style.display = 'none';
    }
    
    function showFieldError(field, message) {
        const input = document.getElementById(field);
        const errorDiv = document.getElementById(field + 'Error');
        input.classList.add('is-invalid');
        errorDiv.textContent = message;
    }
    
    function showGlobalMessage(message, isError = false) {
        const div = document.getElementById(isError ? 'globalError' : 'globalSuccess');
        div.textContent = message;
        div.style.display = 'block';
    }
    
    function setLoading(loading) {
        btn.disabled = loading;
        btn.innerHTML = loading 
            ? '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng nhập...'
            : '<i class="fas fa-sign-in-alt me-2"></i>Đăng nhập';
    }
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);
        
        const formData = new FormData(form);
        const data = {
            email: formData.get('email'),
            password: formData.get('password')
        };
        
        try {
            const response = await fetch('{{ route("ajax.login") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (response.ok && result.status) {
                showGlobalMessage(result.message);
                // Redirect on success
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
            } else {
                // Handle validation errors
                if (result.errors) {
                    Object.keys(result.errors).forEach(field => {
                        if (result.errors[field].length > 0) {
                            showFieldError(field, result.errors[field][0]);
                        }
                    });
                }
                
                // Show global error message
                showGlobalMessage(result.message || 'Đăng nhập thất bại', true);
            }
        } catch (error) {
            console.error('Login error:', error);
            showGlobalMessage('Lỗi kết nối. Vui lòng thử lại.', true);
        } finally {
            setLoading(false);
        }
    });
})();
</script>
@endsection
