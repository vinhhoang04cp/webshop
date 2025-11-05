# Hướng dẫn đầy đủ: Triển khai Laravel Socialite

## 📋 Mục lục
1. [Tổng quan](#tổng-quan)
2. [Các bước triển khai kỹ thuật](#các-bước-triển-khai-kỹ-thuật)
3. [Cấu hình Google OAuth chi tiết](#cấu-hình-google-oauth-chi-tiết)
4. [Cấu hình Facebook OAuth](#cấu-hình-facebook-oauth)
5. [Cấu hình GitHub OAuth](#cấu-hình-github-oauth)
6. [Testing và Debugging](#testing-và-debugging)
7. [Security và Production](#security-và-production)
8. [Troubleshooting](#troubleshooting)

---

## Tổng quan

### Tính năng đã triển khai
Dự án đã được tích hợp **Laravel Socialite** để hỗ trợ đăng nhập/đăng ký qua:
- ✅ Google OAuth 2.0
- ✅ Facebook Login
- ✅ GitHub OAuth (tùy chọn)

### Ưu điểm
- 🚀 Đăng nhập nhanh chóng, không cần nhập password
- 🎨 UI/UX hiện đại với nút social login
- 🔐 Bảo mật cao nhờ OAuth 2.0
- 🔄 Tự động liên kết tài khoản nếu email đã tồn tại
- 👤 Tự động lấy avatar từ social provider

### Flow hoạt động
```
User clicks "Đăng nhập với Google"
    ↓
Redirect to Google OAuth
    ↓
User xác thực với Google
    ↓
Google redirect về callback URL
    ↓
Laravel nhận user info từ Google
    ↓
Tìm/tạo user trong database
    ↓
Tự động đăng nhập
    ↓
Redirect theo role (admin→dashboard, customer→products)
```

---

## Các bước triển khai kỹ thuật

### Bước 1: Cài đặt Packages

#### 1.1. Cài đặt Laravel Socialite
```bash
composer require laravel/socialite
```

**Package được cài**: `laravel/socialite` v5.23

#### 1.2. Cài đặt Doctrine DBAL (để modify column)
```bash
composer require doctrine/dbal
```

**Package được cài**: `doctrine/dbal` v4.3

### Bước 2: Tạo Migration

#### 2.1. Generate migration file
```bash
php artisan make:migration add_social_login_fields_to_users_table --table=users
```

#### 2.2. Cập nhật migration file

**File**: `database/migrations/2025_10_23_144525_add_social_login_fields_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('provider')->nullable()->after('remember_token');
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('provider_id');
            
            // Cho phép password null khi đăng nhập bằng social
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id', 'avatar']);
        });
    }
};
```

**Giải thích các trường**:
- `provider`: Tên provider (google, facebook, github)
- `provider_id`: ID duy nhất của user từ provider
- `avatar`: URL avatar từ provider
- `password`: Chuyển thành nullable vì social login không cần password

#### 2.3. Chạy migration
```bash
php artisan migrate
```

### Bước 3: Cập nhật Model User

**File**: `app/Models/User.php`

Thêm các field vào `$fillable`:
```php
protected $fillable = [
    'name',
    'email',
    'password',
    'phone',
    'address',
    'provider',      // ← Thêm
    'provider_id',   // ← Thêm
    'avatar',        // ← Thêm
];
```

### Bước 4: Tạo SocialAuthService và Controller

#### 4.1. Generate service
```bash
php artisan make:service SocialAuthService
```

**File**: `app/Services/SocialAuthService.php`

```php
<?php

namespace App\Services;

use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Exception;
use Illuminate\Support\Facades\DB;

class SocialAuthService
{
    /**
     * Tìm hoặc tạo user từ thông tin social
     */
    public function findOrCreateUser($socialUser, $provider)
    {
        // Tìm user theo provider và provider_id
        $user = User::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($user) {
            // Cập nhật avatar nếu user đã tồn tại
            $user->update([
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $user;
        }

        // Tìm user theo email (liên kết account)
        $existingUser = User::where('email', $socialUser->getEmail())->first();

        if ($existingUser) {
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
            ]);

            return $existingUser;
        }

        // Tạo user mới
        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                'email' => $socialUser->getEmail(),
                'provider' => $provider,
                'provider_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar(),
                'password' => null,
            ]);

            // Gán role customer mặc định
            $customerRole = Role::where('role_name', 'customer')->first();
            if ($customerRole) {
                UserRole::create([
                    'user_id' => $user->id,
                    'role_id' => $customerRole->role_id,
                    'assigned_at' => now(),
                ]);
            }

            DB::commit();

            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Kiểm tra provider hợp lệ
     */
    public function isValidProvider($provider)
    {
        return in_array($provider, ['google', 'facebook', 'github']);
    }

    /**
     * Lấy redirect route dựa trên role của user
     */
    public function getRedirectRoute($user)
    {
        if ($user->hasRole('admin') || $user->hasRole('manager')) {
            return 'dashboard';
        } elseif ($user->hasRole('customer')) {
            return 'products.index';
        } else {
            return 'home';
        }
    }
}
```

#### 4.2. Generate controller
```bash
php artisan make:controller Web/SocialAuthController
```

**File**: `app/Http/Controllers/Web/SocialAuthController.php`

```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\SocialAuthService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

    public function redirect($provider)
    {
        if (!$this->socialAuthService->isValidProvider($provider)) {
            return redirect()->route('login')->withErrors([
                'error' => 'Provider không hợp lệ.',
            ]);
        }

        try {
            return Socialite::driver($provider)->redirect();
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'error' => 'Không thể kết nối đến '.ucfirst($provider).'. Vui lòng thử lại sau.',
            ]);
        }
    }

    public function callback($provider)
    {
        if (!$this->socialAuthService->isValidProvider($provider)) {
            return redirect()->route('login')->withErrors([
                'error' => 'Provider không hợp lệ.',
            ]);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
            $user = $this->socialAuthService->findOrCreateUser($socialUser, $provider);

            Auth::login($user);

            $redirectRoute = $this->socialAuthService->getRedirectRoute($user);

            return redirect()->route($redirectRoute)
                ->with('success', 'Đăng nhập thành công qua '.ucfirst($provider).'!');
        } catch (Exception $e) {
            return redirect()->route('login')->withErrors([
                'error' => 'Đăng nhập thất bại. Vui lòng thử lại. Lỗi: '.$e->getMessage(),
            ]);
        }
    }
}
```

**💡 Ưu điểm của cách tiếp cận với Service:**

1. **SocialAuthService**:
   - Tập trung business logic
   - Dễ test và tái sử dụng
   - Tách biệt khỏi HTTP concerns

2. **SocialAuthController**:
   - Chỉ xử lý HTTP request/response
   - Gọn gàng, dễ đọc
   - Tuân thủ Single Responsibility Principle

3. **Logic tách biệt**:
   - `findOrCreateUser()`: Xử lý user creation/linking
   - `isValidProvider()`: Validation
   - `getRedirectRoute()`: Routing logic

### Bước 5: Thêm Routes

**File**: `routes/web.php`

Thêm import:
```php
use App\Http\Controllers\Web\SocialAuthController;
```

Thêm routes:
```php
// Social Authentication Routes
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])
    ->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])
    ->name('social.callback');
```

**Routes này sẽ tạo**:
- `/auth/google/redirect` - Chuyển đến Google
- `/auth/google/callback` - Nhận callback từ Google
- `/auth/facebook/redirect` - Chuyển đến Facebook
- `/auth/facebook/callback` - Nhận callback từ Facebook

### Bước 6: Cập nhật Views

#### 6.1. Login Page

**File**: `resources/views/auth/login.blade.php`

Thêm sau form đăng nhập:
```blade
<div class="social-divider mt-4 mb-3">
    <span>HOẶC</span>
</div>

<div class="social-login-buttons">
    <a href="{{ route('social.redirect', 'google') }}" class="btn btn-outline-danger btn-lg w-100 mb-2">
        <i class="fab fa-google me-2"></i>Đăng nhập với Google
    </a>
    <a href="{{ route('social.redirect', 'facebook') }}" class="btn btn-outline-primary btn-lg w-100 mb-2">
        <i class="fab fa-facebook me-2"></i>Đăng nhập với Facebook
    </a>
</div>
```

#### 6.2. Register Page

**File**: `resources/views/auth/register.blade.php`

Thêm tương tự như login page (nhưng text là "Đăng ký với...")

### Bước 7: Thêm CSS

**File**: `resources/css/auth.css`

```css
/* Social Login Styles */
.social-divider {
    display: flex;
    align-items: center;
    text-align: center;
}

.social-divider::before,
.social-divider::after {
    content: '';
    flex: 1;
    border-bottom: 1px solid #dee2e6;
}

.social-divider span {
    padding: 0 10px;
    color: #6c757d;
    font-size: 0.875rem;
    font-weight: 500;
}

.social-login-buttons .btn {
    transition: all 0.3s ease;
}

.social-login-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
```

### Bước 8: Cấu hình Services

**File**: `config/services.php`

Thêm vào cuối file (trước `];`):
```php
/*
|--------------------------------------------------------------------------
| Laravel Socialite Services
|--------------------------------------------------------------------------
*/

'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URL', env('APP_URL').'/auth/google/callback'),
],

'facebook' => [
    'client_id' => env('FACEBOOK_CLIENT_ID'),
    'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URL', env('APP_URL').'/auth/facebook/callback'),
],

'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    'redirect' => env('GITHUB_REDIRECT_URL', env('APP_URL').'/auth/github/callback'),
],
```

---

## Cấu hình Google OAuth chi tiết

### Bước 1: Truy cập Google Cloud Console

1. Mở trình duyệt và truy cập: https://console.cloud.google.com/
2. Đăng nhập bằng tài khoản Google của bạn

### Bước 2: Tạo hoặc chọn Project

#### Nếu chưa có project:
1. Click vào dropdown project ở đầu trang
2. Click **"NEW PROJECT"** hoặc **"新建项目"**
3. Nhập tên project (ví dụ: "WebShop OAuth")
4. Click **"CREATE"** hoặc **"创建"**
5. Đợi project được tạo (khoảng 10-30 giây)

#### Nếu đã có project:
1. Click vào dropdown project
2. Chọn project bạn muốn sử dụng

### Bước 3: Kích hoạt Google+ API (nếu cần)

1. Từ menu bên trái, vào **"APIs & Services"** > **"Library"**
2. Tìm kiếm "Google+ API" hoặc "People API"
3. Click vào và nhấn **"Enable"**

### Bước 4: Tạo OAuth Consent Screen

1. Vào **"APIs & Services"** > **"OAuth consent screen"**
2. Chọn **"External"** (cho phép bất kỳ ai đăng nhập)
3. Click **"CREATE"** hoặc **"创建"**

**Điền thông tin**:
- **App name**: `WebShop` (hoặc tên app của bạn)
- **User support email**: Email của bạn
- **Developer contact information**: Email của bạn

4. Click **"SAVE AND CONTINUE"**
5. Skip phần Scopes (click **"SAVE AND CONTINUE"**)
6. Skip phần Test users (click **"SAVE AND CONTINUE"**)
7. Review và click **"BACK TO DASHBOARD"**

### Bước 5: Tạo OAuth Client ID

1. Vào **"APIs & Services"** > **"Credentials"** (客户端)
2. Click **"+ CREATE CREDENTIALS"** ở đầu trang
3. Chọn **"OAuth client ID"**

**Cấu hình OAuth Client**:

4. **Application type**: Chọn **"Web application"**
5. **Name**: Nhập tên (ví dụ: "WebShop Web Client")

6. **Authorized JavaScript origins** (tùy chọn):
   ```
   http://localhost
   http://127.0.0.1
   ```

7. **Authorized redirect URIs** (quan trọng!):
   Click **"+ Add URI"** và thêm:
   ```
   http://localhost/auth/google/callback
   ```
   
   Nếu bạn có domain thực, thêm thêm:
   ```
   https://yourdomain.com/auth/google/callback
   ```

8. Click **"CREATE"** hoặc **"创建"**

### Bước 6: Lưu Credentials

Một popup sẽ hiện ra với:
- **Client ID**: `1019683768885-at8ia1eugsoldkeki3r0qp4jigk2ojnp.apps.googleusercontent.com`
- **Client Secret**: `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`

**⚠️ Quan trọng**: 
- Copy cả 2 giá trị này ngay
- Client Secret sẽ không hiển thị lại (nhưng có thể tạo mới)
- Giữ bí mật, không share công khai

### Bước 7: Thêm vào .env

Mở file `.env` trong project và thêm:

```env
GOOGLE_CLIENT_ID=1019683768885-at8ia1eugsoldkeki3r0qp4jigk2ojnp.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=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
GOOGLE_REDIRECT_URL=http://localhost/auth/google/callback
```

**Lưu ý**:
- Thay thế bằng Client ID và Secret thực tế của bạn
- Đảm bảo `APP_URL` trong `.env` đã đúng
- Không có khoảng trắng thừa

### Bước 8: Clear Cache Laravel

```bash
php artisan config:clear
php artisan cache:clear
```

### Bước 9: Test

1. Truy cập: `http://localhost/login`
2. Click nút **"Đăng nhập với Google"**
3. Được chuyển đến trang đăng nhập Google
4. Chọn tài khoản Google
5. Cho phép app truy cập thông tin
6. Được redirect về website và tự động đăng nhập

**Nếu thành công**: Bạn sẽ thấy thông báo "Đăng nhập thành công qua Google!"

---

## Cấu hình Facebook OAuth

### Bước 1: Truy cập Facebook Developers

1. Truy cập: https://developers.facebook.com/
2. Đăng nhập bằng tài khoản Facebook

### Bước 2: Tạo App

1. Click **"My Apps"** ở góc trên phải
2. Click **"Create App"**
3. Chọn use case: **"Consumer"** hoặc **"Other"**
4. Click **"Next"**

**Điền thông tin**:
- **App Name**: `WebShop` (hoặc tên của bạn)
- **App Contact Email**: Email của bạn
- **Business Account**: Có thể bỏ trống

5. Click **"Create App"**
6. Xác thực security check (captcha)

### Bước 3: Thêm Facebook Login

1. Từ Dashboard, tìm **"Facebook Login"**
2. Click **"Set Up"**
3. Chọn platform: **"Web"**
4. Nhập Site URL: `http://localhost`
5. Click **"Save"** và **"Continue"**

### Bước 4: Cấu hình Facebook Login

1. Từ menu bên trái, vào **"Facebook Login"** > **"Settings"**
2. Tại **"Valid OAuth Redirect URIs"**, thêm:
   ```
   http://localhost/auth/facebook/callback
   ```
3. Click **"Save Changes"**

### Bước 5: Lấy App ID và App Secret

1. Vào **"Settings"** > **"Basic"**
2. Copy **App ID** (hiển thị ngay)
3. Click **"Show"** bên cạnh **App Secret**
4. Nhập password Facebook để xác thực
5. Copy **App Secret**

### Bước 6: Thêm vào .env

```env
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URL=http://localhost/auth/facebook/callback
```

### Bước 7: Chuyển App sang Live Mode

**Quan trọng**: App mặc định ở Development Mode

1. Vào **"Settings"** > **"Basic"**
2. Scroll xuống dưới
3. Chọn **Category** cho app (ví dụ: "Business and Pages")
4. Thêm **Privacy Policy URL** (nếu có)
5. Click toggle ở đầu trang để chuyển sang **Live Mode**

### Bước 8: Clear Cache và Test

```bash
php artisan config:clear
```

Test tương tự như Google.

---

## Cấu hình GitHub OAuth

### Bước 1: Truy cập GitHub Settings

1. Đăng nhập GitHub
2. Truy cập: https://github.com/settings/developers
3. Click **"OAuth Apps"** tab
4. Click **"New OAuth App"**

### Bước 2: Điền thông tin

- **Application name**: `WebShop`
- **Homepage URL**: `http://localhost`
- **Authorization callback URL**: `http://localhost/auth/github/callback`

### Bước 3: Lấy Credentials

1. Click **"Register application"**
2. Copy **Client ID**
3. Click **"Generate a new client secret"**
4. Copy **Client Secret**

### Bước 4: Thêm vào .env

```env
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URL=http://localhost/auth/github/callback
```

### Bước 5: Thêm nút GitHub vào view (tùy chọn)

Trong `login.blade.php` và `register.blade.php`:
```blade
<a href="{{ route('social.redirect', 'github') }}" class="btn btn-outline-dark btn-lg w-100 mb-2">
    <i class="fab fa-github me-2"></i>Đăng nhập với GitHub
</a>
```

---

## Testing và Debugging

### Testing Checklist

- [ ] **User mới đăng ký qua Google**
  - Tạo user mới trong database
  - Gán role 'customer'
  - Avatar được lưu
  - Redirect đến products page
  
- [ ] **User hiện có đăng nhập lại**
  - Không tạo duplicate user
  - Avatar được cập nhật
  - Session được tạo đúng
  
- [ ] **Liên kết account**
  - User đăng ký thường với email A
  - Đăng nhập social với email A
  - Account được liên kết (không tạo mới)
  
- [ ] **Error handling**
  - Cancel OAuth flow → redirect về login với message
  - Provider không hợp lệ → error message
  - Network error → graceful error

### Debug Mode

Bật debug trong `.env` khi development:
```env
APP_DEBUG=true
```

Xem logs tại: `storage/logs/laravel.log`

### Common Issues

**1. "Invalid redirect URI"**
```
✓ Kiểm tra redirect URI trong provider settings
✓ Đảm bảo không có trailing slash
✓ Đảm bảo protocol khớp (http vs https)
✓ Clear cache: php artisan config:clear
```

**2. "Provider không hợp lệ"**
```
✓ Kiểm tra URL có đúng format: /auth/google/redirect
✓ Provider name phải là: google, facebook, hoặc github
```

**3. "Client ID not found"**
```
✓ Kiểm tra .env có GOOGLE_CLIENT_ID chưa
✓ Chạy: php artisan config:clear
✓ Restart web server
```

**4. "Email null"**
```
✓ Một số provider không trả về email
✓ Request thêm scope cho email
✓ Kiểm tra consent screen settings
```

---

## Security và Production

### Pre-Production Checklist

- [ ] **HTTPS Required**
  ```env
  APP_URL=https://yourdomain.com
  ```

- [ ] **Update Redirect URIs**
  - Thêm production domain vào Google Console
  - Thêm production domain vào Facebook Settings
  - Remove localhost URLs khi đã live

- [ ] **Environment Variables**
  ```env
  APP_ENV=production
  APP_DEBUG=false
  ```

- [ ] **Secure .env file**
  ```bash
  chmod 600 .env
  ```

- [ ] **Secrets Management**
  - Không commit .env lên Git
  - Sử dụng .env.example cho template
  - Lưu secrets trong vault (AWS Secrets Manager, etc.)

### Security Best Practices

1. **Validate Email từ Provider**
   ```php
   if (!$socialUser->getEmail()) {
       throw new Exception('Email is required');
   }
   ```

2. **Check Email Verified**
   ```php
   // Google provides email_verified
   if (isset($socialUser->user['email_verified']) && !$socialUser->user['email_verified']) {
       // Handle unverified email
   }
   ```

3. **Rate Limiting**
   ```php
   Route::middleware(['throttle:10,1'])->group(function () {
       Route::get('/auth/{provider}/redirect', ...);
       Route::get('/auth/{provider}/callback', ...);
   });
   ```

4. **CSRF Protection**
   - Laravel tự động bảo vệ (đã có @csrf trong forms)

5. **Log Social Logins**
   ```php
   Log::info('Social login', [
       'provider' => $provider,
       'email' => $socialUser->getEmail(),
       'ip' => request()->ip(),
   ]);
   ```

### Production URLs

Khi deploy production:

**Google Cloud Console**:
- Thêm: `https://yourdomain.com/auth/google/callback`

**Facebook Developers**:
- Thêm: `https://yourdomain.com/auth/facebook/callback`
- Update App Domains: `yourdomain.com`

**.env file**:
```env
GOOGLE_REDIRECT_URL=https://yourdomain.com/auth/google/callback
FACEBOOK_REDIRECT_URL=https://yourdomain.com/auth/facebook/callback
```

---

## Troubleshooting

### Database Errors

**Error**: `SQLSTATE[42S22]: Column not found: 'provider'`

**Solution**:
```bash
php artisan migrate
# Nếu đã chạy rồi, check migration status:
php artisan migrate:status
```

### OAuth Errors

**Error**: `Client error: GET https://www.googleapis.com/oauth2/v3/userinfo resulted in 401`

**Solution**:
- Client Secret sai hoặc đã expired
- Tạo lại Client Secret trong Google Console
- Cập nhật .env

**Error**: `redirect_uri_mismatch`

**Solution**:
- Redirect URI không khớp
- Kiểm tra Google Console > Credentials > Authorized redirect URIs
- Đảm bảo exact match (including http/https)

### Laravel Errors

**Error**: `Class "Laravel\Socialite\Facades\Socialite" not found`

**Solution**:
```bash
composer require laravel/socialite
composer dump-autoload
php artisan config:clear
```

**Error**: `Undefined array key "email"`

**Solution**:
- Provider không trả về email
- Check user permissions/scopes
- Add fallback:
  ```php
  'email' => $socialUser->getEmail() ?? 'user_'.$socialUser->getId().'@social.local',
  ```

### Permission Issues

**Error**: `This app is blocked`

**Solution**:
- OAuth Consent Screen chưa được verify
- Trong development, thêm test users
- Trong production, submit app for verification

---

## Tổng kết Files đã tạo/sửa

### Created Files
1. `app/Services/SocialAuthService.php` ⭐ NEW
2. `app/Http/Controllers/Web/SocialAuthController.php`
3. `database/migrations/2025_10_23_144525_add_social_login_fields_to_users_table.php`
4. `docs/SOCIALITE_COMPLETE_GUIDE.md` (file này)

### Modified Files
1. `app/Models/User.php` - Thêm fillable fields
2. `routes/web.php` - Thêm social auth routes
3. `config/services.php` - Thêm provider configs
4. `resources/views/auth/login.blade.php` - Thêm social buttons
5. `resources/views/auth/register.blade.php` - Thêm social buttons
6. `resources/css/auth.css` - Thêm social styles
7. `composer.json` - Thêm packages
8. `.env` - Thêm social credentials

---

## Quick Reference

### Lệnh thường dùng
```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Run migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Check routes
php artisan route:list | grep social

# View logs
tail -f storage/logs/laravel.log
```

### URLs
```
Login page: /login
Register page: /register

Google redirect: /auth/google/redirect
Google callback: /auth/google/callback

Facebook redirect: /auth/facebook/redirect
Facebook callback: /auth/facebook/callback
```

### Providers Config Links
- Google: https://console.cloud.google.com/
- Facebook: https://developers.facebook.com/
- GitHub: https://github.com/settings/developers

---

## 📝 Changelog

### Version 2.0 - 26/10/2025
**Cập nhật - Refactor theo Service Pattern:**
- ✅ Thêm **SocialAuthService** để tách business logic
- ✅ Controllers gọn gàng hơn, chỉ xử lý HTTP
- ✅ Cải thiện **code organization** và **testability**
- ✅ Áp dụng **Dependency Injection** pattern
- ✅ Tách biệt concerns: validation, user creation, routing
- ✅ Cập nhật tài liệu theo chuẩn mới

### Version 1.0 - 23/10/2025
- Phiên bản ban đầu với logic trong Controllers

---

**Tác giả**: Hoàng Quang Vinh  
**Ngày tạo**: 23/10/2025  
**Cập nhật lần cuối**: 26/10/2025  
**Version**: 2.0  
**Status**: ✅ Hoàn thành

**Hỗ trợ**: Xem thêm tại [Laravel Socialite Documentation](https://laravel.com/docs/socialite)

