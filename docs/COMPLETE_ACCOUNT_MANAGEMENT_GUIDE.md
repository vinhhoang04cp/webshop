# 📚 Hướng dẫn đầy đủ: Quản lý Tài khoản & Lấy lại Mật khẩu

> **Tài liệu hợp nhất** - Hướng dẫn chi tiết cách tạo và sử dụng tính năng Quản lý Tài khoản và Reset Password qua Email

---

## 📋 Mục lục

1. [Tổng quan](#tổng-quan)
2. [Các tính năng](#các-tính-năng)
3. [Hướng dẫn tạo từng bước](#hướng-dẫn-tạo-từng-bước)
4. [Cài đặt và cấu hình](#cài-đặt-và-cấu-hình)
5. [Hướng dẫn sử dụng](#hướng-dẫn-sử-dụng)
6. [Troubleshooting](#troubleshooting)

---

## 🎯 Tổng quan

Dự án WebShop đã được tích hợp 2 tính năng quan trọng:

### 1. **Quên mật khẩu & Reset qua Email**
- Người dùng có thể yêu cầu đặt lại mật khẩu qua email
- Gửi link reset có token bảo mật, thời hạn 24 giờ
- Email template đẹp, chuyên nghiệp

### 2. **Quản lý Tài khoản (Profile Management)**
- Cập nhật thông tin cá nhân (tên, SĐT, địa chỉ)
- Upload/thay đổi avatar (max 2MB)
- Đổi mật khẩu an toàn
- Tự động chọn layout phù hợp (Admin/Manager/Customer)

---

## ✨ Các tính năng

### ✅ Reset Password
- ✅ Form yêu cầu reset (`/forgot-password`)
- ✅ Gửi email chứa link reset
- ✅ Xác thực token bảo mật
- ✅ Form nhập password mới
- ✅ Token tự động xóa sau khi dùng
- ✅ Hết hạn sau 24 giờ

### ✅ Profile Management
- ✅ Hiển thị thông tin tài khoản
- ✅ Cập nhật tên, SĐT, địa chỉ
- ✅ Upload avatar (jpeg, png, jpg, gif)
- ✅ Đổi mật khẩu (yêu cầu password cũ)
- ✅ Dynamic layout (Admin vs Customer)
- ✅ Truy cập từ dropdown menu & icon user

---

## 🔨 Hướng dẫn tạo từng bước

### 📦 Bước 1: Tạo Service và Form Requests

#### 1.1. Tạo Service cho Password Reset

```bash
php artisan make:service PasswordResetService
```

**File tạo:** `app/Services/PasswordResetService.php`

```php
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Gửi link reset password qua email
     */
    public function sendResetLink($email)
    {
        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetLink = route('password.reset', ['token' => $token, 'email' => $email]);

        Mail::send('emails.reset-password', ['resetLink' => $resetLink], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Yêu cầu đặt lại mật khẩu');
        });

        return true;
    }

    /**
     * Xác thực và reset password
     */
    public function resetPassword($email, $token, $newPassword)
    {
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$passwordReset) {
            throw new \Exception('Token không hợp lệ.');
        }

        if (!Hash::check($token, $passwordReset->token)) {
            throw new \Exception('Token không hợp lệ.');
        }

        $created = \Carbon\Carbon::parse($passwordReset->created_at);
        if ($created->addHours(24)->isPast()) {
            throw new \Exception('Token đã hết hạn. Vui lòng yêu cầu lại.');
        }

        $user = User::where('email', $email)->first();
        $user->password = Hash::make($newPassword);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
```

#### 1.2. Tạo Form Requests

```bash
php artisan make:request PasswordResetLinkRequest
php artisan make:request PasswordResetRequest
```

**File:** `app/Http/Requests/PasswordResetLinkRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetLinkRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
        ];
    }
}
```

**File:** `app/Http/Requests/PasswordResetRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
            'token' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.exists' => 'Email không tồn tại trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu mới.',
            'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ];
    }
}
```

#### 1.3. Tạo Controller

```bash
php artisan make:controller Web/PasswordResetController
```

**File:** `app/Http/Controllers/Web/PasswordResetController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetLinkRequest;
use App\Http\Requests\PasswordResetRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\Request;

class PasswordResetController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(PasswordResetLinkRequest $request)
    {
        try {
            $this->passwordResetService->sendResetLink($request->email);
            return back()->with('success', 'Link đặt lại mật khẩu đã được gửi đến email của bạn!');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Không thể gửi email. Vui lòng thử lại sau.']);
        }
    }

    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(PasswordResetRequest $request)
    {
        try {
            $this->passwordResetService->resetPassword(
                $request->email,
                $request->token,
                $request->password
            );

            return redirect()->route('login')->with('success', 'Mật khẩu đã được đặt lại thành công. Vui lòng đăng nhập.');
        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }
}
```

**💡 Ưu điểm của cách tiếp cận này:**
- ✅ **Separation of Concerns**: Business logic ở Service, validation ở Form Request
- ✅ **Reusability**: Service có thể tái sử dụng ở nhiều nơi
- ✅ **Testability**: Dễ dàng test từng component
- ✅ **Maintainability**: Code sạch, dễ maintain

---

### 📦 Bước 2: Tạo Service và Form Requests cho Profile Management

#### 2.1. Tạo ProfileService

```bash
php artisan make:service ProfileService
```

**File:** `app/Services/ProfileService.php`

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Cập nhật thông tin profile
     */
    public function updateProfile($user, $data, $avatarFile = null)
    {
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        $user->address = $data['address'];

        if ($avatarFile) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Lưu avatar mới
            $avatarPath = $avatarFile->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }

        $user->save();

        return $user;
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword($user, $currentPassword, $newPassword)
    {
        if (!Hash::check($currentPassword, $user->password)) {
            throw new \Exception('Mật khẩu hiện tại không đúng.');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }
}
```

#### 2.2. Tạo Form Requests

```bash
php artisan make:request ProfileUpdateRequest
php artisan make:request ChangePasswordRequest
```

**File:** `app/Http/Requests/ProfileUpdateRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập họ tên.',
            'name.max' => 'Họ tên không được vượt quá 255 ký tự.',
            'phone.max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá 500 ký tự.',
            'avatar.image' => 'File phải là hình ảnh.',
            'avatar.mimes' => 'Hình ảnh phải có định dạng: jpeg, png, jpg, gif.',
            'avatar.max' => 'Kích thước hình ảnh không được vượt quá 2MB.',
        ];
    }
}
```

**File:** `app/Http/Requests/ChangePasswordRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'new_password.confirmed' => 'Mật khẩu xác nhận không khớp.',
        ];
    }
}
```

#### 2.3. Tạo ProfileController

```bash
php artisan make:controller Web/ProfileController
```

**File:** `app/Http/Controllers/Web/ProfileController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Services\ProfileService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index()
    {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    }

    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = Auth::user();
        $avatarFile = $request->hasFile('avatar') ? $request->file('avatar') : null;

        $this->profileService->updateProfile($user, $request->validated(), $avatarFile);

        return back()->with('success', 'Cập nhật thông tin thành công!');
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = Auth::user();

        try {
            $this->profileService->changePassword(
                $user,
                $request->current_password,
                $request->new_password
            );

            return back()->with('success', 'Đổi mật khẩu thành công!');
        } catch (\Exception $e) {
            return back()->withErrors(['current_password' => $e->getMessage()]);
        }
    }
}
```

---

### 📦 Bước 3: Tạo Views

#### 3.1. Form Quên Mật khẩu

**File:** `resources/views/auth/forgot-password.blade.php`

```php
@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Quên mật khẩu</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-muted">Nhập email của bạn để nhận link đặt lại mật khẩu.</p>

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Gửi link đặt lại mật khẩu
                            </button>
                        </div>
                    </form>

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
```

#### 3.2. Form Reset Password

**File:** `resources/views/auth/reset-password.blade.php`

```php
@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Đặt lại mật khẩu</h4>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   value="{{ $email }}" 
                                   readonly>
                        </div>

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
                            <small class="form-text text-muted">Mật khẩu phải có ít nhất 8 ký tự.</small>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation" 
                                   required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                Đặt lại mật khẩu
                            </button>
                        </div>
                    </form>

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
```

#### 3.3. Email Template

**Tạo thư mục:** `resources/views/emails/`

**File:** `resources/views/emails/reset-password.blade.php`

```html
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f4f4f4;
            border-radius: 5px;
            padding: 20px;
        }
        .content {
            background-color: white;
            padding: 30px;
            border-radius: 5px;
        }
        h1 {
            color: #0d6efd;
            margin-top: 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #0b5ed7;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 10px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>Đặt lại mật khẩu</h1>
            
            <p>Xin chào,</p>
            
            <p>Bạn nhận được email này vì chúng tôi đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
            
            <p>Vui lòng nhấp vào nút bên dưới để đặt lại mật khẩu:</p>
            
            <a href="{{ $resetLink }}" class="button">Đặt lại mật khẩu</a>
            
            <div class="warning">
                <strong>⚠️ Lưu ý:</strong> Link này sẽ hết hạn sau 24 giờ.
            </div>
            
            <p>Nếu bạn không thể nhấp vào nút, hãy sao chép và dán URL sau vào trình duyệt của bạn:</p>
            <p style="word-break: break-all; color: #0d6efd;">{{ $resetLink }}</p>
            
            <p>Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.</p>
            
            <div class="footer">
                <p>Trân trọng,<br>{{ config('app.name') }}</p>
                <p style="margin-top: 10px;">
                    Email này được gửi tự động. Vui lòng không trả lời email này.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
```

#### 3.4. Profile Page

**Tạo thư mục:** `resources/views/profile/`

**File:** `resources/views/profile/index.blade.php`

*File này khá dài (230 dòng), bao gồm 2 tabs: Thông tin cá nhân và Đổi mật khẩu*

> **Lưu ý:** View này đã được tạo với dynamic layout, tự động chọn `layouts.app` cho Admin/Manager và `layouts.customer` cho Customer.

---

### 📦 Bước 4: Cập nhật Routes

**File:** `routes/web.php`

Thêm các routes sau:

```php
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\ProfileController;

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

// Profile Management Routes (yêu cầu đăng nhập)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');
});
```

---

### 📦 Bước 5: Cập nhật Views

#### 5.1. Thêm link "Quên mật khẩu?" vào form login

**File:** `resources/views/auth/login.blade.php`

Tìm phần password và thêm link:

```php
<div class="d-flex justify-content-between align-items-center mb-3">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="remember" name="remember">
        <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
    </div>
    <a href="{{ route('password.request') }}" class="text-decoration-none small">Quên mật khẩu?</a>
