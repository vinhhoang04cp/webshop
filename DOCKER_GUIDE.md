# Docker Setup Guide

Đây là hướng dẫn sử dụng Docker thay thế cho Laravel Sail với hỗ trợ HTTPS/SSL.

## Cấu trúc Docker

```
docker/
├── nginx/
│   ├── nginx.conf          # Cấu hình nginx chính
│   ├── default.conf        # Virtual host Laravel với SSL
│   └── ssl/
│       ├── cert.pem        # SSL certificate (auto-generated)
│       └── key.pem         # SSL private key (auto-generated)
├── php/
│   ├── php.ini            # Cấu hình PHP
│   └── www.conf           # Cấu hình PHP-FPM
├── mysql/
│   └── my.cnf             # Cấu hình MySQL
└── supervisor/
    └── supervisord.conf   # Quản lý nginx + php-fpm
```

## Lệnh sử dụng

### Development (với PhpMyAdmin, Mailpit, Ngrok)
```bash
# Generate SSL certificate (chỉ cần 1 lần)
./generate-ssl-cert.sh

# Build và chạy containers
docker compose --profile dev up -d --build

# Hoặc dùng Makefile
make dev-up

# Xem logs
make dev-logs

# Chạy lệnh Laravel
make artisan cmd="migrate"
make migrate
make cache-clear

# Dừng containers
make dev-down
```

### Production (chỉ core services)
```bash
# Generate SSL certificate
./generate-ssl-cert.sh

# Build và chạy containers
docker compose --profile prod up -d --build

# Hoặc dùng Makefile
make prod-up

# Xem logs
make prod-logs

# Dừng containers
make prod-down
```

## Services

**Core Services (luôn chạy):**
- **app**: Laravel application với nginx + php-fpm (ports 80, 443)
- **mysql**: MySQL 8.0 (port 3306)
- **redis**: Redis cache (port 6379)

**Development Services (chỉ khi dùng --profile dev):**
- **phpmyadmin**: Web interface cho MySQL (port 8080)
- **mailpit**: Email testing (ports 8025, 1025)
- **ngrok**: Public tunnel cho testing webhooks (port 4040)

## URLs

### Development Mode
- **HTTPS (recommended)**: https://localhost
- **HTTP (auto-redirect)**: http://localhost → https://localhost
- **PhpMyAdmin**: http://localhost:8080
- **Mailpit**: http://localhost:8025
- **Ngrok Dashboard**: http://localhost:4040

### Production Mode
- **HTTPS**: https://localhost
- **HTTP**: http://localhost → https://localhost

## SSL/HTTPS

### Self-signed Certificate (Development)

SSL certificate được tự động generate khi chạy `make dev-up` hoặc `make prod-up`.

Để generate manually:
```bash
./generate-ssl-cert.sh
```

Certificate được lưu tại:
- `docker/nginx/ssl/cert.pem` (certificate)
- `docker/nginx/ssl/key.pem` (private key)

### Trust Self-signed Certificate

**Chrome/Edge:**
1. Truy cập https://localhost
2. Click "Advanced" hoặc "Chi tiết"
3. Click "Proceed to localhost (unsafe)" hoặc "Tiếp tục tới localhost"

**Firefox:**
1. Truy cập https://localhost
2. Click "Advanced" hoặc "Nâng cao"
3. Click "Accept the Risk and Continue" hoặc "Chấp nhận rủi ro và tiếp tục"

### Production SSL Certificate (Real Domain)

Để sử dụng Let's Encrypt SSL cho production:

```bash
# 1. Install certbot trong container
docker compose exec app apt-get update
docker compose exec app apt-get install -y certbot

# 2. Generate certificate (thay your-domain.com)
docker compose exec app certbot certonly --webroot \
  -w /var/www/html/public \
  -d your-domain.com \
  -d www.your-domain.com \
  --email your-email@example.com \
  --agree-tos \
  --no-eff-email

# 3. Update nginx config để dùng Let's Encrypt cert
# Sửa trong docker/nginx/default.conf:
# ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
# ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
```

## Environment Variables

Đảm bảo file `.env` có các cấu hình sau:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password

CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# For development
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

# Ngrok (get from https://dashboard.ngrok.com/get-started/your-authtoken)
NGROK_AUTHTOKEN=your_ngrok_token_here
```

## Sử dụng Ngrok

Ngrok cho phép bạn expose ứng dụng local ra internet (hữu ích cho testing webhooks, OAuth callbacks, v.v.)

### Lấy Ngrok Auth Token
1. Truy cập: https://dashboard.ngrok.com/get-started/your-authtoken
2. Copy auth token
3. Thêm vào file `.env`: `NGROK_AUTHTOKEN=your_token_here`

### Xem Public URL
Sau khi start containers, truy cập: http://localhost:4040

Bạn sẽ thấy public URL dạng: `https://xxxxx.ngrok-free.app`

## Khác biệt với Laravel Sail

1. **Single docker-compose.yml**: Dùng profiles thay vì 2 files riêng
2. **Nginx + PHP-FPM**: Thay vì Apache, performance tốt hơn
3. **HTTPS/SSL support**: Built-in SSL cho development và production
4. **Production ready**: Tối ưu cho deployment
5. **Supervisor**: Quản lý processes
6. **Smaller image**: Tối ưu kích thước
7. **Docker profiles**: Dev/Prod services được quản lý qua profiles

## Docker Profiles

```bash
# Development: bao gồm phpmyadmin, mailpit, ngrok
docker compose --profile dev up -d

# Production: chỉ core services (app, mysql, redis)
docker compose --profile prod up -d

# Tất cả services (không khuyến khích)
docker compose up -d
```

## Deployment lên Server

1. Copy toàn bộ project lên server
2. Tạo file `.env` với cấu hình production
3. Chạy: `docker-compose up -d --build`
4. Chạy migrations: `docker-compose exec app php artisan migrate --force`