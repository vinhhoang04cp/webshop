# 📚 WebShop Documentation

> **Tài liệu hệ thống WebShop E-commerce Platform**  
> Laravel 12 | PHP 8.4 | MySQL 8.0 | Redis | Tailwind CSS 4.0

---

## 🎯 Tổng quan

WebShop là một nền tảng thương mại điện tử (e-commerce) được xây dựng trên **Laravel 12**, cung cấp đầy đủ chức năng quản lý sản phẩm, đơn hàng, giỏ hàng, và hệ thống phân quyền người dùng (RBAC).

### ✨ Tính năng chính
- 🛍️ **Quản lý sản phẩm**: CRUD sản phẩm, danh mục, chi tiết sản phẩm
- 🛒 **Giỏ hàng & Đặt hàng**: Quy trình checkout hoàn chỉnh với quản lý tồn kho
- 👥 **Phân quyền**: RBAC với 4 roles (Admin, Manager, Customer, Guest)
- 🔐 **API Authentication**: Laravel Sanctum token-based authentication
- 📊 **Dashboard**: Giao diện quản trị cho Admin/Manager
- 📦 **Inventory Management**: Quản lý tồn kho với điều chỉnh thủ công

### 🏗️ Kiến trúc
- **Backend**: Laravel 12 (PHP 8.4)
- **Frontend**: Blade Templates + Tailwind CSS 4.0 + Vite 7.0
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis Alpine
- **Development**: Docker + Laravel Sail

---

## 📖 Cấu trúc tài liệu

Tài liệu được tổ chức thành 7 file chính, mỗi file phục vụ một mục đích riêng:

### 1️⃣ [GETTING_STARTED.md](./GETTING_STARTED.md) 🚀
**Bắt đầu từ đây nếu bạn mới setup dự án!**

- ✅ Yêu cầu hệ thống
- 📥 Hướng dẫn cài đặt từng bước (7 bước)
- 🎬 Khởi động dự án (Composer script hoặc thủ công)
- 🌐 URLs sau khi cài đặt
- 🔧 Troubleshooting

**Đọc khi**:
- Lần đầu setup dự án
- Gặp lỗi cài đặt
- Cần khởi động lại môi trường development

---

### 2️⃣ [TECH_STACK.md](./TECH_STACK.md) 🛠️
**Reference nhanh về công nghệ sử dụng**

- 🎯 Core Stack (PHP, Laravel, Node.js)
- ⚙️ Backend Technologies (Packages & versions)
- 🎨 Frontend Technologies (Vite, Tailwind CSS)
- 💾 Database & Cache (MySQL, Redis)
- 🐳 Development Environment (Docker services)
- 🧪 Testing Tools
- 📊 Version Matrix

**Đọc khi**:
- Cần biết version của các packages
- Thêm dependencies mới
- Kiểm tra tương thích công nghệ

---

### 3️⃣ [ARCHITECTURE.md](./ARCHITECTURE.md) 🏛️
**Kiến trúc hệ thống & design patterns**

- 📐 Layers Architecture (Presentation, Business, Data)
- 🔄 Request Flow (Web & API)
- 🗄️ Database ERD (Entity Relationship Diagram)
- 🔐 Authentication Architecture
- ⚡ Caching Strategy
- 🎨 Design Patterns (MVC, Repository, Observer, etc.)

**Đọc khi**:
- Cần hiểu cấu trúc tổng thể
- Thiết kế feature mới
- Refactor code
- Onboarding developer mới

---

### 4️⃣ [AUTHENTICATION.md](./AUTHENTICATION.md) 🔐
**Hệ thống xác thực & phân quyền**

- 🛡️ Laravel Sanctum setup
- 🔄 Authentication Flow (Register → Login → Logout)
- 🛡️ Middleware System (auth:sanctum, throttle, custom middleware)
- 👥 Role-Based Access Control (RBAC)
- 💡 Code Examples
- 🔒 Security Best Practices
- 🧪 Testing Authentication

**Đọc khi**:
- Làm việc với authentication/authorization
- Thêm role/permission mới
- Debug vấn đề đăng nhập
- Implement protected routes

---

