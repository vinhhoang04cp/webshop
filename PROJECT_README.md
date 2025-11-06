# 🛒 WebShop E-Commerce Platform

> **Nền tảng thương mại điện tử hoàn chỉnh được xây dựng với Laravel 12, PHP 8.4, và MySQL 8.0**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.0-38B2AC?logo=tailwind-css)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)

---

## 📋 Mục lục

- [Giới thiệu](#-giới-thiệu)
- [Tính năng chính](#-tính-năng-chính)
- [Công nghệ sử dụng](#️-công-nghệ-sử-dụng)
- [Kiến trúc hệ thống](#-kiến-trúc-hệ-thống)
- [Cài đặt nhanh](#-cài-đặt-nhanh)
- [Screenshots](#-screenshots)
- [Tài liệu](#-tài-liệu)
- [API Documentation](#-api-documentation)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Đóng góp](#-đóng-góp)
- [License](#-license)
- [Team](#-team)

---

## 🎯 Giới thiệu

**WebShop** là một nền tảng thương mại điện tử đầy đủ tính năng, được thiết kế cho các doanh nghiệp vừa và nhỏ muốn bán hàng trực tuyến. Hệ thống cung cấp:

- 🛍️ Trải nghiệm mua sắm mượt mà cho khách hàng
- 📊 Dashboard quản trị mạnh mẽ cho Admin/Manager
- 📱 Responsive design hoàn hảo trên mọi thiết bị
- 🔐 Bảo mật cao với Laravel Sanctum
- 💳 Tích hợp thanh toán VNPay
- 🔔 Real-time notifications với Laravel Reverb
- 📧 Email notifications & password reset

### 🎪 Demo

- **Live Demo**: [Coming soon]
- **Admin Demo**: [Coming soon]
- **API Docs**: [Coming soon]

---

## ✨ Tính năng chính

### 👥 Cho Khách Hàng

- ✅ **Đăng ký/Đăng nhập** - Tài khoản người dùng với email verification
- ✅ **Social Login** - Đăng nhập qua Google, Facebook, GitHub
- ✅ **Duyệt sản phẩm** - Tìm kiếm, lọc, sắp xếp sản phẩm
- ✅ **Giỏ hàng** - Thêm/xóa/cập nhật sản phẩm
- ✅ **Áp dụng Coupon** - Mã giảm giá với nhiều điều kiện
- ✅ **Checkout** - Quy trình đặt hàng đơn giản
- ✅ **Thanh toán** - COD và VNPay online payment
- ✅ **Theo dõi đơn hàng** - Xem trạng thái đơn hàng real-time
- ✅ **Đánh giá sản phẩm** - Rating & reviews
- ✅ **Quản lý tài khoản** - Cập nhật thông tin, đổi mật khẩu

### 👨‍💼 Cho Admin/Manager

- ✅ **Dashboard** - Thống kê doanh thu, đơn hàng, sản phẩm
- ✅ **Quản lý sản phẩm** - CRUD sản phẩm, danh mục
- ✅ **Quản lý đơn hàng** - Xem, cập nhật trạng thái đơn hàng
- ✅ **Quản lý tồn kho** - Theo dõi và điều chỉnh inventory
- ✅ **Quản lý người dùng** - Phân quyền RBAC
- ✅ **Quản lý Coupon** - Tạo mã giảm giá với điều kiện
- ✅ **Báo cáo** - Revenue reports, customer analytics
- ✅ **Cấu hình** - Settings & configurations

### 🔧 Tính năng kỹ thuật

- ✅ **RESTful API** - 60+ endpoints cho mobile/SPA
- ✅ **API Authentication** - Token-based với Laravel Sanctum
- ✅ **RBAC** - 4 roles (Admin, Manager, Customer, Guest)
- ✅ **Soft Deletes** - Khôi phục dữ liệu đã xóa
- ✅ **Caching** - Redis cho performance
- ✅ **Queue Jobs** - Background processing
- ✅ **Real-time** - WebSocket với Laravel Reverb
- ✅ **Email** - Transactional emails
- ✅ **Logging** - Comprehensive error tracking
- ✅ **Testing** - Unit & Feature tests

---

## 🛠️ Công nghệ sử dụng

### Backend

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.4 | Core language |
| **Laravel** | 12.x | Framework |
| **MySQL** | 8.0 | Primary database |
| **Redis** | Alpine | Cache & Queue |
| **Laravel Sanctum** | 4.0 | API authentication |
| **Laravel Reverb** | 1.6 | WebSocket server |
| **Laravel Socialite** | 5.23 | OAuth providers |

### Frontend

| Technology | Version | Purpose |
|------------|---------|---------|
| **Blade Templates** | - | Server-side rendering |
| **Tailwind CSS** | 4.0 | Styling framework |
| **Vite** | 7.0 | Build tool |
| **Alpine.js** | - | Lightweight JS framework |
| **Font Awesome** | 6.4 | Icons |

### DevOps & Tools

| Tool | Purpose |
|------|---------|
| **Docker** | Containerization |
| **Docker Compose** | Multi-container orchestration |
| **Nginx** | Web server |
| **PHP-FPM** | PHP process manager |
| **Composer** | PHP dependency manager |
| **NPM** | JS dependency manager |
| **PHPUnit** | Testing framework |
| **Laravel Pint** | Code style fixer |

---

## 🏗️ Kiến trúc hệ thống

### Layered Architecture

```
┌─────────────────────────────────────────┐
│          Presentation Layer             │
│  (Controllers, Views, API Resources)    │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│          Business Logic Layer           │
│     (Services, Use Cases, Policies)     │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│           Data Access Layer             │
│     (Models, Repositories, Database)    │
└─────────────────────────────────────────┘
```

### Design Patterns

- ✅ **MVC Pattern** - Separation of concerns
- ✅ **Service Layer Pattern** - Business logic isolation
- ✅ **Repository Pattern** - Data access abstraction
- ✅ **Dependency Injection** - Loose coupling
- ✅ **Observer Pattern** - Event-driven architecture
- ✅ **State Pattern** - Order status management
- ✅ **Strategy Pattern** - Payment gateway strategies
- ✅ **Factory Pattern** - Object creation

### Database Schema

**13 Tables** với relationships:

```
users ─┬─ roles (Many-to-Many via user_roles)
       ├─ carts (One-to-One)
       ├─ orders (One-to-Many)
       └─ ratings (One-to-Many)

categories ─── products (One-to-Many)

products ─┬─ product_details (One-to-One)
          ├─ inventories (One-to-One)
          ├─ cart_items (One-to-Many)
          ├─ order_items (One-to-Many)
          ├─ ratings (One-to-Many)
          └─ coupons (One-to-Many)

orders ─── order_items (One-to-Many)

carts ─── cart_items (One-to-Many)
```

---

## 🚀 Cài đặt nhanh

### Yêu cầu hệ thống

- **PHP** >= 8.2
- **Composer** >= 2.7
- **Node.js** >= 20.x
- **MySQL** >= 8.0
- **Redis** >= 7.0
- **Docker** & **Docker Compose** (optional)

### Cài đặt với Docker (Khuyến nghị)

```bash
# 1. Clone repository
git clone https://github.com/vinhhoang04cp/webshop.git
cd webshop

# 2. Copy environment file
cp .env.example .env

# 3. Install dependencies
composer install
npm install

# 4. Generate application key
php artisan key:generate

# 5. Start Docker services
docker-compose -f docker-compose.dev.yml up -d --build

# 6. Run migrations & seeders
docker-compose -f docker-compose.dev.yml exec app php artisan migrate:fresh --seed

# 7. Build frontend assets
npm run dev

# 8. Start Laravel Reverb (new terminal)
php artisan reverb:start
```

### Cài đặt không dùng Docker

```bash
# 1-4: Giống như trên

# 5. Configure .env với MySQL local
DB_HOST=127.0.0.1
DB_DATABASE=webshop
DB_USERNAME=root
DB_PASSWORD=your_password

# 6. Run migrations & seeders
php artisan migrate:fresh --seed

# 7. Build assets
npm run build

# 8. Start development server
composer dev
```

### Truy cập ứng dụng

- **Web**: http://localhost
- **API**: http://localhost/api
- **Admin**: http://localhost/dashboard
- **Mailpit**: http://localhost:8025 (email testing)
- **phpMyAdmin**: http://localhost:8080

### Tài khoản demo

```
Admin:
Email: admin@webshop.com
Password: password

Manager:
Email: manager@webshop.com
Password: password

Customer:
Email: customer@webshop.com
Password: password
```

---

## 📸 Screenshots

### Homepage
![Homepage](docs/screenshots/homepage.png)

### Product Listing
![Products](docs/screenshots/products.png)

### Shopping Cart
![Cart](docs/screenshots/cart.png)

### Admin Dashboard
![Dashboard](docs/screenshots/dashboard.png)

### Order Management
![Orders](docs/screenshots/orders.png)

---

## 📚 Tài liệu

### 📖 Documentation Hub

Tất cả tài liệu được tổ chức trong thư mục [`docs/`](docs/):

#### 🚀 Getting Started
- [GETTING_STARTED.md](docs/GETTING_STARTED.md) - Hướng dẫn cài đặt chi tiết
- [TECH_STACK.md](docs/TECH_STACK.md) - Công nghệ & versions

#### 🏗️ Architecture & Design
- [ARCHITECTURE.md](docs/ARCHITECTURE.md) - Kiến trúc hệ thống
- [DATABASE.md](docs/DATABASE.md) - Database schema
- [CODE_QUALITY_REVIEW.md](docs/CODE_QUALITY_REVIEW.md) - Code quality assessment
- [REFACTORING_EXAMPLES.md](docs/REFACTORING_EXAMPLES.md) - Refactoring guides

#### 🔐 Authentication & Security
- [AUTHENTICATION.md](docs/AUTHENTICATION.md) - Auth & RBAC
- [SOCIALITE_COMPLETE_GUIDE.md](docs/SOCIALITE_COMPLETE_GUIDE.md) - Social login
- [PASSWORD_RESET.md](PASSWORD_RESET.md) - Password reset

#### 💼 Business Logic
- [BUSINESS_LOGIC.md](docs/BUSINESS_LOGIC.md) - Business workflows
- [API_REFERENCE.md](docs/API_REFERENCE.md) - API endpoints

#### 💳 Payment Integration
- [VNPAY_COMPLETE_GUIDE.md](VNPAY_COMPLETE_GUIDE.md) - VNPay payment

#### 📱 Frontend
- [RESPONSIVE_IMPROVEMENTS.md](docs/RESPONSIVE_IMPROVEMENTS.md) - Responsive design
- [RESPONSIVE_TESTING_CHECKLIST.md](docs/RESPONSIVE_TESTING_CHECKLIST.md) - Testing guide

#### 📝 Coding Standards
- [CODING_CONVENTIONS.md](docs/CODING_CONVENTIONS.md) - Code standards
- [EMAIL_CONFIGURATION.md](docs/EMAIL_CONFIGURATION.md) - Email setup

### 🗺️ Roadmap cho từng vai trò

**Backend Developer:**
```
1. ARCHITECTURE.md → 2. DATABASE.md → 3. AUTHENTICATION.md 
→ 4. BUSINESS_LOGIC.md → 5. CODE_QUALITY_REVIEW.md
```

**Frontend Developer:**
```
1. RESPONSIVE_IMPROVEMENTS.md → 2. RESPONSIVE_TESTING_CHECKLIST.md 
→ 3. ARCHITECTURE.md → 4. CODING_CONVENTIONS.md
```

**API Integration:**
```
1. API_REFERENCE.md → 2. AUTHENTICATION.md → 3. BUSINESS_LOGIC.md
```

---

## 📡 API Documentation

### Authentication

```bash
# Register
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123!",
  "password_confirmation": "SecurePass123!"
}

# Login
POST /api/login
{
  "email": "john@example.com",
  "password": "SecurePass123!"
}

# Response
{
  "success": true,
  "token": "1|abc123...",
  "user": { ... }
}
```

### Products

```bash
# Get all products
GET /api/products?page=1&per_page=10

# Get product by ID
GET /api/products/{id}

# Create product (Admin only)
POST /api/products
Authorization: Bearer {token}
{
  "name": "Product Name",
  "price": 100000,
  "category_id": 1,
  "stock_quantity": 50
}
```

### Cart

```bash
# Add to cart
POST /api/cart/add/{product_id}
Authorization: Bearer {token}
{
  "quantity": 2
}

# Checkout
POST /api/cart/checkout
{
  "shipping_name": "John Doe",
  "shipping_phone": "0901234567",
  "shipping_address": "123 Street, City",
  "payment_method": "vnpay"
}
```

**Xem đầy đủ**: [API_REFERENCE.md](docs/API_REFERENCE.md)

---

## 🧪 Testing

### Run Tests

```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/AuthenticationTest.php

# With coverage
php artisan test --coverage

# Parallel testing
php artisan test --parallel
```

### Test Structure

```
tests/
├── Unit/              # Unit tests
│   ├── Services/
│   └── Models/
├── Feature/           # Feature tests
│   ├── Auth/
│   ├── Products/
│   ├── Cart/
│   └── Orders/
└── TestCase.php       # Base test case
```

### Code Coverage

Target: **80%+** coverage

```bash
# Generate HTML coverage report
php artisan test --coverage-html coverage/
```

---

## 🚢 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate new `APP_KEY`
- [ ] Configure production database
- [ ] Set up Redis cache
- [ ] Configure email provider (Gmail/SendGrid)
- [ ] Set up queue worker
- [ ] Configure VNPay production credentials
- [ ] Set up SSL certificate (HTTPS)
- [ ] Configure CORS policies
- [ ] Set up backups
- [ ] Configure monitoring (Sentry/Bugsnag)
- [ ] Run `composer install --optimize-autoloader --no-dev`
- [ ] Run `npm run build`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Cache config: `php artisan config:cache`
- [ ] Cache routes: `php artisan route:cache`
- [ ] Cache views: `php artisan view:cache`

### Docker Production

```bash
# Build production image
docker-compose -f docker-compose.prod.yml build

# Deploy
docker-compose -f docker-compose.prod.yml up -d

# Run migrations
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### Server Requirements

**Minimum:**
- 2 CPU cores
- 4GB RAM
- 20GB SSD storage
- Ubuntu 20.04 LTS

**Recommended:**
- 4 CPU cores
- 8GB RAM
- 50GB SSD storage
- Ubuntu 22.04 LTS

---

## 🤝 Đóng góp

### Development Workflow

1. **Fork** repository
2. **Clone** fork của bạn
3. **Create branch**: `git checkout -b feature/amazing-feature`
4. **Commit changes**: `git commit -m 'Add amazing feature'`
5. **Push**: `git push origin feature/amazing-feature`
6. **Open Pull Request**

### Code Standards

- Follow **PSR-12** coding standards
- Write **PHPDoc** for all methods
- Add **unit tests** for new features
- Run **Laravel Pint**: `./vendor/bin/pint`
- Follow **Laravel best practices**

### Commit Message Convention

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation
- `style`: Formatting
- `refactor`: Code restructuring
- `test`: Adding tests
- `chore`: Maintenance

**Example:**
```
feat(cart): add coupon validation

- Validate coupon expiry date
- Check minimum order amount
- Verify usage limit

Closes #123
```

---

## 📊 Project Status

### Current Version: 3.4.0

**Last Updated**: 06/11/2025

### Features Status

| Feature | Status | Version |
|---------|--------|---------|
| User Authentication | ✅ Complete | 1.0 |
| Social Login | ✅ Complete | 3.1 |
| Product Management | ✅ Complete | 1.0 |
| Shopping Cart | ✅ Complete | 1.0 |
| Order Management | ✅ Complete | 1.0 |
| Coupon System | ✅ Complete | 2.0 |
| VNPay Payment | ✅ Complete | 2.5 |
| Password Reset | ✅ Complete | 3.2 |
| Responsive Design | ✅ Complete | 3.4 |
| API Documentation | ✅ Complete | 3.0 |
| Admin Dashboard | ✅ Complete | 1.0 |
| Real-time Notifications | 🚧 In Progress | - |
| Mobile App | 📅 Planned | - |
| Multi-language | 📅 Planned | - |

### Roadmap

**Q4 2025:**
- ✅ Responsive design
- ✅ Code quality improvements
- 🚧 Real-time notifications
- 📅 Performance optimization

**Q1 2026:**
- 📅 Multi-language support (EN, VI)
- 📅 Advanced analytics
- 📅 Customer loyalty program
- 📅 Product reviews & ratings

**Q2 2026:**
- 📅 Mobile app (React Native)
- 📅 Advanced search (Elasticsearch)
- 📅 Recommendation engine
- 📅 Live chat support

---

## 📞 Liên hệ & Hỗ trợ

### Team

**Hoàng Quang Vinh**
- Role: Full-stack Developer & Architect
- Email: vinhhoang04cp@gmail.com
- GitHub: [@vinhhoang04cp](https://github.com/vinhhoang04cp)

### Bug Reports

Nếu phát hiện bug, vui lòng:
1. Kiểm tra [Issues](https://github.com/vinhhoang04cp/webshop/issues) existing
2. Tạo [New Issue](https://github.com/vinhhoang04cp/webshop/issues/new)
3. Cung cấp thông tin chi tiết:
   - Steps to reproduce
   - Expected behavior
   - Actual behavior
   - Screenshots (nếu có)
   - Environment (OS, PHP version, etc.)

### Feature Requests

Đề xuất tính năng mới tại [Discussions](https://github.com/vinhhoang04cp/webshop/discussions)

---

## 📄 License

This project is **proprietary software**. All rights reserved.

**Unauthorized copying, distribution, or use of this software is strictly prohibited.**

For licensing inquiries, contact: vinhhoang04cp@gmail.com

---

## 🙏 Acknowledgments

### Technologies
- [Laravel](https://laravel.com) - The PHP Framework
- [Tailwind CSS](https://tailwindcss.com) - CSS Framework
- [VNPay](https://vnpay.vn) - Payment Gateway
- [Laravel Reverb](https://reverb.laravel.com) - WebSocket Server

### Inspiration
- [Laravel Bootcamp](https://bootcamp.laravel.com)
- [Laracasts](https://laracasts.com)
- [Laravel News](https://laravel-news.com)

### Special Thanks
- Laravel Community
- All contributors
- Beta testers

---

## 📈 Statistics

```
Lines of Code: 25,000+
PHP Files: 150+
Blade Views: 80+
API Endpoints: 60+
Database Tables: 13
Migrations: 15+
Tests: 100+
Documentation Pages: 18
```

---

## 🔗 Links

- **Repository**: https://github.com/vinhhoang04cp/webshop
- **Documentation**: [docs/](docs/)
- **Issue Tracker**: https://github.com/vinhhoang04cp/webshop/issues
- **Changelog**: [CHANGELOG.md](CHANGELOG.md)
- **Contributing**: [CONTRIBUTING.md](CONTRIBUTING.md)

---

<div align="center">

**Made with ❤️ by Hoàng Quang Vinh**

⭐ **Star this repository if you found it helpful!**

[Report Bug](https://github.com/vinhhoang04cp/webshop/issues) • [Request Feature](https://github.com/vinhhoang04cp/webshop/discussions)

</div>
