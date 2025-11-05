# ⚡ Fix Login Redirect - Copy & Paste Commands

## Vấn đề: Login bị redirect về trang login liên tục

---

## 🚀 GIẢI PHÁP NHANH (5 phút)

### Bước 1: Sửa file .env

```bash
nano .env
```

Tìm và sửa các dòng sau (hoặc thêm nếu chưa có):

```env
APP_URL=https://your-domain.com          # ← Thay bằng domain thực tế của bạn
SESSION_DRIVER=redis
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=your-domain.com           # ← Thay bằng domain của bạn
SANCTUM_STATEFUL_DOMAINS=your-domain.com # ← Thay bằng domain của bạn
```

Save: `Ctrl+O`, Enter, `Ctrl+X`

---

### Bước 2: Restart và Clear Cache

```bash
docker compose restart app redis
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
```

---

### Bước 3: Test

1. Xóa cookies trong browser (Ctrl+Shift+Del)
2. Mở cửa sổ ẩn danh (Incognito)
3. Truy cập: `https://your-domain.com/login`
4. Login → Nên KHÔNG bị redirect

---

## ❌ NẾU VẪN BỊ LỖI

### Kiểm tra config:

```bash
docker compose exec app php artisan tinker
```

Trong tinker, gõ:

```php
config('app.url')           // Phải là: "https://your-domain.com"
config('session.secure')    // Phải là: true
config('session.driver')    // Phải là: "redis"
Redis::ping()               // Phải trả về: "PONG"
exit
```

---

### Xem logs lỗi:

```bash
docker compose logs app | tail -50
docker compose exec app tail -30 storage/logs/laravel.log
```

---

## 💡 CHECKLIST NHANH

- [ ] APP_URL = https://domain-thực-tế (KHÔNG phải localhost)
- [ ] SESSION_SECURE_COOKIE = true
- [ ] SESSION_DRIVER = redis
- [ ] SESSION_DOMAIN = domain-thực-tế (KHÔNG có https://)
- [ ] Đã restart: `docker compose restart app redis`
- [ ] Đã clear cache: `php artisan optimize:clear`
- [ ] Đã xóa cookies browser

---

## 🔧 RESET HOÀN TOÀN (nếu cần)

```bash
# Stop containers
docker compose --profile prod down

# Sửa .env (xem Bước 1)
nano .env

# Start lại
docker compose --profile prod up -d

# Clear cache
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache
```

---

## 📋 EXAMPLE .env MẪU

```env
APP_NAME=WebShop
APP_ENV=production
APP_KEY=base64:your-key-here
APP_DEBUG=false
APP_URL=https://example.com

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=example.com

REDIS_HOST=redis
REDIS_PORT=6379

DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=your-password

SANCTUM_STATEFUL_DOMAINS=example.com
```

---

## ⚠️ LƯU Ý QUAN TRỌNG

1. **APP_URL** phải giống CHÍNH XÁC với domain bạn đang truy cập
2. Không có dấu `/` ở cuối APP_URL
3. SESSION_DOMAIN không có `https://` hay `www.`
4. Phải dùng **HTTPS** trên production
5. Sau khi sửa .env, PHẢI clear cache

---

## 🆘 VẪN KHÔNG ĐƯỢC?

Copy các thông tin sau và gửi cho developer:

```bash
# 1. Check config
docker compose exec app php artisan about

# 2. Check logs
docker compose logs app | tail -100 > app-logs.txt

# 3. Check .env (ẩn sensitive data)
grep -E "APP_URL|SESSION_|SANCTUM_" .env
```

---

**Thời gian fix: 3-5 phút**