### 5️⃣ [API_REFERENCE.md](./API_REFERENCE.md) 📡
**Quick reference cho tất cả API endpoints**

- 🔐 Authentication endpoints
- 📦 Products endpoints
- 📁 Categories endpoints
- 🎫 Coupon endpoints ✨
- 🛒 Cart endpoints
- 📋 Orders endpoints
- 👥 Users endpoints
- 📊 Inventory endpoints
- 📊 Status Codes
- 🔴 Error Response Format
- 🧪 cURL Examples

**Đọc khi**:
- Develop API client
- Test API endpoints
- Integrate với mobile app
- Debug API responses

---

### 6️⃣ [BUSINESS_LOGIC.md](./BUSINESS_LOGIC.md) 💼
**Quy trình nghiệp vụ & use cases**

- 👥 User Roles & Permissions
- 🔄 Complete Order Flow (5 steps chi tiết)
- 🎫 Coupon System & Discount Rules ✨
- ⚠️ Critical Business Rules
- 📖 Core Use Cases (Browse, Add to Cart, Apply Coupon, Checkout, etc.)
- 📊 Inventory Management
- 🔄 Status Transitions

**⚠️ ĐẶC BIỆT QUAN TRỌNG**: 
- **Stock deduction policy**: Stock trừ NGAY khi checkout (không restore khi cancel)
- **Order status workflow**: pending → processing → shipped → delivered
- **Price locking**: Giá được lock khi tạo order

**Đọc khi**:
- Cần hiểu workflow đặt hàng
- Làm việc với inventory
- Debug vấn đề tồn kho
- Implement business rules mới

---

### 7️⃣ [DATABASE.md](./DATABASE.md) 🗄️
**Database schema & relationships**

- 📊 ERD Diagram
- 📋 Table Structures (15 tables bao gồm coupons) ✨
- 🔗 Relationships (One-to-Many, Many-to-Many)
- 🔑 Indexes & Constraints
- 📝 Migration Commands

**Đọc khi**:
- Thiết kế queries
- Tạo migration mới
- Hiểu relationships giữa tables
- Optimize database performance

---

### 8️⃣ [CODING_CONVENTIONS.md](./CODING_CONVENTIONS.md) 📝
**Code standards & best practices**

- 📏 PSR-12 Compliance
- 📦 Laravel Naming Conventions
- 🎨 Blade Template Guidelines
- 🧪 Testing Standards
- 📚 Documentation Standards

**Đọc khi**:
- Viết code mới
- Code review
- Setup coding standards cho team

---

### 9️⃣ [SOCIALITE_COMPLETE_GUIDE.md](./docs/SOCIALITE_COMPLETE_GUIDE.md) 🔐
**Hướng dẫn đầy đủ về Laravel Socialite - Social Login**

- 📋 Tổng quan tính năng đăng nhập qua Google/Facebook/GitHub
- 🛠️ Các bước triển khai kỹ thuật chi tiết
- 🔧 Cấu hình Google OAuth từng bước (có screenshots)
- 📱 Cấu hình Facebook OAuth
- 🐙 Cấu hình GitHub OAuth
- 🧪 Testing & Debugging
- 🔒 Security & Production checklist
- ⚠️ Troubleshooting

**Đọc khi**:
- Cần setup đăng nhập qua Social Networks
- Debug OAuth integration
- Deploy social login lên production
- Thêm provider mới (Twitter, LinkedIn, etc.)

---

## 🚀 Quick Start

### 1. Cài đặt lần đầu
```bash
# Clone repository
git clone https://github.com/vinhhoang04cp/webshop.git
cd webshop

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
./vendor/bin/sail artisan key:generate

# Start Docker services
./vendor/bin/sail up -d

# Run migrations & seeders
./vendor/bin/sail artisan migrate:fresh --seed

# Build frontend
npm run dev
```

### 2. Truy cập ứng dụng
- **Web Application**: http://localhost
- **phpMyAdmin**: http://localhost:8080
- **Mailpit (Email Testing)**: http://localhost:8025
- **Vite Dev Server**: http://localhost:5173

