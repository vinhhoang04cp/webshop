# 🏗️ System Architecture - Kiến trúc Hệ thống

> **Mục đích**: Mô tả kiến trúc tổng thể, luồng dữ liệu, và design patterns

## 📋 Mục lục
1. [Tổng quan kiến trúc](#tổng-quan-kiến-trúc)
2. [Layers Architecture](#layers-architecture)
3. [Request Flow](#request-flow)
4. [Database Architecture](#database-architecture)
5. [Authentication Architecture](#authentication-architecture)
6. [Caching Strategy](#caching-strategy)

---

## 🎯 Tổng quan kiến trúc

### Kiến trúc tổng thể

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
│  │ • CartController │              │ • ProductCtrl     │   │
│  └───────┬──────────┘              └────────┬──────────┘   │
│          │                                   │               │
│  ┌───────▼───────────────────────────────────▼──────────┐  │
│  │                  SERVICE LAYER                       │  │
│  │  • Business Logic  • Validation  • Authorization    │  │
│  └───────┬──────────────────────────────────┬──────────┘  │
│          │                                   │               │
│  ┌───────▼───────────────────────────────────▼──────────┐  │
│  │              MODEL LAYER (Eloquent ORM)              │  │
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

---

## 📚 Layers Architecture

### 1. Client Layer (Tầng Giao diện)

**Thành phần**:
- **Web Browser**: Blade templates + Tailwind CSS
- **Mobile App**: API client (JSON)
- **Third-party**: External API consumers

**Trách nhiệm**:
- Hiển thị UI/UX
- Gửi request đến server
- Xử lý response và hiển thị kết quả

---

### 2. Application Layer (Tầng Ứng dụng)

#### 2.1 Routes (Định tuyến)

**Web Routes** (`routes/web.php`):
```php
// Public routes
Route::get('/', [HomeController::class, 'index']);
Route::get('/products', [CustomerProductController::class, 'index']);

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/cart', [CustomerCartController::class, 'index']);
    Route::post('/cart/checkout', [CustomerCartController::class, 'checkout']);
});

// Admin routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('dashboard/products', ProductController::class);
});
```

**API Routes** (`routes/api.php`):
```php
// Public API
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']);

// Protected API
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', fn(Request $r) => $r->user());
    Route::apiResource('orders', OrderController::class);
});

// Admin-only API
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::apiResource('categories', CategoryController::class);
});
```

#### 2.2 Middleware (Bộ lọc Request)

**Built-in Middleware**:
- `auth:sanctum` - Token authentication
- `throttle:60,1` - Rate limiting (60 req/min)
- `cors` - Cross-Origin Resource Sharing

**Custom Middleware**:
- `AdminMiddleware` - Kiểm tra role admin
- `RolePermissionMiddleware` - Kiểm tra permissions

#### 2.3 Controllers (Điều khiển)

**Web Controllers**:
```
app/Http/Controllers/Web/
├── HomeController.php           # Trang chủ
├── AuthController.php           # Login/Register
├── CustomerProductController.php # Xem sản phẩm (customer)
├── CustomerCartController.php   # Giỏ hàng (customer)
├── ProductController.php        # Quản lý SP (admin)
├── OrderController.php          # Quản lý đơn (admin)
└── ...
```

**API Controllers**:
```
app/Http/Controllers/Api/
├── AuthController.php           # API Auth
├── ProductController.php        # Product API
├── OrderController.php          # Order API
└── ...
```

---

### 3. Service Layer (Tầng Nghiệp vụ)

**Chức năng**:
- Business logic
- Data validation
- Authorization checks
- Complex operations

**Ví dụ** (trong Controller):
```php
public function checkout(Request $request)
{
    // Validation
    $validated = $request->validate([...]);
    
    // Business logic
    DB::transaction(function () use ($request) {
        // 1. Kiểm tra tồn kho
        // 2. Tạo Order
        // 3. Trừ stock
        // 4. Xóa cart
    });
    
    // Response
    return redirect()->route('orders.success');
}
```

---

### 4. Model Layer (Tầng Dữ liệu)

**Eloquent ORM Models**:
```
app/Models/
├── User.php           # HasApiTokens, roles relationship
├── Product.php        # belongsTo Category, hasOne Inventory
├── Category.php       # hasMany Products
├── Order.php          # belongsTo User, hasMany OrderItems
├── Cart.php           # belongsTo User, hasMany CartItems
└── ...
```

**Relationships**:
```php
// User.php
public function roles() {
    return $this->belongsToMany(Role::class, 'user_roles');
}

public function cart() {
    return $this->hasOne(Cart::class);
}

// Product.php
public function category() {
    return $this->belongsTo(Category::class, 'category_id');
}

public function inventory() {
    return $this->hasOne(Inventory::class, 'product_id');
}
```

---

### 5. Data Layer (Tầng Lưu trữ)

#### 5.1 MySQL Database
- **Primary storage**: Relational data
- **Tables**: users, products, orders, etc.
- **Transactions**: ACID compliance

#### 5.2 Redis Cache
- **Sessions**: User sessions
- **Cache**: Query results, config
- **Queue**: Background jobs

#### 5.3 File Storage
- **Public**: Product images
- **Storage**: Private files

---

## 🔄 Request Flow

### Web Request Flow

```
1. User truy cập browser → http://localhost/products
   ↓
2. Web Route → CustomerProductController@index
   ↓
3. Controller:
   - Query products từ database (eager load category)
   - Apply filters, pagination
   ↓
4. Blade template render HTML
   - Loop products
   - Display với Tailwind CSS
   ↓
5. Response HTML → Browser
```

**Code Example**:
```php
// routes/web.php
Route::get('/products', [CustomerProductController::class, 'index']);

// Controller
public function index(Request $request) {
    $query = Product::with('category');
    
    if ($request->has('search')) {
        $query->where('name', 'LIKE', "%{$request->search}%");
    }
    
    $products = $query->paginate(12);
    
    return view('products.index', compact('products'));
}
```

---

### API Request Flow

```
1. Client → POST /api/products
   Headers: Authorization: Bearer {token}
   Body: {"name": "iPhone 15", ...}
   ↓
2. Middleware chain:
   - throttle:60,1 (rate limit check)
   - auth:sanctum (authenticate token)
   - admin (check role)
   ↓
3. ProductController@store:
   - Validate request
   - Create product
   - Create inventory
   ↓
4. JSON Response
   {
     "success": true,
     "data": {...}
   }
```

**Code Example**:
```php
// routes/api.php
Route::middleware(['auth:sanctum', 'admin'])
    ->apiResource('products', ProductController::class);

// Controller
public function store(Request $request) {
    $validated = $request->validate([...]);
    
    $product = Product::create($validated);
    
    Inventory::create([
        'product_id' => $product->id,
        'stock_in' => $validated['stock_quantity'],
        'current_stock' => $validated['stock_quantity'],
    ]);
    
    return response()->json([
        'success' => true,
        'data' => $product,
    ], 201);
}
```

---

## 🗄️ Database Architecture

### Entity Relationship Diagram

```
users ────────┬──── user_roles ──── roles
              │
              ├──── carts ──── cart_items ──── products
              │
              └──── orders ──── order_items ──── products

categories ──── products ────┬──── product_details
                             │
                             └──── inventory

revenue_reports (standalone)

personal_access_tokens (Laravel Sanctum)
```

### Key Relationships

| Parent | Child | Type | Description |
|--------|-------|------|-------------|
| users | roles | Many-to-Many | User có nhiều roles |
| users | cart | One-to-One | Mỗi user có 1 cart |
| users | orders | One-to-Many | User có nhiều orders |
| categories | products | One-to-Many | Category có nhiều products |
| products | inventory | One-to-One | Product có 1 inventory record |
| products | product_details | One-to-One | Product có 1 detail record |
| carts | cart_items | One-to-Many | Cart có nhiều items |
| orders | order_items | One-to-Many | Order có nhiều items |

---

## 🔐 Authentication Architecture

### Token-based Authentication Flow

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
│ personal_access_tokens   │ 4. Store hashed token
│  - tokenable_id (user)   │
│  - token (hashed)        │
└──────┬───────────────────┘
       │ 5. Return plaintext token
       ▼
┌──────────────┐
│   Client     │ 6. Store token locally
│  localStorage│    Future requests:
└──────┬───────┘    Authorization: Bearer {token}
       │
       │ 7. Subsequent requests
       ▼
┌──────────────────┐
│ auth:sanctum     │ 8. Verify token
│  Middleware      │    Load user
└──────┬───────────┘
       │ 9. User authenticated
       ▼
┌──────────────────┐
│   Controller     │ 10. Access $request->user()
└──────────────────┘
```

### Role-Based Access Control (RBAC)

```
┌─────────────┐
│    User     │
└──────┬──────┘
       │ belongsToMany
       ▼
┌──────────────┐
│  user_roles  │ (pivot table)
└──────┬───────┘
       │
       ▼
┌──────────────┐
│    Roles     │
│  - admin     │ → Full permissions
│  - manager   │ → View + Edit (no delete)
│  - customer  │ → Buy products only
└──────────────┘
```

**Permission Matrix**:

| Resource | Admin | Manager | Customer |
|----------|-------|---------|----------|
| Products | ✅ CRUD | ✅ RU | ✅ R |
| Categories | ✅ CRUD | ✅ RU | ✅ R |
| Orders | ✅ CRUD | ✅ RU | ✅ R (own) |
| Users | ✅ CRUD | ❌ | ❌ |
| Inventory | ✅ CRUD | ✅ RU | ❌ |

---

## 💾 Caching Strategy

### Multi-level Caching

```
Request
   ↓
┌──────────────────┐
│  Browser Cache   │ (Static assets)
└────────┬─────────┘
         │ Miss
         ▼
┌──────────────────┐
│  Redis Cache     │ (Query results, sessions)
└────────┬─────────┘
         │ Miss
         ▼
┌──────────────────┐
│  MySQL Database  │ (Source of truth)
└──────────────────┘
```

### Cache Keys

```php
// Product list cache
Cache::remember('products:all', 3600, function () {
    return Product::with('category')->get();
});

// Category tree cache
Cache::remember('categories:tree', 3600, function () {
    return Category::tree();
});

// User-specific cart cache
Cache::remember("cart:user:{$userId}", 600, function () use ($userId) {
    return Cart::with('items.product')
        ->where('user_id', $userId)
        ->first();
});
```

### Cache Invalidation

```php
// Khi tạo/sửa/xóa product
Cache::forget('products:all');
Cache::tags(['products'])->flush();

// Khi tạo/sửa category
Cache::forget('categories:tree');
```

---

## 🔀 Transaction Flow

### Checkout Transaction (Critical)

```php
DB::transaction(function () use ($cart, $request) {
    // 1. Validate stock
    foreach ($cart->items as $item) {
        if ($item->product->stock_quantity < $item->quantity) {
            throw new Exception('Insufficient stock');
        }
    }
    
    // 2. Create order
    $order = Order::create([...]);
    
    // 3. Create order items
    foreach ($cart->items as $item) {
        OrderItem::create([...]);
    }
    
    // 4. ⚠️ Decrease stock IMMEDIATELY
    foreach ($cart->items as $item) {
        $item->product->decrement('stock_quantity', $item->quantity);
        
        // Update inventory
        $inventory = $item->product->inventory;
        $inventory->increment('stock_out', $item->quantity);
        $inventory->decrement('current_stock', $item->quantity);
    }
    
    // 5. Clear cart
    $cart->items()->delete();
    
    // All or nothing - if any step fails, rollback all
});
```

---

## 🎯 Design Patterns sử dụng

### 1. **MVC Pattern**
- Model: Eloquent ORM
- View: Blade templates
- Controller: Handle requests

### 2. **Repository Pattern** (via Eloquent)
```php
// Eloquent as repository
class ProductRepository extends Product {
    public function findWithCategory($id) {
        return $this->with('category')->findOrFail($id);
    }
}
```

### 3. **Service Container (Dependency Injection)**
```php
class OrderController {
    public function __construct(
        private OrderService $orderService
    ) {}
}
```

### 4. **Observer Pattern**
```php
// Product Observer
class ProductObserver {
    public function created(Product $product) {
        Cache::forget('products:all');
    }
}
```

### 5. **Factory Pattern**
```php
// Database seeders
Product::factory()->count(50)->create();
```

---

## 📊 Performance Considerations

### Database Optimization
- ✅ Indexes on foreign keys
- ✅ Eager loading (`with()`) to avoid N+1
- ✅ Pagination for large datasets
- ✅ Query caching with Redis

### Caching Strategy
- ✅ Redis for sessions & cache
- ✅ Config/route/view caching in production
- ✅ Opcache enabled for PHP

### Asset Optimization
- ✅ Vite for bundling & minification
- ✅ Code splitting
- ✅ Asset hashing (cache busting)

---

## 📚 Tài liệu liên quan

- **[TECH_STACK.md](./TECH_STACK.md)** - Danh sách công nghệ
- **[DATABASE.md](./DATABASE.md)** - Chi tiết schema
- **[AUTHENTICATION.md](./AUTHENTICATION.md)** - Auth system
- **[BUSINESS_LOGIC.md](./BUSINESS_LOGIC.md)** - Business rules

---

**Cập nhật lần cuối**: 19/10/2025  
**Version**: 2.0  
**Author**: Hoàng Quang Vinh
