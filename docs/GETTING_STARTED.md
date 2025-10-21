# 🚀 Getting Started - Hướng dẫn Cài đặt & Khởi động

> **Mục đích**: Hướng dẫn setup môi trường development từ đầu

## 📋 Mục lục
1. [Yêu cầu hệ thống](#yêu-cầu-hệ-thống)
2. [Cài đặt lần đầu](#cài-đặt-lần-đầu)
3. [Khởi động dự án](#khởi-động-dự-án)
4. [Truy cập ứng dụng](#truy-cập-ứng-dụng)
5. [Troubleshooting](#troubleshooting)

---

## ✅ Yêu cầu hệ thống

### Phần mềm bắt buộc
- **Docker Desktop** (latest version)
  - Windows/macOS: [Download](https://www.docker.com/products/docker-desktop)
  - Linux: Docker Engine + Docker Compose
- **Git** (version 2.0+)
- **Node.js** (version 18.x hoặc 20.x)
- **NPM** hoặc **Yarn**

### Yêu cầu tối thiểu
- RAM: 4GB (khuyến nghị 8GB)
- Dung lượng: 10GB trống
- OS: Windows 10/11, macOS 10.15+, Ubuntu 20.04+

---

## 📥 Cài đặt lần đầu

### Bước 1: Clone Repository
```bash
git clone https://github.com/vinhhoang04cp/webshop.git
cd webshop
```

### Bước 2: Cài đặt PHP Dependencies
```bash
# Nếu chưa có Composer cục bộ, dùng Docker
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Hoặc nếu đã có Composer
composer install
```

### Bước 3: Cài đặt JavaScript Dependencies
```bash
npm install
# hoặc
yarn install
```

### Bước 4: Cấu hình Environment
```bash
# Copy file .env example
cp .env.example .env

# Generate application key
./vendor/bin/sail artisan key:generate
```

### Bước 5: Khởi động Docker Services
```bash
# Khởi động tất cả services (MySQL, Redis, Mailpit, PhpMyAdmin)
./vendor/bin/sail up -d

# Tạo alias để gõ nhanh hơn (khuyến nghị)
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
sail up -d
```

### Bước 6: Chạy Migrations & Seeders
```bash
# Migration + Seed data mẫu
sail artisan migrate:fresh --seed
```

### Bước 7: Build Frontend Assets
```bash
# Development mode (hot reload)
npm run dev

# Production build
npm run build
```

---

## 🎬 Khởi động dự án

### Cách 1: Sử dụng Composer Script (Khuyến nghị)
```bash
# Chạy tất cả services cùng lúc:
# - Laravel server (port 8000)
# - Queue worker
# - Real-time logs (Pail)
# - Vite dev server (HMR)
composer dev
```

### Cách 2: Chạy từng service riêng
```bash
# Terminal 1: Laravel development server
sail artisan serve

# Terminal 2: Queue worker
sail artisan queue:listen

# Terminal 3: Real-time logs
sail artisan pail

# Terminal 4: Vite dev server
npm run dev
```

---

## 🌐 Truy cập ứng dụng

Sau khi khởi động thành công:

| Service | URL | Mô tả |
|---------|-----|-------|
| **Web Application** | http://localhost | Trang web chính |
| **Admin Dashboard** | http://localhost/dashboard | Quản trị (cần login) |
| **API** | http://localhost/api | RESTful API |
| **PhpMyAdmin** | http://localhost:8080 | Quản lý database |
| **Mailpit** | http://localhost:8025 | Email testing |
| **Vite Dev Server** | http://localhost:5173 | Frontend HMR |

## 🐳 Docker Services

Dự án sử dụng Laravel Sail với các services:

### 1. laravel.test (App Container)
- PHP 8.4
- Ports: 80 (HTTP), 5173 (Vite)
- XDebug support

### 2. MySQL 8.0
- Port: 3306
- Database: `laravel`
- Username: `sail`
- Password: `password`

### 3. Redis (Alpine)
- Port: 6379
- Use: Cache, Sessions, Queue

### 4. Mailpit
- SMTP: 1025
- Web UI: 8025
- Email testing trong development

### 5. PhpMyAdmin
- Port: 8080
- GUI quản lý MySQL database

---

## 🔧 Lệnh thường dùng

### Docker/Sail Commands
```bash
# Khởi động containers
sail up -d

# Dừng containers
sail down

# Khởi động lại
sail restart

# Xem logs
sail logs

# Truy cập container shell
sail shell
```

### Artisan Commands
```bash
# Database
sail artisan migrate
sail artisan migrate:fresh --seed
sail artisan db:seed

# Cache
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear

# Generate code
sail artisan make:model Product -mfsc
sail artisan make:controller ProductController
```

### Testing
```bash
# Run all tests
sail artisan test

# Run specific test
sail artisan test --filter=ProductTest

# Code formatting
./vendor/bin/pint
```

---

## ❗ Troubleshooting

### 1. Port đã được sử dụng

**Vấn đề**: Container không khởi động do port conflict

**Giải pháp**:
```bash
# Kiểm tra port
sudo lsof -i :80
sudo lsof -i :3306

# Thay đổi port trong .env
APP_PORT=8000
FORWARD_DB_PORT=33060
```

### 2. Permission Issues (Linux/macOS)

**Vấn đề**: Lỗi permission khi tạo file/folder

**Giải pháp**:
```bash
# Set ownership
sudo chown -R $USER:$USER .

# Fix storage permissions
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Database Connection Failed

**Vấn đề**: Không kết nối được database

**Giải pháp**:
```bash
# Kiểm tra MySQL container
sail ps

# Kiểm tra logs
sail logs mysql

# Đảm bảo DB_HOST=mysql trong .env
# Restart containers
sail down && sail up -d
```

### 4. Composer Install Failed

**Vấn đề**: Lỗi khi install dependencies

**Giải pháp**:
```bash
# Clear cache
sail composer clear-cache

# Install với ignore platform
sail composer install --ignore-platform-reqs
```

### 5. Frontend Build Issues

**Vấn đề**: Vite không chạy hoặc assets không load

**Giải pháp**:
```bash
# Clear node_modules
rm -rf node_modules package-lock.json
npm install

# Clear Vite cache
rm -rf .vite

# Rebuild
npm run build
```

### 6. Storage Link Missing

**Vấn đề**: Images/files không hiển thị

**Giải pháp**:
```bash
sail artisan storage:link
```

### 7. Clear All Caches

**Khi gặp lỗi lạ**:
```bash
sail artisan optimize:clear

# Hoặc từng cái:
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear
```

---

## 🔄 Workflow Development

### 1. Bắt đầu làm việc
```bash
# 1. Pull code mới
git pull origin main

# 2. Cài đặt dependencies (nếu có thay đổi)
composer install
npm install

# 3. Chạy migrations mới (nếu có)
sail artisan migrate

# 4. Khởi động services
composer dev
```

### 2. Trong quá trình code
```bash
# Xem logs real-time
sail artisan pail

# Chạy tests
sail artisan test

# Format code
./vendor/bin/pint
```

### 3. Trước khi commit
```bash
# 1. Chạy tests
sail artisan test

# 2. Format code
./vendor/bin/pint

# 3. Clear caches
sail artisan optimize:clear

# 4. Commit
git add .
git commit -m "feat: Add new feature"
git push origin feature-branch
```

---

## 📚 Tài liệu liên quan

- **[ARCHITECTURE.md](./ARCHITECTURE.md)** - Kiến trúc hệ thống
- **[TECH_STACK.md](./TECH_STACK.md)** - Tech stack chi tiết
- **[DATABASE.md](./DATABASE.md)** - Database schema
- **[CODING_CONVENTIONS.md](./CODING_CONVENTIONS.md)** - Coding standards

---

## 🆘 Cần hỗ trợ?

1. Kiểm tra [Laravel Documentation](https://laravel.com/docs)
2. Kiểm tra [Laravel Sail Documentation](https://laravel.com/docs/sail)
3. Tìm kiếm trên [Laravel Forums](https://laracasts.com/discuss)
4. Tạo issue trên [GitHub Repository](https://github.com/vinhhoang04cp/webshop/issues)

---

**Cập nhật lần cuối**: 21/10/2025  
**Version**: 3.0  
**Author**: Hoàng Quang Vinh