</div>
```

#### 5.2. Thêm menu Profile vào Sidebar (Dashboard)

**File:** `resources/views/components/sidebar.blade.php`

Thêm link trước closing tag `</nav>`:

```php
<div class="border-top mt-3 pt-3" style="border-color: rgba(255,255,255,0.1) !important;"></div>

<a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
    <i class="fas fa-user-circle"></i> Tài khoản của tôi
</a>
```

#### 5.3. Thêm Dropdown User Menu (Customer Layout)

**File:** `resources/views/layouts/customer.blade.php`

Trong phần header-top, thay thế phần user info:

```php
@auth
    <div class="dropdown d-inline-block">
        <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-user"></i> {{ Auth::user()->name }}
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li>
                <a class="dropdown-item" href="{{ route('profile.index') }}">
                    <i class="fas fa-user-circle"></i> Quản lý tài khoản
                </a>
            </li>
            @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager'))
            <li>
                <a class="dropdown-item" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            @endif
            <li><hr class="dropdown-divider"></li>
            <li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </button>
                </form>
            </li>
        </ul>
    </div>
@else
    <!-- Login/Register links -->
@endauth
```

Trong phần header-main, thêm icon user:

```php
<div class="col-md-3">
    <div class="header-icons text-end">
        @auth
        <a href="{{ route('profile.index') }}" title="Tài khoản của tôi">
            <i class="fas fa-user-circle"></i>
        </a>
        @endauth
        <a href="{{ route('cart.index') }}" title="Giỏ hàng">
            <i class="fas fa-shopping-cart"></i>
        </a>
        <!-- ... -->
    </div>