### 3. Tài khoản mẫu (sau khi seed)
```
Admin:
- Email: admin@webshop.com
- Password: password

Manager:
- Email: manager@webshop.com
- Password: password

Customer:
- Email: customer@webshop.com
- Password: password
```

---

## 🗺️ Roadmap đọc tài liệu

### 👶 Cho người mới bắt đầu
```
1. GETTING_STARTED.md    → Setup dự án
2. TECH_STACK.md         → Hiểu công nghệ sử dụng
3. ARCHITECTURE.md       → Hiểu cấu trúc hệ thống
4. DATABASE.md           → Hiểu database schema
5. AUTHENTICATION.md     → Hiểu hệ thống auth
6. BUSINESS_LOGIC.md     → Hiểu quy trình nghiệp vụ
```

### 💻 Cho Backend Developer
```
1. ARCHITECTURE.md       → Hiểu layers & patterns
2. DATABASE.md           → Database schema & relationships
3. AUTHENTICATION.md     → Auth & authorization
4. BUSINESS_LOGIC.md     → Business rules & use cases
5. API_REFERENCE.md      → API endpoints reference
```

### 📱 Cho API Integration
```
1. API_REFERENCE.md      → All endpoints
2. AUTHENTICATION.md     → How to authenticate
3. BUSINESS_LOGIC.md     → Understand workflows
```

### 👨‍💼 Cho Business Analyst
```
1. BUSINESS_LOGIC.md     → Complete workflows & use cases
2. DATABASE.md           → Data structure
3. API_REFERENCE.md      → Feature capabilities
```

---

## 📊 Thống kê dự án

### Database
- **13 tables** chính
- **20+ relationships** (One-to-Many, Many-to-Many)
- **15+ migrations**

### API Endpoints
- **60+ endpoints** (Authentication, Products, Orders, Cart, etc.)
- **RESTful** design
- **Token-based** authentication

### Controllers
- **15+ controllers** (API + Web)
- **40+ routes** (Web routes)
- **60+ routes** (API routes)

### Models
- **12 Eloquent models**
- **Relationships**: hasMany, belongsTo, belongsToMany
- **Soft deletes** trên Product

---

## 🔧 Development Workflow

### Làm việc với features mới

1. **Đọc tài liệu liên quan**
   - ARCHITECTURE.md: Hiểu cấu trúc
   - BUSINESS_LOGIC.md: Hiểu requirements
   - DATABASE.md: Thiết kế schema nếu cần

2. **Tạo migration & model** (nếu cần)
   ```bash
   sail artisan make:model ProductVariant -m
   ```

3. **Tạo controller**
   ```bash
   sail artisan make:controller Api/ProductVariantController --api
   ```

4. **Thêm routes** (routes/api.php hoặc routes/web.php)

5. **Viết tests**
   ```bash
   sail artisan make:test ProductVariantTest
   sail artisan test
   ```

6. **Update documentation** (cập nhật file liên quan)

---

## 🛠️ Công cụ hữu ích

### CLI Commands
```bash
# Development server với hot reload
composer dev

# Run tests
sail artisan test

# Code style fix
./vendor/bin/pint

# View logs real-time
sail artisan pail

# Clear caches
sail artisan cache:clear
sail artisan config:clear
sail artisan route:clear
sail artisan view:clear

# Database
sail artisan migrate
sail artisan db:seed
sail artisan migrate:fresh --seed
```

### Docker Services
```bash
# Start services
sail up -d

# Stop services
sail down

# View logs
sail logs

# Access MySQL
sail mysql

# Access container shell
sail shell
```

---

## 📞 Hỗ trợ & Đóng góp

### Báo lỗi trong tài liệu
Nếu phát hiện lỗi, thông tin lỗi thời, hoặc thiếu sót trong tài liệu:
1. Tạo issue trên GitHub
2. Ghi rõ file nào và nội dung cần sửa
3. Pull request (nếu có thể)

### Cập nhật tài liệu
Khi thêm feature mới hoặc thay đổi code:
- ✅ Cập nhật API_REFERENCE.md nếu thêm/sửa endpoint
- ✅ Cập nhật BUSINESS_LOGIC.md nếu thay đổi workflow
- ✅ Cập nhật DATABASE.md nếu thêm/sửa table
- ✅ Cập nhật ARCHITECTURE.md nếu thay đổi structure

