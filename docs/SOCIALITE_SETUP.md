# Hướng dẫn cấu hình Laravel Socialite

## Tổng quan
Dự án đã được tích hợp Laravel Socialite để hỗ trợ đăng nhập/đăng ký qua:
- Google
- Facebook
- GitHub

## Các bước cài đặt

### 1. Chạy Migration

Trước tiên, bạn cần chạy migration để thêm các trường cần thiết cho social login:

```bash
php artisan migrate
```

Migration sẽ thêm các trường sau vào bảng `users`:
- `provider` - Tên provider (google, facebook, github)
- `provider_id` - ID của user từ provider
- `avatar` - URL avatar của user từ provider
- `password` sẽ được chuyển thành nullable

### 2. Cấu hình Google OAuth

#### 2.1. Tạo Google OAuth Client

1. Truy cập [Google Cloud Console](https://console.cloud.google.com/)
2. Tạo một project mới hoặc chọn project có sẵn
3. Vào **APIs & Services** > **Credentials**
4. Click **Create Credentials** > **OAuth client ID**
5. Chọn **Web application**
6. Thêm **Authorized redirect URIs**:
   ```
   http://localhost/auth/google/callback
   http://your-domain.com/auth/google/callback
   ```
7. Lưu lại **Client ID** và **Client Secret**

#### 2.2. Cập nhật file .env

```env
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URL=http://localhost/auth/google/callback
```

### 3. Cấu hình Facebook OAuth

#### 3.1. Tạo Facebook App

1. Truy cập [Facebook Developers](https://developers.facebook.com/)
2. Click **My Apps** > **Create App**
3. Chọn **Consumer** > **Continue**
4. Điền thông tin app và tạo app
5. Vào **Settings** > **Basic**
6. Lưu lại **App ID** và **App Secret**
7. Vào **Facebook Login** > **Settings**
8. Thêm **Valid OAuth Redirect URIs**:
   ```
   http://localhost/auth/facebook/callback
   http://your-domain.com/auth/facebook/callback
   ```

#### 3.2. Cập nhật file .env

```env
FACEBOOK_CLIENT_ID=your-facebook-app-id
FACEBOOK_CLIENT_SECRET=your-facebook-app-secret
FACEBOOK_REDIRECT_URL=http://localhost/auth/facebook/callback
```

### 4. Cấu hình GitHub OAuth (Tùy chọn)

#### 4.1. Tạo GitHub OAuth App

1. Truy cập [GitHub Developer Settings](https://github.com/settings/developers)
2. Click **New OAuth App**
3. Điền thông tin:
   - **Application name**: Tên app của bạn
   - **Homepage URL**: `http://localhost` hoặc domain của bạn
   - **Authorization callback URL**: `http://localhost/auth/github/callback`
4. Lưu lại **Client ID** và **Client Secret**

#### 4.2. Cập nhật file .env

```env
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
GITHUB_REDIRECT_URL=http://localhost/auth/github/callback
```

### 5. Cấu hình APP_URL

Đảm bảo `APP_URL` trong file `.env` được cấu hình đúng:

```env
APP_URL=http://localhost
# hoặc
APP_URL=https://your-domain.com
```

## Cách sử dụng

### Đăng nhập/Đăng ký qua Social

1. Người dùng truy cập trang đăng nhập hoặc đăng ký
2. Click vào nút "Đăng nhập với Google" hoặc "Đăng nhập với Facebook"
3. Được chuyển đến trang xác thực của provider
4. Sau khi xác thực thành công, được chuyển về website và tự động đăng nhập

### Flow xử lý

1. **User mới lần đầu đăng nhập**:
   - Tạo tài khoản mới trong database
   - Tự động gán role 'customer'
   - Đăng nhập và chuyển đến trang products

2. **User đã tồn tại (đăng ký thông thường)**:
   - Liên kết tài khoản hiện tại với social account
   - Cập nhật thông tin avatar
   - Đăng nhập bình thường

3. **User đã đăng nhập social trước đó**:
   - Cập nhật avatar mới nhất
   - Đăng nhập và chuyển đến trang phù hợp với role

## Cấu trúc Code

### Controller
- `App\Http\Controllers\Web\SocialAuthController`
  - `redirect($provider)` - Chuyển hướng đến provider
  - `callback($provider)` - Xử lý callback từ provider
  - `findOrCreateUser($socialUser, $provider)` - Tìm hoặc tạo user

### Routes
```php
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
```

### Database Schema
```sql
ALTER TABLE users ADD COLUMN provider VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN provider_id VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(255) NULL;
ALTER TABLE users MODIFY password VARCHAR(255) NULL;
```

### Model User
Đã cập nhật `$fillable` để bao gồm:
- `provider`
- `provider_id`
- `avatar`

## Lưu ý quan trọng

### Bảo mật
1. **KHÔNG** commit file `.env` lên Git
2. Giữ bí mật Client ID và Client Secret
3. Chỉ thêm trusted domains vào Redirect URIs

### Production
1. Đổi tất cả callback URLs sang HTTPS
2. Cập nhật APP_URL thành domain thực
3. Kiểm tra lại tất cả redirect URIs trong provider settings
4. Đảm bảo SSL certificate hợp lệ

### Debugging
Nếu gặp lỗi, kiểm tra:
1. `.env` đã có đầy đủ credentials chưa
2. Callback URL có khớp với cấu hình trong provider không
3. Provider app có được kích hoạt chưa (với Facebook)
4. Laravel cache đã được clear chưa: `php artisan config:clear`

## Tùy chỉnh

### Thêm provider mới
1. Cập nhật validation trong `SocialAuthController`:
```php
if (!in_array($provider, ['google', 'facebook', 'github', 'twitter'])) {
    // ...
}
```

2. Thêm cấu hình trong `config/services.php`:
```php
'twitter' => [
    'client_id' => env('TWITTER_CLIENT_ID'),
    'client_secret' => env('TWITTER_CLIENT_SECRET'),
    'redirect' => env('TWITTER_REDIRECT_URL'),
],
```

3. Thêm nút vào view `login.blade.php` và `register.blade.php`

### Thay đổi redirect sau khi đăng nhập
Chỉnh sửa logic trong `SocialAuthController::callback()`:
```php
if ($user->hasRole('admin') || $user->hasRole('manager')) {
    return redirect()->route('dashboard')->with('success', '...');
}
// ...
```

## Hỗ trợ

Nếu gặp vấn đề, kiểm tra:
- [Laravel Socialite Documentation](https://laravel.com/docs/socialite)
- [Google OAuth Documentation](https://developers.google.com/identity/protocols/oauth2)
- [Facebook Login Documentation](https://developers.facebook.com/docs/facebook-login)
- [GitHub OAuth Documentation](https://docs.github.com/en/developers/apps/building-oauth-apps)

