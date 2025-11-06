# 🚀 Quick Start Guide - Docker Setup

## Khởi động nhanh
## Khởi tạo SSL Cert trước

### Development (khuyến nghị cho dev)
```bash
# Start all services including phpmyadmin, mailpit, ngrok
docker compose --profile dev up -d --build

# Hoặc dùng Makefile
make dev-up
```

### Production
```bash
# Start only core services (app, mysql, redis)
docker compose --profile prod up -d --build

# Hoặc dùng Makefile
make prod-up
```

## 🌐 URLs sau khi khởi động

| Service | URL | Credentials |
|---------|-----|-------------|
| **Application (HTTPS)** | https://localhost | - |
| **Application (HTTP)** | http://localhost (→ HTTPS) | - |
| **PhpMyAdmin** (dev only) | http://localhost:8080 | DB_USERNAME / DB_PASSWORD |
| **Mailpit** (dev only) | http://localhost:8025 | - |
| **Ngrok Dashboard** (dev only) | http://localhost:4040 | - |

⚠️ **Browser sẽ cảnh báo về SSL** vì đây là self-signed certificate. Click "Advanced" > "Proceed to localhost" để tiếp tục.

## 📋 Lệnh thường dùng

### Makefile Commands
```bash
make help              # Xem tất cả commands
make dev-up            # Start development
make dev-down          # Stop development
make dev-logs          # Xem logs
make dev-shell         # Vào container shell

make migrate           # Run migrations
make cache-clear       # Clear all caches
make test              # Run tests

make ngrok-url         # Lấy ngrok public URL
```

### Docker Commands (không dùng Makefile)
```bash
# Development (with phpmyadmin, mailpit, ngrok)
docker compose --profile dev up -d
docker compose --profile dev down
docker compose --profile dev logs -f
docker compose exec app bash

# Production (only core services)
docker compose --profile prod up -d
docker compose --profile prod down
docker compose --profile prod logs -f
docker compose exec app bash
```

### Laravel Commands trong Container
```bash
# Via Makefile
make artisan cmd="migrate"
make artisan cmd="db:seed"
make artisan cmd="cache:clear"

# Direct Docker
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan cache:clear
```

## 🔧 Setup lần đầu

```bash
# 1. Clone project (nếu chưa có)
git clone <repository-url>
cd webshop

# 2. Copy environment file
cp .env.example .env

# 3. Cập nhật .env với thông tin database và ngrok token
# DB_HOST=mysql
# DB_DATABASE=laravel
# DB_USERNAME=laravel
# DB_PASSWORD=password
# NGROK_AUTHTOKEN=your_token_here

# 4. Generate SSL certificate
./generate-ssl-cert.sh

# 5. Build và start containers (development mode)
docker compose --profile dev up -d --build
# Hoặc: make dev-up

# 6. Install dependencies (nếu chưa có vendor/)
docker compose exec app composer install

# 7. Generate app key
docker compose exec app php artisan key:generate

# 8. Run migrations & seeders
docker compose exec app php artisan migrate:fresh --seed

# 9. Build frontend assets
npm install
npm run dev
```

## 📊 Container Architecture

```
┌─────────────────────────────────────────┐
│         Laravel App Container           │
│  ┌─────────────┐    ┌────────────────┐ │
│  │   Nginx     │────│   PHP-FPM      │ │
│  │   (Port 80) │    │   (Port 9000)  │ │
│  └─────────────┘    └────────────────┘ │
│           │                             │
│      Supervisor                         │
└───────────┬─────────────────────────────┘
            │
     ┌──────┴────────┐
     │               │
┌────▼────┐    ┌────▼────┐    ┌─────────┐
│  MySQL  │    │  Redis  │    │  Ngrok  │
│  :3306  │    │  :6379  │    │  :4040  │
└─────────┘    └─────────┘    └─────────┘
```

## 🐛 Troubleshooting

### Port đã được sử dụng
```bash
# Kiểm tra port nào đang dùng
sudo lsof -i :80
sudo lsof -i :3306

# Stop service đang dùng port
sudo systemctl stop apache2
sudo systemctl stop mysql
```

### Container không start
```bash
# Xem logs
docker-compose logs app

# Rebuild container
docker-compose down
docker-compose up -d --build
```

### Permission errors
```bash
# Fix storage permissions
docker-compose exec app chmod -R 777 storage bootstrap/cache
```

### Database connection errors
```bash
# Đảm bảo DB_HOST=mysql trong .env
# Không dùng 127.0.0.1 hoặc localhost

# Restart containers
docker-compose down
docker-compose up -d
```

## 📚 Tài liệu chi tiết

- [DOCKER_GUIDE.md](./DOCKER_GUIDE.md) - Hướng dẫn Docker đầy đủ
- [MIGRATION_SAIL_TO_DOCKER.md](./MIGRATION_SAIL_TO_DOCKER.md) - Chi tiết migration
- [README.md](./README.md) - Tổng quan project

## ✅ Checklist

- [ ] Đã cài Docker & Docker Compose
- [ ] Đã tạo file `.env` từ `.env.example`
- [ ] Đã cập nhật database credentials trong `.env`
- [ ] Đã lấy Ngrok auth token (nếu cần)
- [ ] Đã generate SSL certificate: `./generate-ssl-cert.sh`
- [ ] Đã start containers: `make dev-up` hoặc `docker compose --profile dev up -d`
- [ ] Đã run migrations: `make migrate`
- [ ] Đã build assets: `npm run dev`
- [ ] Application chạy tại https://localhost (HTTPS)
- [ ] Đã trust self-signed certificate trong browser