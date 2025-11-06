# 🔒 SSL Setup với Nginx Proxy Manager

Hướng dẫn chi tiết cài đặt SSL/HTTPS tự động cho WebShop project.

---

## 📋 Mục lục

1. [Giới thiệu](#giới-thiệu)
2. [Chuẩn bị](#chuẩn-bị)
3. [Cài đặt Nginx Proxy Manager](#cài-đặt-nginx-proxy-manager)
4. [Cấu hình Laravel App](#cấu-hình-laravel-app)
5. [Tạo Proxy Host với SSL](#tạo-proxy-host-với-ssl)
6. [Troubleshooting](#troubleshooting)
7. [Bảo mật](#bảo-mật)

---

## 🎯 Giới thiệu

**Nginx Proxy Manager (NPM)** là một công cụ quản lý reverse proxy với giao diện web, giúp:

- ✅ Tự động lấy SSL certificate từ Let's Encrypt
- ✅ Tự động gia hạn SSL (renewal)
- ✅ Quản lý nhiều domain/subdomain
- ✅ Force HTTPS redirect
- ✅ WebSocket proxy support
- ✅ Access lists & basic authentication

### Kiến trúc

```
Internet
    ↓
Nginx Proxy Manager (Port 80/443)
    ↓
Laravel App Container (Internal Port 80)
    ↓
PHP-FPM → MySQL → Redis
```

---

## 🚀 Chuẩn bị

### 1. Yêu cầu

- ✅ Domain đã trỏ về IP server (A record)
- ✅ Port 80 và 443 đang mở trên firewall
- ✅ Docker & Docker Compose đã cài đặt
- ✅ Laravel app đang chạy

### 2. Kiểm tra DNS

```bash
# Kiểm tra domain đã trỏ đúng IP chưa
nslookup dienthoaicuavinh.name.vn

# Hoặc dùng dig
dig dienthoaicuavinh.name.vn +short

# Kết quả phải trả về IP server của bạn
```

### 3. Kiểm tra port

```bash
# Kiểm tra port 80, 443 đang bị chiếm không
sudo netstat -tulpn | grep -E ':(80|443)'

# Nếu có service nào đang dùng, cần stop lại
sudo systemctl stop nginx     # Nếu có nginx
sudo systemctl stop apache2   # Nếu có apache
```

---

## 📦 Cài đặt Nginx Proxy Manager

### Bước 1: Dừng Laravel app (nếu đang chạy)

```bash
cd /path/to/webshop

# Dừng app hiện tại
docker-compose down
```

### Bước 2: Khởi động Nginx Proxy Manager

```bash
# Start NPM
docker-compose -f docker-compose.npm.yml up -d

# Kiểm tra logs
docker-compose -f docker-compose.npm.yml logs -f nginx-proxy-manager

# Đợi đến khi thấy dòng "Server listening on port 0.0.0.0:80"
# Ctrl+C để thoát logs
```

### Bước 3: Khởi động Laravel app (đã sửa config)

```bash
# Start Laravel app (ports 80/443 không còn expose ra ngoài)
docker-compose up -d

# Kiểm tra containers đang chạy
docker ps
```

Bạn sẽ thấy:

```
CONTAINER ID   IMAGE                              PORTS
abc123...      jc21/nginx-proxy-manager:latest   0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp, 0.0.0.0:81->81/tcp
def456...      webshop_app                       5173/tcp, 80/tcp, 443/tcp
```

**Chú ý**: 
- NPM expose ports 80, 443, 81 ra ngoài
- Laravel app CHỈ expose port 80/443 cho containers khác (không ra internet)

---

## ⚙️ Cấu hình Nginx Proxy Manager

### Bước 1: Truy cập Admin UI

Mở trình duyệt:

```
http://IP_SERVER_CUA_BAN:81
```

**Hoặc nếu đã cấu hình domain cho NPM:**

```
http://npm.your-domain.com:81
```

### Bước 2: Đăng nhập lần đầu

**Tài khoản mặc định:**

```
Email: admin@example.com
Password: changeme
```

**QUAN TRỌNG**: Sau khi login, NPM sẽ **bắt buộc** bạn:
1. Đổi email
2. Đổi password
3. Nhập tên

→ Làm ngay bước này để bảo mật!

### Bước 3: Cấu hình User

1. Click vào **Users** trên menu
2. Click vào user `admin@example.com`
3. Cập nhật:
   - **Name**: Admin WebShop
   - **Email**: your-email@gmail.com (email thật của bạn)
   - **Password**: Mật khẩu mạnh mới
4. Click **Save**

---

## 🌐 Tạo Proxy Host với SSL

### Bước 1: Thêm Proxy Host

1. Vào **Hosts** → **Proxy Hosts**
2. Click **Add Proxy Host**

### Bước 2: Tab "Details"

Điền thông tin:

```
Domain Names:
  - dienthoaicuavinh.name.vn
  - www.dienthoaicuavinh.name.vn (optional)

Scheme: http
Forward Hostname/IP: laravel_app
Forward Port: 80

☑ Cache Assets
☑ Block Common Exploits
☑ Websockets Support (quan trọng cho Laravel Reverb!)
```

**Giải thích:**

- **Domain Names**: Domain của bạn (có thể thêm nhiều domain)
- **Scheme**: `http` (vì Laravel app container chạy http nội bộ)
- **Forward Hostname**: `laravel_app` (tên container Laravel)
- **Forward Port**: `80` (port Nginx trong container)
- **Websockets Support**: ✅ BẮT BUỘC cho chat/notifications real-time

### Bước 3: Tab "SSL"

Cấu hình SSL:

```
SSL Certificate: Request a new SSL Certificate with Let's Encrypt

☑ Force SSL
☑ HTTP/2 Support
☑ HSTS Enabled
☑ HSTS Subdomains

Email Address for Let's Encrypt: your-email@gmail.com

☑ I Agree to the Let's Encrypt Terms of Service
```

**Quan trọng:**
- Email phải thật (Let's Encrypt sẽ gửi thông báo gia hạn)
- **Force SSL**: Tự động redirect HTTP → HTTPS
- **HSTS**: Bảo mật cao hơn, browser sẽ luôn dùng HTTPS

### Bước 4: Lưu và chờ

1. Click **Save**
2. NPM sẽ tự động:
   - Kiểm tra domain có trỏ về server không
   - Request SSL certificate từ Let's Encrypt
   - Cài đặt certificate
   - Cấu hình auto-renewal

**Thời gian:** 30-60 giây

### Bước 5: Kiểm tra

Sau khi thấy status **Online** với **SSL** badge màu xanh:

```bash
# Test HTTPS
curl -I https://dienthoaicuavinh.name.vn

# Kết quả mong đợi:
HTTP/2 200 
server: nginx
```

Mở trình duyệt:

```
https://dienthoaicuavinh.name.vn
```

✅ Bạn sẽ thấy icon ổ khóa màu xanh "Kết nối an toàn"!

---

## 🔧 Cấu hình Laravel App

### 1. Update .env

```bash
# Vào server
cd /path/to/webshop

# Sửa .env
nano .env
```

Cập nhật:

```env
# App URL phải dùng HTTPS
APP_URL=https://dienthoaicuavinh.name.vn

# Session cookie secure
SESSION_SECURE_COOKIE=true

# Trusted proxies (quan trọng!)
TRUSTED_PROXIES=*
```

### 2. Update config/trustedproxy.php

Nếu chưa có, tạo file:

```bash
docker-compose exec app php artisan vendor:publish --provider="Fideloper\Proxy\TrustedProxyServiceProvider"
```

Hoặc tạo thủ công `config/trustedproxy.php`:

```php
<?php

return [
    'proxies' => '*', // Trust all proxies (NPM)
    
    'headers' => [
        'FORWARDED' => 'FORWARDED',
        'X_FORWARDED_FOR' => 'X_FORWARDED_FOR',
        'X_FORWARDED_HOST' => 'X_FORWARDED_HOST',
        'X_FORWARDED_PORT' => 'X_FORWARDED_PORT',
        'X_FORWARDED_PROTO' => 'X_FORWARDED_PROTO',
    ],
];
```

### 3. Restart Laravel app

```bash
docker-compose restart app

# Clear cache
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

---

## 🔍 Troubleshooting

### Lỗi: "502 Bad Gateway"

**Nguyên nhân:**
- Laravel container không chạy
- Tên container sai
- Network không kết nối

**Giải pháp:**

```bash
# Kiểm tra Laravel app có chạy không
docker ps | grep laravel_app

# Kiểm tra logs
docker-compose logs app

# Kiểm tra network
docker network inspect webshop_app-network

# Restart containers
docker-compose restart app
```

### Lỗi: "SSL Certificate Error"

**Nguyên nhân:**
- Domain chưa trỏ đúng IP
- Port 80/443 bị firewall block
- DNS chưa propagate

**Giải pháp:**

```bash
# Kiểm tra DNS
dig dienthoaicuavinh.name.vn +short

# Kiểm tra port từ bên ngoài
telnet YOUR_SERVER_IP 80
telnet YOUR_SERVER_IP 443

# Kiểm tra firewall (Ubuntu)
sudo ufw status
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw reload

# Xem logs NPM
docker-compose -f docker-compose.npm.yml logs nginx-proxy-manager
```

### Lỗi: "Mixed Content Warning"

**Nguyên nhân:**
- Blade templates vẫn dùng `http://` hardcoded
- Assets không dùng HTTPS

**Giải pháp:**

```php
// Trong blade templates, dùng:
{{ asset('css/app.css') }}  // ✅ Đúng
{{ secure_asset('css/app.css') }}  // ✅ Đúng

// KHÔNG dùng:
<img src="http://domain.com/image.jpg">  // ❌ Sai
```

```php
// Trong AppServiceProvider.php
public function boot()
{
    if ($this->app->environment('production')) {
        \URL::forceScheme('https');
    }
}
```

### WebSocket không hoạt động

**Nguyên nhân:**
- Chưa bật "Websockets Support" trong NPM
- Laravel Reverb chưa config đúng

**Giải pháp:**

1. Trong NPM Proxy Host, tab Details:
   - ✅ Websockets Support

2. Trong `.env`:
```env
REVERB_HOST=dienthoaicuavinh.name.vn
REVERB_PORT=443
REVERB_SCHEME=https
```

3. Trong `config/broadcasting.php`:
```php
'reverb' => [
    'driver' => 'reverb',
    'key' => env('REVERB_APP_KEY'),
    'secret' => env('REVERB_APP_SECRET'),
    'app_id' => env('REVERB_APP_ID'),
    'options' => [
        'host' => env('REVERB_HOST', '0.0.0.0'),
        'port' => env('REVERB_PORT', 8080),
        'scheme' => env('REVERB_SCHEME', 'http'),
        'useTLS' => env('REVERB_SCHEME') === 'https',
    ],
],
```

---

## 🛡️ Bảo mật

### 1. Đổi port Admin UI (khuyến nghị)

Mặc định NPM admin ở port 81 → dễ bị tấn công

**Cách đổi:**

```yaml
# docker-compose.npm.yml
services:
  nginx-proxy-manager:
    ports:
      - '80:80'
      - '443:443'
      - '8888:81'  # Đổi từ 81 → 8888 (hoặc port bất kỳ)
```

Restart:

```bash
docker-compose -f docker-compose.npm.yml down
docker-compose -f docker-compose.npm.yml up -d
```

Giờ truy cập: `http://IP:8888`

### 2. Tạo Proxy Host cho NPM Admin UI

Để truy cập NPM qua domain (với SSL):

1. Tạo subdomain: `npm.dienthoaicuavinh.name.vn` → A record → IP server
2. Trong NPM, tạo Proxy Host mới:
   - Domain: `npm.dienthoaicuavinh.name.vn`
   - Forward: `nginx-proxy-manager:81`
   - SSL: Request Let's Encrypt
3. Truy cập: `https://npm.dienthoaicuavinh.name.vn`

### 3. Firewall rules

```bash
# Ubuntu UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw allow 8888/tcp    # NPM Admin (nếu cần truy cập từ xa)
sudo ufw enable

# Kiểm tra
sudo ufw status numbered
```

### 4. Access Lists (NPM feature)

Bảo vệ Admin UI bằng username/password:

1. Vào **Access Lists** → **Add Access List**
2. Name: `Admin Only`
3. Tab **Authorization**:
   - ✅ Satisfy Any
   - Add username/password
4. Tab **Access** → Add IP whitelist (nếu cần)
5. Save

Sau đó vào Proxy Host của NPM admin, chọn Access List này.

### 5. Rate Limiting

NPM có built-in rate limiting:

1. Edit Proxy Host
2. Tab **Advanced**
3. Custom Nginx Configuration:

```nginx
limit_req_zone $binary_remote_addr zone=mylimit:10m rate=10r/s;
limit_req zone=mylimit burst=20 nodelay;
```

---

## 📊 Monitoring & Maintenance

### 1. Kiểm tra SSL expiry

NPM tự động gia hạn, nhưng nên kiểm tra:

```bash
# Xem SSL info
echo | openssl s_client -servername dienthoaicuavinh.name.vn -connect dienthoaicuavinh.name.vn:443 2>/dev/null | openssl x509 -noout -dates

# Hoặc dùng online tool:
# https://www.ssllabs.com/ssltest/
```

### 2. Backup NPM data

```bash
# Backup volumes
docker run --rm \
  -v webshop_npm_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/npm-backup-$(date +%Y%m%d).tar.gz -C /data .

# Restore
docker run --rm \
  -v webshop_npm_data:/data \
  -v $(pwd):/backup \
  alpine tar xzf /backup/npm-backup-YYYYMMDD.tar.gz -C /data
```

### 3. Update NPM

```bash
# Pull latest image
docker-compose -f docker-compose.npm.yml pull

# Recreate container
docker-compose -f docker-compose.npm.yml up -d --force-recreate
```

### 4. Logs

```bash
# NPM logs
docker-compose -f docker-compose.npm.yml logs -f --tail=100

# Access logs
docker exec nginx_proxy_manager cat /data/logs/proxy-host-1_access.log

# Error logs
docker exec nginx_proxy_manager cat /data/logs/proxy-host-1_error.log
```

---

## 📝 Checklist hoàn chỉnh

### Trước khi bắt đầu

- [ ] Domain đã trỏ về IP server (A record)
- [ ] Port 80, 443 đang mở
- [ ] Docker & Docker Compose đã cài
- [ ] Laravel app đang chạy bình thường (HTTP)

### Cài đặt

- [ ] Dừng Laravel app: `docker-compose down`
- [ ] Start NPM: `docker-compose -f docker-compose.npm.yml up -d`
- [ ] Sửa `docker-compose.yml` (remove ports 80/443)
- [ ] Start Laravel app: `docker-compose up -d`
- [ ] Truy cập NPM admin: `http://IP:81`
- [ ] Đổi password mặc định

### Cấu hình Proxy Host

- [ ] Tạo Proxy Host mới
- [ ] Domain: `dienthoaicuavinh.name.vn`
- [ ] Forward: `laravel_app:80`
- [ ] ✅ Websockets Support
- [ ] ✅ Block Common Exploits
- [ ] Request SSL Certificate
- [ ] ✅ Force SSL
- [ ] ✅ HTTP/2 Support

### Cấu hình Laravel

- [ ] Update `APP_URL=https://...` trong `.env`
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `TRUSTED_PROXIES=*`
- [ ] Update Reverb config (nếu dùng)
- [ ] Clear cache: `php artisan config:cache`
- [ ] Restart: `docker-compose restart app`

### Kiểm tra

- [ ] Test HTTPS: `curl -I https://dienthoaicuavinh.name.vn`
- [ ] Mở trình duyệt, check icon ổ khóa
- [ ] Test redirect HTTP → HTTPS
- [ ] Test WebSocket (nếu có)
- [ ] Test form submission (login, checkout)
- [ ] Check mixed content warnings (F12 Console)

### Bảo mật

- [ ] Đổi port NPM admin (81 → 8888)
- [ ] Cấu hình firewall
- [ ] Tạo Access List cho NPM admin
- [ ] Backup NPM data
- [ ] Set up monitoring

---

## 🎯 Kết luận

Sau khi làm theo hướng dẫn này, website của bạn sẽ:

✅ **HTTPS** với SSL certificate miễn phí từ Let's Encrypt  
✅ **Tự động gia hạn** SSL hàng tháng  
✅ **Force redirect** HTTP → HTTPS  
✅ **WebSocket** support cho real-time features  
✅ **Security headers** (HSTS, X-Frame-Options, etc.)  
✅ **Quản lý dễ dàng** qua Web UI  

**Không còn cảnh báo "Không bảo mật" nữa!** 🎉

---

## 📞 Support

Nếu gặp vấn đề:

1. Kiểm tra [Troubleshooting](#troubleshooting)
2. Xem logs NPM và Laravel
3. Check firewall & DNS
4. Google lỗi cụ thể

**NPM Documentation**: https://nginxproxymanager.com/guide/

---

**Last updated**: 06/11/2025  
**Version**: 1.0