</div>
```

---

## ⚙️ Cài đặt và cấu hình

### 1️⃣ Database

Bảng `password_reset_tokens` đã có sẵn trong Laravel. Chỉ cần chạy migration:

```bash
# Khởi động database (nếu dùng Docker)
docker-compose up -d

# Chạy migration
php artisan migrate

# Hoặc reset toàn bộ
php artisan migrate:fresh --seed
```

### 2️⃣ Cấu hình Email

#### Option 1: Gmail (Production)

**File:** `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@webshop.com
MAIL_FROM_NAME="WebShop"
```

**Lấy App Password Gmail:**

1. Truy cập: https://myaccount.google.com/security
2. Bật "2-Step Verification"
3. Vào "App passwords"
4. Chọn "Mail" → "Other" → Nhập "WebShop"
5. Copy mật khẩu 16 ký tự → Dán vào `MAIL_PASSWORD`

#### Option 2: Mailtrap (Testing)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@webshop.com
MAIL_FROM_NAME="WebShop"
```

Đăng ký miễn phí: https://mailtrap.io

#### Option 3: Log (Development)

```env
MAIL_MAILER=log
```

Email sẽ lưu vào `storage/logs/laravel.log`

### 3️⃣ Cấu hình Storage (cho upload avatar)

```bash
php artisan storage:link
```

