# TÀI LIỆU TECH STACK - DỰ ÁN WEBSHOP

## 📋 Mục lục
1. [Tổng quan hệ thống](#1-tổng-quan-hệ-thống)
2. [Backend Stack](#2-backend-stack)
3. [Frontend Stack](#3-frontend-stack)
4. [Database & Caching](#4-database--caching)
5. [DevOps & Deployment](#5-devops--deployment)
6. [Testing & Quality](#6-testing--quality)
7. [API & Authentication](#7-api--authentication)
8. [Development Tools](#8-development-tools)
9. [Kiến trúc hệ thống](#9-kiến-trúc-hệ-thống)
10. [Cấu trúc dự án](#10-cấu-trúc-dự-án)

---

## 1. Tổng quan hệ thống

### 1.1 Mô tả dự án
**Webshop** là một nền tảng thương mại điện tử được xây dựng trên Laravel framework, cung cấp:
- Hệ thống quản lý sản phẩm, đơn hàng, kho hàng
- Phân quyền người dùng theo vai trò (RBAC - Role-Based Access Control)
- API RESTful cho ứng dụng di động và SPA
- Giao diện web quản trị và khách hàng

### 1.2 Đặc điểm kỹ thuật
- **Kiến trúc**: Monolithic với API layer riêng biệt
- **Pattern**: MVC (Model-View-Controller)
- **API Style**: RESTful API
- **Authentication**: Token-based (Laravel Sanctum)
- **Authorization**: Role-Based Access Control (RBAC)

---

## 2. Backend Stack

### 2.1 Core Framework

#### Laravel 12.x
- **Phiên bản**: Laravel ^12.0 (Latest stable release)
- **PHP Version**: PHP ^8.2
- **License**: MIT License
- **Mô tả**: Full-stack PHP framework với tính năng hoàn chỉnh

**Tính năng chính sử dụng**:
- ✅ Eloquent ORM - Object Relational Mapping
- ✅ Blade Template Engine - Template rendering
- ✅ Migration & Seeding - Database version control
- ✅ Artisan CLI - Command line interface
- ✅ Middleware - HTTP request filtering
- ✅ Service Container - Dependency injection
- ✅ Queue System - Background job processing
- ✅ Event & Listeners - Event-driven architecture
- ✅ Validation - Data validation
- ✅ File Storage - File management system

### 2.2 Authentication & Authorization

#### Laravel Sanctum ^4.0
**Mục đích**: API Token Authentication cho SPA và Mobile Apps

**Tính năng**:
- Token-based authentication
- API token management
- SPA authentication
- Mobile app authentication
- Token abilities (permissions)

**Cách sử dụng trong dự án**:
```php
// User Model
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}

// Tạo token khi login
$token = $user->createToken('api-token')->plainTextToken;

// Protect routes
Route::middleware('auth:sanctum')->group(function () {
    // Protected routes
});
```

### 2.3 Development Tools

#### Laravel Tinker ^2.10.1
**Mục đích**: REPL (Read-Eval-Print Loop) để tương tác với Laravel application

**Sử dụng**:
```bash
php artisan tinker
>>> User::all();
>>> App\Models\Product::find(1);
```

#### Laravel Pail ^1.2.2
**Mục đích**: Xem logs real-time trong terminal

**Sử dụng**:
```bash
php artisan pail
```

#### Laravel Pint ^1.25
**Mục đích**: Code style fixer cho PHP (dựa trên PHP-CS-Fixer)

**Sử dụng**:
```bash
./vendor/bin/pint
```

#### Laravel Sail ^1.46
**Mục đích**: Docker development environment cho Laravel

**Tính năng**:
- Pre-configured Docker containers
- PHP 8.4, MySQL, Redis, Mailpit
- Easy local development setup

### 2.4 Testing Framework

#### PHPUnit ^11.5.3
**Mục đích**: Unit testing và feature testing

**Tính năng**:
- Unit tests
- Feature tests
- Database testing
- HTTP tests
- Mocking & stubbing

#### Mockery ^1.6
**Mục đích**: Mocking framework cho unit tests

#### Faker PHP ^1.23
**Mục đích**: Generate fake data cho testing và seeding

### 2.5 Package Dependencies

#### Collision ^8.6 (nunomaduro/collision)
**Mục đích**: Beautiful error reporting cho Artisan commands

---

## 3. Frontend Stack

### 3.1 Build Tools

#### Vite ^7.0.4
**Mục đích**: Modern frontend build tool (thay thế Webpack Mix)

**Đặc điểm**:
- ⚡ Extremely fast HMR (Hot Module Replacement)
- 📦 Optimized production builds
- 🔧 Plugin ecosystem
- 🎯 ES modules support

**Cấu hình trong dự án** (`vite.config.js`):
```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
```

#### Laravel Vite Plugin ^2.0.0
**Mục đích**: Integration Vite với Laravel

**Tính năng**:
- Auto-refresh on file changes
- Asset bundling
- CSS/JS processing

### 3.2 CSS Framework

#### Tailwind CSS ^4.0.0
**Mục đích**: Utility-first CSS framework

**Đặc điểm**:
- 🎨 Utility-first approach
- 📱 Responsive design
- 🎯 Just-In-Time compiler
- 🔧 Highly customizable
- 📦 Small bundle size

#### @tailwindcss/vite ^4.0.0
**Mục đích**: Vite plugin cho Tailwind CSS 4.0

**Ưu điểm**:
- Faster compilation
- Better performance
- Native Vite integration

### 3.3 Template Engine

#### Blade Template Engine
**Mục đích**: Laravel's native templating engine

**Tính năng sử dụng**:
- Template inheritance
- Component-based structure
- Directives (@if, @foreach, @auth, etc.)
- Layouts & sections

**Ví dụ trong dự án**:
```php
// layouts/app.blade.php
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WebShop Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @yield('content')
</body>
</html>
```

### 3.4 JavaScript Libraries

#### Axios ^1.11.0
**Mục đích**: Promise-based HTTP client

**Sử dụng**:
- API calls từ frontend
- AJAX requests
- HTTP interceptors

### 3.5 UI Components

#### Bootstrap 5.3.0
**Mục đích**: CSS framework cho admin interface

**Sử dụng trong dự án**:
- Admin dashboard layouts
- Forms & inputs
- Modal dialogs
- Grid system
- Responsive components

#### Font Awesome 6.4.0
**Mục đích**: Icon library

**Sử dụng**:
- UI icons
- Navigation icons
- Action buttons

---

## 4. Database & Caching

### 4.1 Primary Database

#### MySQL 8.0
**Mục đích**: Relational database management system

**Đặc điểm**:
- ACID compliance
- Transaction support
- Foreign key constraints
- Full-text search
- JSON data type support

**Cấu hình** (config/database.php):
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'webshop'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

**Database Schema**:

**Bảng chính**:
1. **users** - Người dùng hệ thống
   - id, name, email, password, phone, address
   - timestamps (created_at, updated_at)

2. **roles** - Vai trò người dùng
   - id, name, description
   - Roles: Admin, Manager, Customer

3. **user_roles** - Liên kết User - Role (Many-to-Many)
   - id, user_id, role_id

4. **categories** - Danh mục sản phẩm
   - id, name, description, parent_id
   - Hỗ trợ danh mục con (self-referencing)

5. **products** - Sản phẩm
   - id, category_id, name, description, price, image_url
   - deleted_at (soft delete)

6. **product_details** - Chi tiết sản phẩm
   - id, product_id, size, color, material

7. **inventory** - Tồn kho
   - id, product_id, quantity, warehouse_location

8. **carts** - Giỏ hàng
   - id, user_id, total_amount

9. **cart_items** - Chi tiết giỏ hàng
   - id, cart_id, product_id, quantity, price

10. **orders** - Đơn hàng
    - id, user_id, total_amount, status, payment_method
    - shipping_name, shipping_phone, shipping_address, shipping_note

11. **order_items** - Chi tiết đơn hàng
    - id, order_id, product_id, quantity, price

12. **revenue_reports** - Báo cáo doanh thu
    - id, report_date, total_revenue, total_orders

13. **personal_access_tokens** - Laravel Sanctum tokens
    - id, tokenable_type, tokenable_id, name, token, abilities

### 4.2 Caching Layer

#### Redis (Alpine)
**Mục đích**: In-memory data structure store

**Use cases trong dự án**:
- Session storage
- Cache storage
- Queue driver
- Rate limiting

**Cấu hình**:
```yaml
# compose.yaml
redis:
    image: 'redis:alpine'
    ports:
        - '${FORWARD_REDIS_PORT:-6379}:6379'
    volumes:
        - 'sail-redis:/data'
```

**Sử dụng trong Laravel**:
```php
// Cache
Cache::put('key', 'value', 600);
$value = Cache::get('key');

// Session
'SESSION_DRIVER' => 'redis'

// Queue
'QUEUE_CONNECTION' => 'redis'
```

### 4.3 Database Tools

#### Eloquent ORM
**Mục đích**: Object-Relational Mapping

**Models trong dự án**:
```
app/Models/
├── Cart.php
├── CartItem.php
├── Category.php
├── Inventory.php
├── Order.php
├── OrderItem.php
├── Product.php
├── ProductDetail.php
├── RevenueReport.php
├── Role.php
├── User.php
└── UserRole.php
```

**Relationships**:
```php
// User Model
public function roles()
{
    return $this->belongsToMany(Role::class, 'user_roles');
}

public function cart()
{
    return $this->hasOne(Cart::class);
}

public function orders()
{
    return $this->hasMany(Order::class);
}

// Product Model
public function category()
{
    return $this->belongsTo(Category::class);
}

public function details()
{
    return $this->hasOne(ProductDetail::class);
}

public function inventory()
{
    return $this->hasOne(Inventory::class);
}
```

#### Migrations
**Mục đích**: Database version control

**Migration files**:
- Schema definition
- Foreign key constraints
- Indexes
- Default values

**Commands**:
```bash
php artisan migrate           # Run migrations
php artisan migrate:fresh     # Drop all tables and re-run
php artisan migrate:rollback  # Rollback last batch
php artisan migrate:status    # Show migration status
```

#### Seeders & Factories
**Mục đích**: Database seeding và test data generation

**Factories**:
```
database/factories/
├── CategoryFactory.php
├── OrderFactory.php
├── OrderItemFactory.php
├── ProductFactory.php
└── UserFactory.php
```

**Commands**:
```bash
php artisan db:seed                 # Run seeders
php artisan db:seed --class=UserSeeder
php artisan migrate:fresh --seed    # Fresh migration + seed
```

---

## 5. DevOps & Deployment

### 5.1 Containerization

#### Docker & Docker Compose
**Mục đích**: Container orchestration và development environment

**Services** (compose.yaml):

1. **laravel.test** (App Container)
   - Image: sail-8.4/app (PHP 8.4)
   - Ports: 80 (HTTP), 5173 (Vite)
   - Features: XDebug support
   
2. **mysql** (Database)
   - Image: mysql/mysql-server:8.0
   - Port: 3306
   - Volume: sail-mysql (persistent storage)
   - Auto-create testing database
   
3. **redis** (Cache & Queue)
   - Image: redis:alpine
   - Port: 6379
   - Volume: sail-redis
   
4. **mailpit** (Mail Testing)
   - Image: axllent/mailpit:latest
   - Ports: 1025 (SMTP), 8025 (Web UI)
   - Purpose: Email testing trong development

**Docker Commands**:
```bash
# Start all services
./vendor/bin/sail up -d

# Stop all services
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan migrate

# Access container shell
./vendor/bin/sail shell
```

### 5.2 Process Management

#### Concurrently ^9.0.1
**Mục đích**: Run multiple commands simultaneously

**Sử dụng trong dự án** (composer.json):
```json
"dev": [
    "npx concurrently -c \"#93c5fd,#c4b5fd,#fb7185,#fdba74\" 
    \"php artisan serve\" 
    \"php artisan queue:listen --tries=1\" 
    \"php artisan pail --timeout=0\" 
    \"npm run dev\" 
    --names=server,queue,logs,vite 
    --kill-others"
]
```

**Chạy development server**:
```bash
composer dev
```

**Processes được chạy đồng thời**:
1. 🌐 **server** - Laravel development server (port 8000)
2. 📬 **queue** - Queue worker (background jobs)
3. 📝 **logs** - Real-time log viewer (Pail)
4. ⚡ **vite** - Vite dev server with HMR (port 5173)

### 5.3 Environment Configuration

#### .env File
**Mục đích**: Environment-specific configuration

**Key configurations**:
```env
# Application
APP_NAME=WebShop
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=webshop
DB_USERNAME=sail
DB_PASSWORD=password

# Cache & Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (Mailpit)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

---

## 6. Testing & Quality

### 6.1 Testing Framework

#### PHPUnit ^11.5.3
**Cấu hình** (phpunit.xml):
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

**Test Structure**:
```
tests/
├── TestCase.php          # Base test class
├── Unit/                 # Unit tests
└── Feature/              # Feature tests
```

**Running tests**:
```bash
php artisan test                    # Run all tests
php artisan test --filter UserTest  # Run specific test
composer test                       # Run via composer script
```

### 6.2 Code Quality

#### Laravel Pint ^1.25
**Mục đích**: PHP code style fixer

**Coding standards**:
- PSR-12 compliant
- Laravel-specific conventions

**Usage**:
```bash
./vendor/bin/pint              # Fix all files
./vendor/bin/pint --test       # Check without fixing
./vendor/bin/pint app/Models   # Fix specific directory
```

### 6.3 Development Practices

**Coding Conventions** (CODING_CONVENTIONS.md):
- Tuân thủ PSR-12
- Laravel best practices
- Naming conventions
- Documentation standards

---

## 7. API & Authentication

### 7.1 API Architecture

#### RESTful API Design
**Base URL**: `/api/`

**API Routes** (routes/api.php):

**Public Endpoints**:
```php
POST   /api/login           # Authentication
POST   /api/register        # User registration
GET    /api/products        # List products (with throttle)
GET    /api/products/{id}   # Product details
GET    /api/categories      # List categories
GET    /api/categories/{id} # Category details
```

**Protected Endpoints** (require authentication):
```php
GET    /api/user            # Current user info

# Cart Management
GET    /api/cart            # View cart
POST   /api/cart/add        # Add to cart
PUT    /api/cart/update/{id} # Update cart item
DELETE /api/cart/remove/{id} # Remove from cart

# Order Management
GET    /api/orders          # List orders
POST   /api/orders          # Create order
GET    /api/orders/{id}     # Order details
PUT    /api/orders/{id}     # Update order status

# Admin Only
POST   /api/products        # Create product
PUT    /api/products/{id}   # Update product
DELETE /api/products/{id}   # Delete product
GET    /api/users           # List users
```

### 7.2 Authentication Flow

#### Laravel Sanctum Token Authentication

**Login Flow**:
```
1. Client gửi POST /api/login
   {
     "email": "user@example.com",
     "password": "password123"
   }
   ↓
2. AuthController validate credentials
   ↓
3. Tạo token mới
   $token = $user->createToken('api-token')->plainTextToken;
   ↓
4. Trả về response
   {
     "user": {...},
     "token": "1|abc123xyz..."
   }
   ↓
5. Client lưu token (localStorage/cookies)
   ↓
6. Subsequent requests include header:
   Authorization: Bearer 1|abc123xyz...
```

**Middleware Protection**:
```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    // Protected routes
});
```

### 7.3 Authorization

#### Role-Based Access Control (RBAC)

**Roles**:
1. **Admin** - Full system access
2. **Manager** - Manage products, orders, inventory
3. **Customer** - Browse, purchase, manage own orders

**Middleware** (`app/Http/Middleware/AdminMiddleware.php`):
```php
public function handle(Request $request, Closure $next)
{
    if (!$request->user() || !$request->user()->hasRole('Admin')) {
        abort(403, 'Unauthorized');
    }
    return $next($request);
}
```

**Usage**:
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin only routes
});
```

### 7.4 API Rate Limiting

**Throttle Middleware**:
```php
'throttle:60,1'  // 60 requests per minute
```

**Applied to**:
- All public product endpoints
- All public category endpoints
- All authenticated endpoints

---

## 8. Development Tools

### 8.1 CLI Tools

#### Artisan CLI
**Mục đích**: Laravel's command-line interface

**Commonly used commands**:
```bash
# Development server
php artisan serve

# Database
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed

# Cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Queue
php artisan queue:listen
php artisan queue:work

# Make commands
php artisan make:model Product
php artisan make:controller ProductController
php artisan make:migration create_products_table
php artisan make:seeder ProductSeeder
php artisan make:factory ProductFactory
php artisan make:request ProductRequest
php artisan make:resource ProductResource
php artisan make:middleware AdminMiddleware

# Logs
php artisan pail

# Testing
php artisan test
```

### 8.2 Composer Scripts

**Defined in composer.json**:
```json
{
  "scripts": {
    "dev": "Run all dev services concurrently",
    "test": "Run test suite",
    "post-autoload-dump": "Package discovery",
    "post-update-cmd": "Publish assets"
  }
}
```

**Usage**:
```bash
composer dev      # Start development environment
composer test     # Run tests
```

### 8.3 NPM Scripts

**Defined in package.json**:
```json
{
  "scripts": {
    "dev": "vite",              // Start Vite dev server
    "build": "vite build"       // Build for production
  }
}
```

**Usage**:
```bash
npm run dev      # Development mode with HMR
npm run build    # Production build
```

---

## 9. Kiến trúc hệ thống

### 9.1 Tổng quan kiến trúc

```
┌─────────────────────────────────────────────────────────────┐
│                         CLIENT LAYER                         │
├──────────────────┬──────────────────┬──────────────────────┤
│   Web Browser    │   Mobile App     │   Third-party API    │
│   (Blade Views)  │   (API Client)   │   Consumer           │
└────────┬─────────┴────────┬─────────┴──────────┬───────────┘
         │                  │                     │
         │ HTTP/HTTPS       │ HTTP/HTTPS         │ HTTP/HTTPS
         │                  │ + Bearer Token     │ + API Token
         │                  │                     │
┌────────▼──────────────────▼─────────────────────▼───────────┐
│                      APPLICATION LAYER                       │
│  ┌────────────────┐              ┌─────────────────────┐   │
│  │  Web Routes    │              │     API Routes      │   │
│  │  (routes/web)  │              │   (routes/api.php)  │   │
│  └───────┬────────┘              └──────────┬──────────┘   │
│          │                                   │               │
│  ┌───────▼───────────────────────────────────▼──────────┐  │
│  │              MIDDLEWARE LAYER                        │  │
│  │  • auth:sanctum  • admin  • throttle  • cors        │  │
│  └───────┬───────────────────────────────────┬──────────┘  │
│          │                                   │               │
│  ┌───────▼──────────┐              ┌────────▼──────────┐   │
│  │ Web Controllers  │              │ API Controllers   │   │
│  │ • HomeController │              │ • AuthController  │   │
│  │ • AuthController │              │ • ProductCtrl     │   │
│  │ • CartController │              │ • OrderController │   │
│  └───────┬──────────┘              └────────┬──────────┘   │
│          │                                   │               │
│  ┌───────▼───────────────────────────────────▼──────────┐  │
│  │                  SERVICE LAYER                       │  │
│  │  • Business Logic  • Validation  • Authorization    │  │
│  └───────┬──────────────────────────────────┬──────────┘  │
│          │                                   │               │
│  ┌───────▼───────────────────────────────────▼──────────┐  │
│  │                   MODEL LAYER (Eloquent ORM)         │  │
│  │  User • Product • Order • Cart • Category • etc     │  │
│  └───────┬──────────────────────────────────┬──────────┘  │
└──────────┼──────────────────────────────────┼──────────────┘
           │                                   │
┌──────────▼───────────────────────────────────▼──────────────┐
│                      DATA LAYER                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │    MySQL     │  │    Redis     │  │  File Storage│     │
│  │  (Database)  │  │ (Cache/Queue)│  │   (Images)   │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
└──────────────────────────────────────────────────────────────┘
```

### 9.2 Request Flow

#### Web Request Flow:
```
1. User accesses browser → http://localhost
   ↓
2. Web Route (routes/web.php) → HomeController@index
   ↓
3. Controller retrieves data from Models
   ↓
4. Blade template renders HTML (resources/views/home.blade.php)
   ↓
5. Response sent to browser
```

#### API Request Flow:
```
1. Client sends API request → POST /api/products
   Headers: Authorization: Bearer {token}
   ↓
2. API Route (routes/api.php) matches endpoint
   ↓
3. Middleware chain:
   - throttle:60,1 (rate limiting)
   - auth:sanctum (authentication)
   - admin (authorization)
   ↓
4. ProductController@store
   - Validate request data
   - Create product via Model
   - Save to database
   ↓
5. JSON response returned
   {
     "success": true,
     "data": {...}
   }
```

### 9.3 Database Architecture

**Relationships Diagram**:
```
users ────────┬──── user_roles ──── roles
              │
              ├──── carts ──── cart_items ──── products
              │
              └──── orders ──── order_items ──── products

categories ──── products ────┬──── product_details
                             │
                             └──── inventory

revenue_reports (standalone analytics table)

personal_access_tokens (Laravel Sanctum)
```

### 9.4 Authentication Architecture

```
┌──────────────┐
│   Client     │
└──────┬───────┘
       │ 1. POST /api/login
       │    {email, password}
       ▼
┌──────────────────┐
│ AuthController   │
│  - Validate      │ 2. Check credentials
│  - Authenticate  │    against users table
└──────┬───────────┘
       │ 3. Create token
       ▼
┌──────────────────────────┐
│ personal_access_tokens   │ 4. Store token
│  - tokenable_id (user)   │
│  - token (hashed)        │
│  - abilities             │
└──────┬───────────────────┘
       │ 5. Return plain token
       ▼
┌──────────────┐
│   Client     │ 6. Store token
│  localStorage│    Set header for future requests
└──────┬───────┘
       │ 7. Subsequent requests
       │    Authorization: Bearer {token}
       ▼
┌──────────────────┐
│ auth:sanctum     │ 8. Verify token
│  Middleware      │    Load user
└──────┬───────────┘
       │ 9. User authenticated
       ▼
┌──────────────────┐
│   Controller     │ 10. Process request
│   (Protected)    │     with user context
└──────────────────┘
```

---

## 10. Cấu trúc dự án

### 10.1 Directory Structure

```
webshop/
│
├── app/                          # Application core
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/              # API Controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CartController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   └── ProductController.php
│   │   │   └── Web/              # Web Controllers
│   │   │       ├── AuthController.php
│   │   │       ├── HomeController.php
│   │   │       └── CustomerProductController.php
│   │   │
│   │   ├── Middleware/           # HTTP Middleware
│   │   │   └── AdminMiddleware.php
│   │   │
│   │   ├── Requests/             # Form Requests (Validation)
│   │   └── Resources/            # API Resources (Response formatting)
│   │
│   ├── Models/                   # Eloquent Models
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Order.php
│   │   ├── Cart.php
│   │   └── ...
│   │
│   ├── Providers/                # Service Providers
│   │   ├── AppServiceProvider.php
│   │   └── RouteServiceProvider.php
│   │
│   └── View/
│       └── Composers/            # View Composers
│
├── bootstrap/                    # Bootstrap files
│   ├── app.php                   # Application bootstrap
│   ├── providers.php             # Registered providers
│   └── cache/                    # Bootstrap cache
│
├── config/                       # Configuration files
│   ├── app.php                   # App configuration
│   ├── database.php              # Database configuration
│   ├── auth.php                  # Authentication config
│   ├── sanctum.php               # Sanctum config
│   ├── cache.php                 # Cache configuration
│   ├── queue.php                 # Queue configuration
│   └── ...
│
├── database/                     # Database files
│   ├── migrations/               # Database migrations
│   │   ├── 2025_09_27_063149_create_products_table.php
│   │   ├── 2025_09_27_063149_create_orders_table.php
│   │   └── ...
│   │
│   ├── seeders/                  # Database seeders
│   │   └── DatabaseSeeder.php
│   │
│   └── factories/                # Model factories
│       ├── UserFactory.php
│       ├── ProductFactory.php
│       └── ...
│
├── public/                       # Public accessible files
│   ├── index.php                 # Entry point
│   └── robots.txt
│
├── resources/                    # Frontend resources
│   ├── css/
│   │   └── app.css               # Main CSS file
│   │
│   ├── js/
│   │   └── app.js                # Main JavaScript file
│   │
│   └── views/                    # Blade templates
│       ├── layouts/
│       │   └── app.blade.php     # Master layout
│       ├── auth/                 # Authentication views
│       ├── products/             # Product views
│       ├── cart/                 # Cart views
│       └── dashboard/            # Dashboard views
│
├── routes/                       # Route definitions
│   ├── web.php                   # Web routes
│   ├── api.php                   # API routes
│   └── console.php               # Console routes (artisan)
│
├── storage/                      # Storage files
│   ├── app/                      # Application storage
│   ├── framework/                # Framework storage
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/                     # Log files
│       └── laravel.log
│
├── tests/                        # Test files
│   ├── Unit/                     # Unit tests
│   ├── Feature/                  # Feature tests
│   └── TestCase.php              # Base test case
│
├── vendor/                       # Composer dependencies
│
├── .env                          # Environment variables
├── .env.example                  # Environment template
├── artisan                       # Artisan CLI
├── composer.json                 # PHP dependencies
├── composer.lock                 # PHP dependency lock
├── package.json                  # Node dependencies
├── package-lock.json             # Node dependency lock
├── vite.config.js                # Vite configuration
├── phpunit.xml                   # PHPUnit configuration
├── compose.yaml                  # Docker Compose config
│
├── README.md                     # Project README
├── Api-Document.md               # API Documentation
├── CODING_CONVENTIONS.md         # Coding standards
├── ORDER_DISPLAY_UPDATE.md       # Feature documentation
└── TECH_STACK.md                 # This file
```

### 10.2 Key Files Explained

#### Configuration Files

**composer.json**
- Defines PHP dependencies
- Contains scripts for dev, test, etc.
- Autoload configuration

**package.json**
- Defines Node.js dependencies
- NPM scripts (dev, build)

**vite.config.js**
- Vite build configuration
- Laravel plugin setup
- Tailwind CSS integration

**compose.yaml**
- Docker services definition
- Development environment setup

**.env**
- Environment-specific configuration
- Database credentials
- API keys
- App settings

#### Core Application Files

**routes/api.php**
- RESTful API endpoint definitions
- Middleware assignment
- Route grouping

**routes/web.php**
- Web interface routes
- Authentication routes
- Customer-facing pages

**app/Http/Kernel.php** (or bootstrap/app.php in Laravel 11+)
- Middleware registration
- Middleware groups
- Route middleware

**app/Providers/AppServiceProvider.php**
- Service container bindings
- Application bootstrapping

### 10.3 Naming Conventions

**Controllers**:
- PascalCase
- Suffix with "Controller"
- Examples: `ProductController`, `OrderController`

**Models**:
- PascalCase
- Singular noun
- Examples: `Product`, `Order`, `User`

**Database Tables**:
- snake_case
- Plural noun
- Examples: `products`, `orders`, `users`

**Migrations**:
- snake_case
- Prefix with timestamp
- Descriptive action
- Example: `2025_09_27_063149_create_products_table.php`

**Routes**:
- kebab-case
- RESTful conventions
- Examples: `/api/products`, `/api/cart-items`

**Blade Views**:
- kebab-case
- `.blade.php` extension
- Examples: `product-list.blade.php`, `cart.blade.php`

---

## 11. Performance & Optimization

### 11.1 Caching Strategy

**Cache Drivers**:
- Redis (primary) - Fast in-memory cache
- File cache (fallback)

**What to cache**:
```php
// Product list
Cache::remember('products', 3600, function () {
    return Product::all();
});

// Category tree
Cache::remember('categories', 3600, function () {
    return Category::tree();
});

// Config cache
php artisan config:cache

// Route cache
php artisan route:cache

// View cache
php artisan view:cache
```

### 11.2 Database Optimization

**Indexes**:
- Primary keys (automatic)
- Foreign keys
- Frequently queried columns

**Query Optimization**:
```php
// Eager loading (N+1 problem solution)
$products = Product::with('category', 'inventory')->get();

// Select specific columns
$products = Product::select('id', 'name', 'price')->get();

// Chunk large datasets
Product::chunk(100, function ($products) {
    foreach ($products as $product) {
        // Process
    }
});
```

### 11.3 Asset Optimization

**Vite Production Build**:
```bash
npm run build
```

**Features**:
- Code splitting
- Tree shaking
- Minification
- Asset hashing (cache busting)

### 11.4 Queue System

**Purpose**: Offload time-consuming tasks

**Use cases**:
- Email sending
- Report generation
- Image processing
- Data export

**Queue Driver**: Redis

**Commands**:
```bash
php artisan queue:listen    # Development
php artisan queue:work      # Production
```

---

## 12. Security Features

### 12.1 Authentication Security

**Laravel Sanctum**:
- Token-based authentication
- CSRF protection for SPA
- Token expiration support
- Token abilities/scopes

**Password Security**:
```php
// Hashing
Hash::make($password)

// Verification
Hash::check($password, $hashedPassword)
```

### 12.2 Authorization

**Role-Based Access Control**:
```php
// Check role
$user->hasRole('Admin')

// Middleware protection
Route::middleware('admin')->group(...)
```

**Policy-based authorization**:
```php
// Can user update product?
$this->authorize('update', $product);
```

### 12.3 Input Validation

**Form Requests**:
```php
public function rules()
{
    return [
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'price' => 'required|numeric|min:0',
    ];
}
```

**SQL Injection Prevention**:
- Eloquent ORM (parameterized queries)
- Query builder with bindings

### 12.4 Rate Limiting

**API Throttling**:
```php
'throttle:60,1'  // 60 requests per minute
```

**Applied to**:
- Public API endpoints
- Login attempts
- Registration

### 12.5 CORS (Cross-Origin Resource Sharing)

**Configuration** (config/cors.php):
```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['*'],
'allowed_headers' => ['*'],
```

### 12.6 CSRF Protection

**Web Routes**: Automatic CSRF token validation

**API Routes**: Token-based (no CSRF needed)

---

## 13. Monitoring & Logging

### 13.1 Logging

**Log Channels**:
- Single file log
- Daily rotation
- Stack (multiple channels)

**Log Location**: `storage/logs/laravel.log`

**Usage**:
```php
Log::info('User logged in', ['user_id' => $user->id]);
Log::error('Payment failed', ['order_id' => $order->id]);
Log::warning('Low inventory', ['product_id' => $product->id]);
```

### 13.2 Real-time Log Viewing

**Laravel Pail**:
```bash
php artisan pail
```

**Features**:
- Real-time log streaming
- Color-coded output
- Filter by level

### 13.3 Error Handling

**Production**: Error pages (resources/views/errors/)

**Development**: 
- Whoops error page
- Detailed stack traces
- Debug bar (optional)

---

## 14. Deployment Considerations

### 14.1 Production Checklist

```bash
# 1. Set environment to production
APP_ENV=production
APP_DEBUG=false

# 2. Generate app key
php artisan key:generate

# 3. Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Run migrations
php artisan migrate --force

# 5. Build assets
npm run build

# 6. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Enable opcache (php.ini)
opcache.enable=1
opcache.memory_consumption=256
```

### 14.2 Server Requirements

**Minimum Requirements**:
- PHP >= 8.2
- MySQL >= 8.0
- Redis >= 5.0
- Composer
- Node.js >= 18.x

**PHP Extensions**:
- BCMath
- Ctype
- cURL
- DOM
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

### 14.3 Web Server Configuration

**Nginx Example**:
```nginx
server {
    listen 80;
    server_name webshop.com;
    root /var/www/webshop/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 15. Future Enhancements

### 15.1 Planned Features

**Technical Improvements**:
- [ ] Implement API versioning (v1, v2)
- [ ] Add GraphQL endpoint
- [ ] WebSocket support for real-time updates
- [ ] Elasticsearch for advanced search
- [ ] CDN integration for static assets
- [ ] Kubernetes deployment configuration

**Performance**:
- [ ] Database query optimization
- [ ] Implement full-page caching
- [ ] Image lazy loading
- [ ] Service worker for offline support

**Security**:
- [ ] Two-factor authentication (2FA)
- [ ] OAuth2 integration
- [ ] Rate limiting per user
- [ ] Security headers (CSP, HSTS)

### 15.2 Scalability Considerations

**Horizontal Scaling**:
- Load balancer setup
- Session management (Redis-based)
- Shared file storage (S3, MinIO)

**Database Scaling**:
- Read replicas
- Database sharding
- Connection pooling

**Caching Strategy**:
- Multi-level caching
- Cache warming
- Cache invalidation strategy

---

## 16. Development Workflow

### 16.1 Getting Started

```bash
# 1. Clone repository
git clone https://github.com/vinhhoang04cp/webshop.git
cd webshop

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Start Docker services
./vendor/bin/sail up -d

# 5. Run migrations & seeders
./vendor/bin/sail artisan migrate:fresh --seed

# 6. Start development server
composer dev
```

### 16.2 Development Commands

```bash
# Start all services
composer dev

# Run tests
composer test
php artisan test

# Code formatting
./vendor/bin/pint

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Database operations
php artisan migrate
php artisan db:seed
php artisan migrate:fresh --seed

# Generate code
php artisan make:model Product -mfsc
# m = migration, f = factory, s = seeder, c = controller
```

### 16.3 Git Workflow

**Branching Strategy**:
- `main` - Production-ready code
- `develop` - Development branch
- `feature/*` - Feature branches
- `bugfix/*` - Bug fix branches

**Commit Convention**:
```
feat: Add product search feature
fix: Resolve cart calculation bug
docs: Update API documentation
refactor: Optimize product query
test: Add order controller tests
```

---

## 17. Tài liệu tham khảo

### 17.1 Official Documentation

- **Laravel**: https://laravel.com/docs/12.x
- **Laravel Sanctum**: https://laravel.com/docs/12.x/sanctum
- **Tailwind CSS**: https://tailwindcss.com/docs
- **Vite**: https://vite.dev/guide/
- **MySQL**: https://dev.mysql.com/doc/
- **Redis**: https://redis.io/docs/
- **PHPUnit**: https://phpunit.de/documentation.html

### 17.2 Community Resources

- **Laravel News**: https://laravel-news.com/
- **Laracasts**: https://laracasts.com/
- **Laravel Daily**: https://laraveldaily.com/

### 17.3 Internal Documentation

- **API Documentation**: `Api-Document.md`
- **Coding Conventions**: `CODING_CONVENTIONS.md`
- **Order Display Update**: `ORDER_DISPLAY_UPDATE.md`
- **README**: `README.md`

---

## 18. Support & Contact

### 18.1 Project Information

- **Repository**: https://github.com/vinhhoang04cp/webshop
- **Owner**: vinhhoang04cp
- **Current Branch**: main
- **License**: MIT

### 18.2 Getting Help

**Issues & Bugs**:
1. Check existing documentation
2. Review error logs (`storage/logs/laravel.log`)
3. Use `php artisan pail` for real-time debugging
4. Create GitHub issue với detailed description

**Development Questions**:
1. Check Laravel documentation
2. Search Laravel community forums
3. Stack Overflow (tag: laravel)

---

## 19. Changelog

### Version History

**Current Version**: 1.0.0

**Recent Updates**:
- ✅ Initial project setup với Laravel 12
- ✅ Database schema design & migrations
- ✅ Authentication system với Sanctum
- ✅ RBAC implementation
- ✅ RESTful API development
- ✅ Web interface với Blade templates
- ✅ Docker development environment
- ✅ Comprehensive documentation

---

## 20. Kết luận

### 20.1 Tech Stack Summary

**Backend**:
- Laravel 12.x (PHP 8.2)
- MySQL 8.0
- Redis (Cache & Queue)
- Laravel Sanctum (Authentication)

**Frontend**:
- Blade Template Engine
- Tailwind CSS 4.0
- Vite 7.0
- Bootstrap 5.3 (Admin UI)
- Axios (HTTP Client)

**DevOps**:
- Docker & Docker Compose
- Laravel Sail
- Concurrently (Process management)

**Testing**:
- PHPUnit 11.5
- Laravel Pint (Code style)

**Development Tools**:
- Artisan CLI
- Composer
- NPM
- Mailpit (Email testing)

### 20.2 Key Strengths

✅ **Modern Stack**: Latest versions của Laravel, PHP, Tailwind CSS
✅ **Scalable Architecture**: Monolithic với clear separation of concerns
✅ **Security**: Token-based auth, RBAC, input validation, rate limiting
✅ **Developer Experience**: Hot reload, real-time logs, comprehensive CLI tools
✅ **Performance**: Redis caching, query optimization, asset bundling
✅ **Documentation**: Extensive docs cho API, code conventions, tech stack
✅ **Testing**: PHPUnit integration với Laravel testing utilities
✅ **Containerization**: Docker-based development environment

### 20.3 Best Practices Followed

- ✅ RESTful API design principles
- ✅ MVC architecture pattern
- ✅ Repository pattern (via Eloquent)
- ✅ Dependency injection
- ✅ Middleware-based request filtering
- ✅ Database migrations for version control
- ✅ Environment-based configuration
- ✅ Comprehensive error handling
- ✅ Security best practices
- ✅ Code style consistency (PSR-12)

---

**Document Version**: 1.0.0  
**Last Updated**: 17/10/2025  
**Author**: Hoàng Quang Vinh  
**Project**: WebShop E-commerce Platform

---

*Tài liệu này được tạo để cung cấp cái nhìn toàn diện về tech stack và kiến trúc của dự án WebShop. Vui lòng cập nhật khi có thay đổi về công nghệ hoặc architecture.*
