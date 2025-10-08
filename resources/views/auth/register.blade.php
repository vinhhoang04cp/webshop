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
            
            <!-- Global error display for AJAX -->
            <div id="globalError" class="alert alert-danger" style="display: none;"></div>
            <div id="globalSuccess" class="alert alert-success" style="display: none;"></div>
            
            <div class="form-floating mb-3">
                <input type="text" 
                       class="form-control @error('name') is-invalid @enderror" 
                       id="name" 
                       name="name" 
                       placeholder="Họ và tên"
                       value="{{ old('name') }}"
                       required>
                <label for="name"><i class="fas fa-user me-2"></i>Họ và tên</label>
                <div id="nameError" class="invalid-feedback"></div>
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
                <div class="form-text">Tối thiểu 8 ký tự</div>
                <div id="passwordError" class="invalid-feedback"></div>
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
                <div id="password_confirmationError" class="invalid-feedback"></div>
            </div>

            <div class="form-floating mb-3">
                <input type="tel" 
                       class="form-control @error('phone') is-invalid @enderror" 
                       id="phone" 
                       name="phone" 
                       placeholder="Số điện thoại"
                       value="{{ old('phone') }}">
                <label for="phone"><i class="fas fa-phone me-2"></i>Số điện thoại (tùy chọn)</label>
                <div id="phoneError" class="invalid-feedback"></div>
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
                <div id="addressError" class="invalid-feedback"></div>
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
                <button type="submit" class="btn btn-primary btn-lg" id="registerBtn">
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

@section('scripts')
<script>
(function(){
    const form = document.getElementById('registerForm');
    const btn = document.getElementById('registerBtn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    const fields = ['name', 'email', 'password', 'password_confirmation', 'phone', 'address'];
    
    function clearErrors() {
        // Clear field errors
        fields.forEach(field => {
            document.getElementById(field + 'Error').textContent = '';
            document.getElementById(field).classList.remove('is-invalid');
        });
        
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
            ? '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng ký...'
            : '<i class="fas fa-user-plus me-2"></i>Đăng ký';
    }
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        clearErrors();
        setLoading(true);
        
        const formData = new FormData(form);
        const data = {
            name: formData.get('name'),
            email: formData.get('email'),
            password: formData.get('password'),
            password_confirmation: formData.get('password_confirmation'),
            phone: formData.get('phone'),
            address: formData.get('address')
        };
        
        try {
            const response = await fetch('{{ route("ajax.register") }}', {
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
                // Clear form on success
                form.reset();
                // Redirect after a delay
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 2000);
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
                showGlobalMessage(result.message || 'Đăng ký thất bại', true);
            }
        } catch (error) {
            console.error('Register error:', error);
            showGlobalMessage('Lỗi kết nối. Vui lòng thử lại.', true);
        } finally {
            setLoading(false);
        }
    });
})();
</script>
@endsection