---

## 📝 Version History

| Version | Date | Changes |
|---------|------|---------|
| **3.1** | 23/10/2025 | Thêm Laravel Socialite (Google/Facebook/GitHub OAuth), tài liệu SOCIALITE_COMPLETE_GUIDE.md |
| **3.0** | 19/10/2025 | Tổ chức lại toàn bộ docs, loại bỏ redundancy |
| **2.0** | 19/10/2025 | Thêm API endpoints, controllers, routes chi tiết |
| **1.0** | Initial | Tài liệu ban đầu |

---

## 📚 External Resources

### Official Documentation
- [Laravel 12](https://laravel.com/docs/12.x)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Tailwind CSS 4.0](https://tailwindcss.com/docs)
- [Vite](https://vite.dev)
- [MySQL 8.0](https://dev.mysql.com/doc/refman/8.0/en/)

### Learning Resources
- [Laravel Bootcamp](https://bootcamp.laravel.com)
- [Laracasts](https://laracasts.com)
- [Laravel News](https://laravel-news.com)

---

## 📄 License

This project is proprietary software. All rights reserved.

---

**Cập nhật lần cuối**: 19/10/2025  
**Version**: 3.0  
**Maintainer**: Hoàng Quang Vinh  
**Repository**: [vinhhoang04cp/webshop](https://github.com/vinhhoang04cp/webshop)

---

## 🗂️ Danh sách file tài liệu

```
docs/
├── README.md                      # 📚 File này - Tổng quan toàn bộ docs
├── GETTING_STARTED.md             # 🚀 Hướng dẫn cài đặt & khởi động
├── TECH_STACK.md                  # 🛠️ Technologies & versions
├── ARCHITECTURE.md                # 🏛️ System architecture & design
├── AUTHENTICATION.md              # 🔐 Auth & authorization
├── API_REFERENCE.md               # 📡 API endpoints reference
├── BUSINESS_LOGIC.md              # 💼 Business workflows & use cases
├── DATABASE.md                    # 🗄️ Database schema
├── CODING_CONVENTIONS.md          # 📝 Code standards
└── SOCIALITE_COMPLETE_GUIDE.md    # 🔐 Laravel Socialite - Social Login

Current Version: 3.1 (23/10/2025) ✨
- Thêm tích hợp Laravel Socialite (Google/Facebook/GitHub OAuth)
- Hướng dẫn chi tiết từng bước setup OAuth providers
- Thêm hệ thống coupon/mã giảm giá hoàn chỉnh
- Cập nhật tất cả tài liệu phản ánh tính năng mới
- API endpoints cho quản lý coupon

Legacy files (sẽ xóa trong future updates):
├── Api-Document.md                # ⚠️ Merged into AUTHENTICATION + API_REFERENCE
├── MIDDLEWARE_AUTHENTICATION.md   # ⚠️ Merged into AUTHENTICATION
├── ENVIRONMENT_SETUP.md           # ⚠️ Merged into GETTING_STARTED
├── ORDER_CHECKOUT_PROCESS.md      # ⚠️ Merged into BUSINESS_LOGIC
└── USE_CASES.md                   # ⚠️ Merged into BUSINESS_LOGIC
```

---

**Cập nhật lần cuối**: 23/10/2025  
**Version**: 3.1  
**Author**: Hoàng Quang Vinh

---

**💡 Tip**: Bookmark file README.md này để dễ dàng điều hướng đến các tài liệu khác!

## 🆕 Tính năng mới (v3.1)

### Laravel Socialite - Social Login
- ✅ Đăng nhập/đăng ký qua Google OAuth 2.0
- ✅ Đăng nhập/đăng ký qua Facebook Login
- ✅ Đăng nhập/đăng ký qua GitHub OAuth
- ✅ Tự động liên kết tài khoản nếu email đã tồn tại
- ✅ UI đẹp với nút social login responsive
- ✅ Tài liệu chi tiết từng bước cấu hình

👉 **Xem hướng dẫn**: [SOCIALITE_COMPLETE_GUIDE.md](./docs/SOCIALITE_COMPLETE_GUIDE.md)