Lệnh này tạo symbolic link: `public/storage` → `storage/app/public`

---

## 📖 Hướng dẫn sử dụng

### 🔑 Reset Password

#### Từ phía User:

1. **Vào trang đăng nhập:** `/login`
2. **Click:** "Quên mật khẩu?"
3. **Nhập email** đã đăng ký
4. **Nhấn:** "Gửi link đặt lại mật khẩu"
5. **Kiểm tra email** → Click link trong email
6. **Nhập mật khẩu mới** (min 8 ký tự)
7. **Xác nhận mật khẩu**
8. **Nhấn:** "Đặt lại mật khẩu"
9. **Đăng nhập** với mật khẩu mới

#### Flow:

```
/login → Click "Quên mật khẩu?"
    ↓
/forgot-password → Nhập email
    ↓
Gửi email (chứa link reset)
    ↓
User click link trong email
    ↓
/reset-password/{token} → Nhập password mới
    ↓
/login → Đăng nhập thành công
```

### 👤 Quản lý Profile

#### Cách truy cập:

**Cách 1: Dropdown Menu (Header Top)**
- Nhìn góc phải header (nền xanh đậm)
- Click vào tên user
- Chọn "📝 Quản lý tài khoản"

**Cách 2: Icon User (Header Main)**
- Nhìn thanh header màu trắng
- Click icon 👤 (bên cạnh giỏ hàng)

**Cách 3: Sidebar (Dashboard - Admin/Manager)**
- Click "Tài khoản của tôi" ở sidebar

**Cách 4: Direct URL**
- Truy cập: `/profile`

#### Cập nhật thông tin:

1. Truy cập `/profile`
2. Tab **"Thông tin cá nhân"**:
   - Cập nhật: Tên, SĐT, Địa chỉ
   - Upload avatar mới (jpeg, png, jpg, gif - max 2MB)
   - Nhấn "Cập nhật thông tin"

#### Đổi mật khẩu:

1. Truy cập `/profile`
2. Tab **"Đổi mật khẩu"**:
   - Nhập mật khẩu hiện tại
   - Nhập mật khẩu mới (min 8 ký tự)
   - Xác nhận mật khẩu mới
   - Nhấn "Đổi mật khẩu"

---

## 🎨 UI/UX Features

### Dynamic Layout
- **Admin/Manager:** Sử dụng `layouts.app` (Dashboard)
- **Customer:** Sử dụng `layouts.customer` (Shopping)

### Breadcrumb (Customer)
```
Trang chủ > Quản lý tài khoản
```

### Styling
- Màu brand: `#00d4aa` (Xanh mint)
- Gradient buttons & borders
- Hover effects (lift + shadow)
- Icons cho tất cả elements
- Responsive design

---

## 🔒 Bảo mật

### Reset Password Token:
- ✅ Token được hash trước khi lưu database
- ✅ Thời hạn: 24 giờ
- ✅ Tự động xóa sau khi sử dụng
- ✅ Không thể tái sử dụng
- ✅ Kiểm tra email tồn tại

### Upload Avatar:
- ✅ Validate: chỉ file ảnh (jpeg, png, jpg, gif)
- ✅ Max size: 2MB
- ✅ Tự động xóa ảnh cũ khi upload mới
- ✅ Lưu vào `storage/app/public/avatars`

