# Fix: Login Redirect Loop trên Production

## Vấn đề
Khi login trên production (internet), sau khi đăng nhập thành công bị redirect về trang login liên tục.

## Nguyên nhân

### 1. **APP_URL không khớp với domain thực tế**
Laravel sử dụng `APP_URL` để generate URLs và validate redirects.

### 2. **Session Cookie Settings**
Khi dùng HTTPS, cookies phải có `secure` flag.

### 3. **Session Driver**
Database/Redis session cần migration table hoặc kết nối đúng.

### 4. **Trusted Proxies** 
Nếu đằng sau proxy/load balancer (nginx, cloudflare), Laravel không nhận được scheme đúng.

---

## Giải pháp

### Bước 1: Cấu hình Environment Variables

Cập nhật file `.env` trên production server:

```env
# App URL - QUAN TRỌNG: Phải khớp với domain thực tế
APP_URL=https://your-domain.com
APP_ENV=production
APP_DEBUG=false

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=your-domain.com

# Redis (nếu dùng redis session)
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Sanctum (nếu dùng)
SANCTUM_STATEFUL_DOMAINS=your-domain.com,www.your-domain.com
```

### Bước 2: Trusted Proxies (Quan trọng nếu dùng Nginx/Cloudflare)

Kiểm tra file `app/Http/Middleware/TrustProxies.php`:

```php
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*'; // Trust all proxies (hoặc chỉ định IP cụ thể)

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
```

### Bước 3: Nếu dùng Session Driver = Database

Chạy migration để tạo sessions table:

```bash
# Trong container
docker compose exec app php artisan session:table
docker compose exec app php artisan migrate --force

# Clear cache
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Bước 4: Cấu hình Sanctum (nếu dùng API authentication)

File `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
    '%s%s',
    'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1',
    Sanctum::currentApplicationUrlWithPort()
))),
```

Trong `.env`:
```env
SANCTUM_STATEFUL_DOMAINS=your-domain.com,www.your-domain.com
```

### Bước 5: CORS Configuration

File `config/cors.php`:

```php
return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [env('APP_URL')],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### Bước 6: Clear All Caches

```bash
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

### Bước 7: Restart Containers

```bash
docker compose restart app
docker compose restart redis
```

---

## Checklist Debug

### ✅ Check 1: APP_URL
```bash
docker compose exec app php artisan tinker
>>> config('app.url')
# Phải trả về: "https://your-domain.com"
```

### ✅ Check 2: Session Config
```bash
docker compose exec app php artisan tinker
>>> config('session.driver')
# Trả về: "redis" hoặc "database"

>>> config('session.secure')
# Phải trả về: true (nếu dùng HTTPS)

>>> config('session.domain')
# Trả về: "your-domain.com" hoặc null
```

### ✅ Check 3: Redis Connection (nếu dùng)
```bash
docker compose exec app php artisan tinker
>>> Redis::ping()
# Phải trả về: "PONG"
```

### ✅ Check 4: Database Sessions Table (nếu dùng)
```bash
docker compose exec app php artisan tinker
>>> DB::table('sessions')->count()
# Không lỗi
```

### ✅ Check 5: Kiểm tra Headers
Trong browser DevTools (F12) -> Network tab:
- Kiểm tra request headers có `X-Forwarded-Proto: https`
- Kiểm tra response có set-cookie với `secure; samesite=lax`

---

## Common Issues & Solutions

### Issue 1: "419 Page Expired" sau khi submit form
**Nguyên nhân:** CSRF token mismatch

**Fix:**
```env
SESSION_SECURE_COOKIE=true
SESSION_DOMAIN=your-domain.com
```

### Issue 2: Session không persist giữa các requests
**Nguyên nhân:** Cookie không được set/send

**Fix:**
1. Check `SESSION_SECURE_COOKIE=true` nếu dùng HTTPS
2. Check `SESSION_DOMAIN` khớp với domain
3. Check browser không block cookies

### Issue 3: Login thành công nhưng redirect về login
**Nguyên nhân:** 
- APP_URL không đúng
- Trusted proxies chưa config
- Session driver không hoạt động

**Fix:**
1. Set `APP_URL=https://your-domain.com`
2. Set `$proxies = '*'` trong TrustProxies middleware
3. Dùng Redis session thay vì file/cookie

### Issue 4: Mixed Content Errors
**Nguyên nhân:** APP_URL là http nhưng site chạy https

**Fix:**
```env
APP_URL=https://your-domain.com
ASSET_URL=https://your-domain.com
```

---

## Production Environment Template

File `.env` mẫu cho production:

```env
APP_NAME=WebShop
APP_ENV=production
APP_KEY=base64:your-generated-key-here
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=your-secure-password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=your-domain.com

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=your-domain.com,www.your-domain.com

# Ngrok (xóa hoặc comment trong production)
# NGROK_AUTHTOKEN=
```

---

## Quick Fix Commands

```bash
# 1. SSH vào server
ssh user@your-server

# 2. Vào thư mục project
cd /path/to/webshop

# 3. Sửa .env
nano .env
# Update: APP_URL, SESSION_SECURE_COOKIE, SESSION_DOMAIN

# 4. Clear và cache lại
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache

# 5. Restart
docker compose restart app

# 6. Test
curl -I https://your-domain.com
```

---

## Testing

### Test 1: Session Cookie
```bash
curl -I https://your-domain.com
# Check header: Set-Cookie: ...session=...; secure; httponly; samesite=lax
```

### Test 2: Login Flow
1. Clear browser cookies
2. Mở incognito/private window
3. Login với admin account
4. Check Network tab trong DevTools
5. Verify không bị redirect loop

### Test 3: Multiple Browsers
Test trên Chrome, Firefox, Safari để đảm bảo cookies hoạt động đúng

---

## Prevention

Để tránh vấn đề này trong tương lai:

1. ✅ Luôn set `APP_URL` chính xác
2. ✅ Dùng Redis session cho production
3. ✅ Config TrustProxies đúng
4. ✅ Test trên staging environment trước
5. ✅ Monitor session errors trong logs
6. ✅ Backup .env trước khi thay đổi

---

Nếu vẫn không fix được, kiểm tra:
- Browser console errors (F12)
- Laravel logs: `docker compose exec app tail -f storage/logs/laravel.log`
- Nginx logs: `docker compose logs app`
- Redis logs: `docker compose logs redis`