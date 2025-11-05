# 🔧 FIX: Google OAuth Login Redirect Loop trong Docker

## 📋 Tóm tắt vấn đề

Khi đăng nhập bằng Google OAuth trong môi trường Docker, bị redirect loop về trang login. Sau khi clear cache/config thì lại hoạt động bình thường.

## 🎯 Nguyên nhân

1. **SESSION_SAME_SITE=strict** - Quá nghiêm ngặt, chặn cookie khi redirect từ Google OAuth
2. **SessionSecurityMiddleware** - Phát hiện sai session hijacking sau OAuth callback
3. **Config cache cũ** - Không tự động clear khi rebuild Docker container

## ✅ Các thay đổi đã thực hiện

### 1. Cập nhật `.env` - Session Configuration

**Thay đổi:**
```bash
# TRƯỚC (gây lỗi)
SESSION_SAME_SITE=strict

# SAU (fix lỗi)
SESSION_SAME_SITE=lax
TRUSTED_PROXIES=*
```

**Giải thích:**
- `SESSION_SAME_SITE=lax` - Cho phép cookie được gửi khi redirect từ Google OAuth
- `TRUSTED_PROXIES=*` - Tin tưởng tất cả proxies (nginx trong Docker) để nhận diện đúng HTTPS

### 2. Cập nhật `SessionSecurityMiddleware.php`

**Thêm phương thức bỏ qua OAuth callbacks:**

```php
/**
 * Kiểm tra xem request có phải từ OAuth callback không
 */
protected function isOAuthCallback(Request $request): bool
{
    $oauthRoutes = [
        'auth/*/callback',
        'auth/google/callback',
        'auth/facebook/callback',
        'auth/github/callback',
    ];

    $path = $request->path();
    foreach ($oauthRoutes as $route) {
        $pattern = str_replace('*', '[^/]+', $route);
        $pattern = '#^'.$pattern.'$#';
        if (preg_match($pattern, $path)) {
            return true;
        }
    }
    return false;
}
```

**Sửa phương thức `handle()`:**
```php
public function handle(Request $request, Closure $next): Response
{
    // Bỏ qua kiểm tra cho OAuth callback routes
    if ($this->isOAuthCallback($request)) {
        return $next($request);
    }
    // ... phần còn lại giữ nguyên
}
```

**Giải thích:**
- Bỏ qua session security checks cho OAuth callback routes
- Tránh false positive khi User Agent/IP có thể thay đổi trong quá trình OAuth

### 3. Thêm script `clear-cache.sh`

Script tự động clear cache trong Docker container:

```bash
./clear-cache.sh
```

### 4. Cập nhật `Dockerfile`

Thêm automatic cache clearing khi build:

```dockerfile
# Clear all Laravel caches to prevent stale config issues
RUN php artisan config:clear || true \
    && php artisan cache:clear || true \
    && php artisan route:clear || true \
    && php artisan view:clear || true
```

## 🚀 Cách áp dụng fix

### Bước 1: Clear cache hiện tại

```bash
# Nếu container đang chạy
./clear-cache.sh

# HOẶC chạy thủ công
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan view:clear
```

### Bước 2: Restart container

```bash
# Restart để áp dụng thay đổi .env
docker compose restart app

# HOẶC rebuild toàn bộ (nếu cần)
docker compose down
docker compose up -d --build
```

### Bước 3: Kiểm tra

1. Truy cập trang login
2. Click "Login with Google"
3. Chọn tài khoản Google
4. Kiểm tra redirect về dashboard (admin) hoặc trang chủ (user)

## 🔍 Debug nếu vẫn gặp vấn đề

### Kiểm tra session cookie

Mở Developer Tools (F12) → Application → Cookies:
- Tìm cookie `laravel-session`
- Kiểm tra `SameSite` = `Lax` (không phải `Strict`)
- Kiểm tra `Secure` = `true`
- Kiểm tra `HttpOnly` = `true`

### Kiểm tra logs

```bash
# Xem logs realtime
docker logs -f laravel_app

# Xem Laravel logs
docker exec laravel_app tail -f storage/logs/laravel.log
```

### Kiểm tra config đã load

```bash
docker exec laravel_app php artisan config:show session

# Kiểm tra SESSION_SAME_SITE
docker exec laravel_app php artisan tinker
>>> config('session.same_site')
```

## 🛡️ Security Notes

### SESSION_SAME_SITE: strict vs lax

**Strict (cũ - gây lỗi):**
- ✅ Bảo mật cao nhất
- ❌ Chặn tất cả cross-site cookies
- ❌ Không hoạt động với OAuth redirects

**Lax (mới - recommended):**
- ✅ Bảo mật tốt (vẫn chống CSRF)
- ✅ Cho phép cookies với safe HTTP methods (GET)
- ✅ Hoạt động với OAuth redirects
- ✅ Google, Facebook, GitHub OAuth đều hoạt động

### TRUSTED_PROXIES

```env
TRUSTED_PROXIES=*
```

**Lưu ý:**
- Trong production, nên chỉ định chính xác IP của proxy
- Trong Docker, `*` là an toàn vì nginx proxy nội bộ
- Giúp Laravel nhận diện đúng HTTPS từ nginx

## 📝 Checklist sau khi áp dụng

- [ ] File `.env` đã update `SESSION_SAME_SITE=lax`
- [ ] File `.env` đã thêm `TRUSTED_PROXIES=*`
- [ ] File `SessionSecurityMiddleware.php` đã update
- [ ] Đã chạy `./clear-cache.sh` hoặc clear cache thủ công
- [ ] Đã restart container với `docker compose restart app`
- [ ] Test login Google với tài khoản admin
- [ ] Test login Google với tài khoản thường
- [ ] Kiểm tra không còn redirect loop

## 🎉 Kết quả mong đợi

Sau khi áp dụng fix:

1. ✅ Login Google không còn redirect loop
2. ✅ Không cần clear cache thủ công sau mỗi lần login
3. ✅ Session được maintain đúng cách
4. ✅ Admin redirect về dashboard
5. ✅ Customer redirect về trang products
6. ✅ Security vẫn được đảm bảo với `lax` mode

## 🔄 Nếu cần rollback

```bash
# Trong .env
SESSION_SAME_SITE=strict  # (giá trị cũ)
# Xóa dòng TRUSTED_PROXIES

# Restart
docker compose restart app
```

## 📞 Support

Nếu vẫn gặp vấn đề:
1. Check logs: `docker logs -f laravel_app`
2. Check session config: `docker exec laravel_app php artisan config:show session`
3. Verify cookies trong browser DevTools
4. Rebuild container: `docker compose up -d --build`
