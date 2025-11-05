# Docker Setup - Troubleshooting & Fixes

## Lỗi đã gặp và cách khắc phục

### 1. ✅ MySQL sql_mode Error
**Lỗi:**
```
Error while setting value 'NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' to 'sql_mode'
```

**Nguyên nhân:** `NO_AUTO_CREATE_USER` đã bị xóa khỏi MySQL 8.0

**Khắc phục:** 
Sửa file `docker/mysql/my.cnf`:
```ini
# Trước (có lỗi)
sql_mode = NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION

# Sau (đã fix)
sql_mode = NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

### 2. ✅ MySQL query_cache Error
**Lỗi:**
```
unknown variable 'query_cache_type=0'
```

**Nguyên nhân:** Query cache đã bị xóa khỏi MySQL 8.0

**Khắc phục:**
Xóa các dòng sau khỏi `docker/mysql/my.cnf`:
```ini
# Xóa các dòng này
query_cache_type = 0
query_cache_size = 0
```

### 3. ✅ Nginx User Error
**Lỗi:**
```
getpwnam("nginx") failed in /etc/nginx/nginx.conf:1
```

**Nguyên nhân:** Trong PHP-FPM container, user mặc định là `www-data`, không phải `nginx`

**Khắc phục:**
Sửa file `docker/nginx/nginx.conf`:
```nginx
# Trước (có lỗi)
user nginx;

# Sau (đã fix)
user www-data;
```

### 4. ✅ Redis PECL Installation Error
**Lỗi:**
```
No releases available for package "pecl.php.net/redis"
install failed
```

**Nguyên nhân:** PECL channel chưa được update hoặc không có phiên bản stable

**Khắc phục:**
Sửa file `Dockerfile`:
```dockerfile
# Trước (có lỗi)
RUN pecl install redis && docker-php-ext-enable redis

# Sau (đã fix)
RUN pecl channel-update pecl.php.net \
    && pecl install redis-6.1.0 \
    && docker-php-ext-enable redis
```

## Cách Reset khi gặp lỗi Database

Nếu MySQL database bị corrupt hoặc có lỗi:

```bash
# 1. Dừng và xóa volumes
docker compose --profile dev down -v

# 2. Khởi động lại (sẽ tạo database mới)
docker compose --profile dev up -d --build

# 3. Kiểm tra MySQL đã sẵn sàng
docker compose logs mysql | grep "ready for connections"
```

## Lệnh hữu ích khi troubleshoot

```bash
# Xem logs của một service cụ thể
docker compose logs <service_name>
docker compose logs app
docker compose logs mysql

# Xem logs realtime
docker compose logs -f app

# Kiểm tra status containers
docker compose ps

# Vào trong container
docker compose exec app bash
docker compose exec mysql bash

# Restart một service
docker compose restart app
docker compose restart mysql

# Rebuild container
docker compose --profile dev up -d --build

# Xóa tất cả và bắt đầu lại
docker compose --profile dev down -v
docker system prune -f
```

## Kiểm tra xem services đã chạy OK chưa

```bash
# MySQL
docker compose logs mysql | grep "ready for connections"
# Kết quả: ready for connections. Version: '8.0.44'

# Nginx + PHP-FPM
docker compose logs app | grep "RUNNING"
# Kết quả: success: nginx entered RUNNING state
# Kết quả: success: php-fpm entered RUNNING state

# Redis
docker compose logs redis
# Kết quả: Ready to accept connections

# Test kết nối từ app container
docker compose exec app php artisan migrate:status
```

## URLs để test

Sau khi containers chạy thành công:

- **Application**: https://localhost (hoặc http://localhost)
- **PhpMyAdmin**: http://localhost:8080
- **Mailpit**: http://localhost:8025
- **Ngrok Dashboard**: http://localhost:4040

## Tips

1. **Luôn check logs** khi có vấn đề: `docker compose logs -f`
2. **Rebuild sau khi sửa config files**: `docker compose --profile dev up -d --build`
3. **Reset database khi corrupt**: `docker compose down -v` rồi `up -d`
4. **Không dùng old MySQL syntax** trong config files (MySQL 8.0+ strict hơn)