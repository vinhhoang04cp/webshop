@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                {{-- Header --}}
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Đặt lại mật khẩu</h4>
                </div>

                <div class="card-body">
                    {{-- Errors --}}
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf

                        {{-- Hidden Fields --}}
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        {{-- Email (Read-only) --}}
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   value="{{ $email }}" 
                                   readonly>
                        </div>

                        {{-- Mật khẩu mới --}}
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu mới</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Tối thiểu 12 ký tự, bao gồm: chữ hoa, chữ thường, số và ký tự đặc biệt (@$!%*?&#^())
                            </small>
                        </div>

                        {{-- Xác nhận mật khẩu --}}
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>

                        {{-- Submit Button --}}
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Đặt lại mật khẩu
                            </button>
                        </div>
                    </form>

                    {{-- Back to Login --}}
                    <div class="mt-3 text-center">
                        <a href="{{ route('login') }}" class="text-decoration-none">
                            ← Quay lại đăng nhập
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