### Change Password:
- ✅ Yêu cầu nhập password hiện tại
- ✅ Validate password mới (min 8 ký tự)
- ✅ Xác nhận password (confirmation)
- ✅ Hash password trước khi lưu

---

## 🧪 Test Cases

### Reset Password:

| Test Case | Input | Expected Output |
|-----------|-------|-----------------|
| Email không tồn tại | fake@email.com | ❌ "Email không tồn tại" |
| Email hợp lệ | user@example.com | ✅ "Link đã gửi đến email" |
| Token không hợp lệ | invalid-token | ❌ "Token không hợp lệ" |
| Token hết hạn (>24h) | expired-token | ❌ "Token đã hết hạn" |
| Password < 8 ký tự | "123456" | ❌ "Password min 8 ký tự" |
| Password confirmation sai | "12345678" vs "87654321" | ❌ "Password không khớp" |
| Reset thành công | Valid data | ✅ Redirect to login |

### Profile Management:

| Test Case | Input | Expected Output |
|-----------|-------|-----------------|
| Cập nhật tên | "New Name" | ✅ "Cập nhật thành công" |
| Upload avatar hợp lệ | image.jpg (1MB) | ✅ Avatar uploaded |
| Upload file không phải ảnh | document.pdf | ❌ "File phải là ảnh" |
| Upload file >2MB | large.jpg (3MB) | ❌ "Max 2MB" |
| Đổi password đúng | Valid current + new | ✅ "Đổi password thành công" |
| Đổi password sai current | Wrong current password | ❌ "Password hiện tại sai" |

---

## 🐛 Troubleshooting

### ❌ Email không được gửi

**Triệu chứng:** Không nhận được email reset password

**Giải pháp:**

1. **Kiểm tra config `.env`:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. **Test với MAIL_MAILER=log:**
   ```env
   MAIL_MAILER=log
   ```
   Check: `storage/logs/laravel.log`

3. **Kiểm tra Gmail App Password:**
   - Đúng format 16 ký tự
   - Không có khoảng trắng
   - 2-Step Verification đã bật

4. **Kiểm tra firewall:**
   ```bash
   telnet smtp.gmail.com 587
   ```

### ❌ Avatar không hiển thị

**Triệu chứng:** Upload thành công nhưng không thấy ảnh

**Giải pháp:**

1. **Chạy storage:link:**
   ```bash
   php artisan storage:link
   ```

2. **Kiểm tra symbolic link:**
   ```bash
   ls -la public/storage
   ```
   
3. **Kiểm tra quyền folder:**
   ```bash
   chmod -R 775 storage
   chmod -R 775 public/storage
   ```

4. **Kiểm tra đường dẫn:**
   ```php
   // Trong view
   {{ asset('storage/' . Auth::user()->avatar) }}
   ```

### ❌ Route không tìm thấy

**Triệu chứng:** 404 khi truy cập `/profile` hoặc `/forgot-password`

**Giải pháp:**

1. **Clear route cache:**
   ```bash
   php artisan route:clear
   php artisan route:cache
   ```

2. **Kiểm tra routes đã đăng ký:**
   ```bash
   php artisan route:list | grep profile
   php artisan route:list | grep password
   ```

### ❌ Token không hợp lệ

**Triệu chứng:** Luôn báo "Token không hợp lệ" dù mới gửi

**Giải pháp:**

1. **Kiểm tra bảng `password_reset_tokens` có tồn tại:**
   ```sql
   SHOW TABLES LIKE 'password_reset_tokens';
   ```

2. **Chạy migration:**
   ```bash
   php artisan migrate
   ```

3. **Kiểm tra timezone:**
   ```env
   # .env
   APP_TIMEZONE=Asia/Ho_Chi_Minh
   ```

### ❌ Middleware auth lỗi

**Triệu chứng:** Redirect về login khi đã đăng nhập

**Giải pháp:**

1. **Clear session:**
   ```bash
   php artisan session:clear
   ```

2. **Kiểm tra session driver:**
   ```env
   SESSION_DRIVER=database
   ```

3. **Migrate sessions table:**
   ```bash
   php artisan migrate
   ```

---

## 📋 Checklist triển khai

### ✅ Backend

- [x] PasswordResetController created
- [x] ProfileController created
- [x] Routes added (password reset + profile)
- [x] Validation messages (tiếng Việt)
- [x] Email sending logic
- [x] Token verification logic
- [x] Avatar upload handling
- [x] Password change logic

### ✅ Frontend

- [x] forgot-password.blade.php
- [x] reset-password.blade.php
- [x] emails/reset-password.blade.php
- [x] profile/index.blade.php
- [x] Login form updated (Quên mật khẩu link)
- [x] Sidebar updated (Tài khoản link)
- [x] Customer layout updated (Dropdown + Icon)

### ✅ Database

- [x] password_reset_tokens table (có sẵn)
- [x] Migration successful
- [x] Seeders working

### ✅ Configuration

- [x] .env.example updated
- [x] Email config documented
- [x] Storage link instructions
- [x] Routes documented

### ✅ Documentation

- [x] Hướng dẫn tạo từng bước
- [x] Hướng dẫn cấu hình
- [x] Hướng dẫn sử dụng
- [x] Troubleshooting guide
- [x] Test cases

---

## 🎯 Tóm tắt nhanh

### Files đã tạo/sửa:

```
Services:
✨ app/Services/PasswordResetService.php
✨ app/Services/ProfileService.php

Form Requests:
✨ app/Http/Requests/PasswordResetLinkRequest.php
✨ app/Http/Requests/PasswordResetRequest.php
✨ app/Http/Requests/ProfileUpdateRequest.php
✨ app/Http/Requests/ChangePasswordRequest.php

Controllers:
✨ app/Http/Controllers/Web/PasswordResetController.php
✨ app/Http/Controllers/Web/ProfileController.php

Views:
✨ resources/views/auth/forgot-password.blade.php
✨ resources/views/auth/reset-password.blade.php
✨ resources/views/emails/reset-password.blade.php
✨ resources/views/profile/index.blade.php
✏️ resources/views/auth/login.blade.php (thêm link)
✏️ resources/views/components/sidebar.blade.php (thêm menu)
✏️ resources/views/layouts/customer.blade.php (dropdown + icon)

Routes:
✏️ routes/web.php (6 routes mới)

Config:
✏️ .env.example (email config)
```

### Commands cần chạy:

```bash
# 1. Migration
php artisan migrate

# 2. Storage link (cho avatar)
php artisan storage:link

# 3. Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Truy cập:

- 🔑 Reset password: `/forgot-password`
- 👤 Profile: `/profile`
- 📧 Test email: Check `storage/logs/laravel.log` (nếu MAIL_MAILER=log)

---

## 🚀 Ready to Use!

Tất cả tính năng đã sẵn sàng:

1. ✅ Lấy lại mật khẩu qua email
2. ✅ Quản lý profile (tất cả roles)
3. ✅ Upload avatar
4. ✅ Đổi mật khẩu
5. ✅ Dynamic layout
6. ✅ Dropdown user menu
7. ✅ Icon user trong header
8. ✅ Breadcrumb cho customer

---

**📝 Lưu ý cuối cùng:**

- Token reset password hết hạn sau **24 giờ**
- Avatar max **2MB**, format: jpeg, png, jpg, gif
- Password tối thiểu **8 ký tự**
- Nhớ cấu hình email trong `.env` cho production
- Test kỹ trước khi deploy

---

**🎉 Hoàn thành 100%!**

Chúc bạn triển khai thành công! 🚀

---

## 📝 Changelog

### Version 2.0 - 26/10/2025
**Cập nhật lớn - Refactor theo Service Pattern:**
- ✅ Thêm **PasswordResetService** và **ProfileService** 
- ✅ Tách validation ra **Form Requests** riêng
- ✅ Controllers chỉ xử lý HTTP, business logic ở Services
- ✅ Cải thiện **code organization** và **testability**
- ✅ Áp dụng **Dependency Injection** pattern
- ✅ Cập nhật tài liệu theo chuẩn mới

### Version 1.0 - 23/10/2025
- Phiên bản ban đầu với logic trong Controllers

---

**Tác giả**: Hoàng Quang Vinh  
**Cập nhật lần cuối**: 26/10/2025  
**Version**: 2.0
