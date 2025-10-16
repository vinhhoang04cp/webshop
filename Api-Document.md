# Tài liệu Hệ thống Authentication, Middleware và Phân quyền

## 📋 Mục lục
1. [Tổng quan](#tổng-quan)
2. [Kiến trúc Authentication](#kiến-trúc-authentication)
3. [Laravel Sanctum](#laravel-sanctum)
4. [Hệ thống Phân quyền (Authorization)](#hệ-thống-phân-quyền)
5. [Middleware](#middleware)
6. [Luồng hoạt động API](#luồng-hoạt-động-api)
7. [Các Route API và Phân quyền](#các-route-api-và-phân-quyền)
8. [Code Examples](#code-examples)

---

## 🎯 Tổng quan

Dự án webshop sử dụng **Laravel Sanctum** để xác thực API và hệ thống **Role-Based Access Control (RBAC)** để phân quyền người dùng.

### Các thành phần chính:
- **Authentication**: Laravel Sanctum (Token-based)
- **Authorization**: Role-Based Access Control (RBAC)
- **Middleware**: `auth:sanctum`, `admin`, `throttle`
- **Roles**: Admin, Manager, Customer

---

## 🔐 Kiến trúc Authentication

### 1. Laravel Sanctum

Laravel Sanctum cung cấp hệ thống authentication đơn giản cho SPA (Single Page Application) và mobile app thông qua API tokens.

#### Cài đặt trong dự án:

**File: `app/Models/User.php`**
```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

**Migration: `database/migrations/2025_10_05_141621_create_personal_access_tokens_table.php`**
```php
Schema::create('personal_access_tokens', function (Blueprint $table) {
    $table->id();
    $table->morphs('tokenable');           // Liên kết đa hình với User
    $table->text('name');                  // Tên token (vd: 'api-token')
    $table->string('token', 64)->unique(); // Token hash (64 ký tự)
    $table->text('abilities')->nullable(); // Quyền của token
    $table->timestamp('last_used_at')->nullable();
    $table->timestamp('expires_at')->nullable()->index();
    $table->timestamps();
});
```

### 2. AuthController - Xử lý Authentication

#### 📍 File: `app/Http/Controllers/Api/AuthController.php`

### 🔹 **Register (Đăng ký)**

**Endpoint**: `POST /api/register`

**Luồng hoạt động**:
```
1. Client gửi request với dữ liệu user
   ↓
2. Validation kiểm tra dữ liệu đầu vào
   ↓
3. Hash password bằng Hash::make()
   ↓
4. Tạo user mới trong database
   ↓
5. Tạo token mới cho user
   ↓
6. Trả về user info + token
```

**Request Body**:
```json
{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "0123456789",
  "address": "123 ABC Street"
}
```

**Validation Rules**:
- `name`: required, string, max 255 ký tự
- `email`: required, string, email format, max 255 ký tự, unique trong bảng users
- `password`: required, string, min 8 ký tự, cần confirmation
- `phone`: nullable, string, max 20 ký tự
- `address`: nullable, string, max 500 ký tự

**Response Success (201)**:
```json
{
  "status": true,
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com",
    "phone": "0123456789",
    "address": "123 ABC Street"
  },
  "token": "1|abcdef123456..."
}
```

**Code Implementation**:
```php
public function register(Request $request)
{
    // Validate dữ liệu
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
    ]);

    // Tạo user mới
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password), // Hash password
        'phone' => $request->phone,
        'address' => $request->address,
    ]);

    // Tạo token
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'Registration successful',
        'user' => $user,
        'token' => $token,
    ], 201);
}
```

---

### 🔹 **Login (Đăng nhập)**

**Endpoint**: `POST /api/login`

**Luồng hoạt động**:
```
1. Client gửi email + password
   ↓
2. Validation kiểm tra format
   ↓
3. Tìm user theo email trong database
   ↓
4. Kiểm tra password với Hash::check()
   ↓
5. Xóa tất cả token cũ của user
   ↓
6. Tạo token mới
   ↓
7. Trả về user info + token
```

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response Success (200)**:
```json
{
  "status": true,
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com"
  },
  "token": "2|xyz789..."
}
```

**Response Error (422)**:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "The provided credentials are incorrect."
    ]
  }
}
```

**Code Implementation**:
```php
public function login(Request $request)
{
    // Validate input
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Tìm user
    $user = User::where('email', $request->email)->first();

    // Kiểm tra user và password
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    // Xóa token cũ
    $user->tokens()->delete();

    // Tạo token mới
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'status' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token,
    ], 200);
}
```

---

### 🔹 **Logout (Đăng xuất)**

**Endpoint**: `POST /api/logout`

**Middleware**: `auth:sanctum`

**Luồng hoạt động**:
```
1. Client gửi request với token trong header
   ↓
2. Middleware auth:sanctum xác thực token
   ↓
3. Lấy token hiện tại đang sử dụng
   ↓
4. Xóa token khỏi database
   ↓
5. Trả về thông báo thành công
```

**Request Headers**:
```
Authorization: Bearer 2|xyz789...
```

**Response Success (200)**:
```json
{
  "status": true,
  "message": "Logout successful"
}
```

**Code Implementation**:
```php
public function logout(Request $request)
{
    // Xóa token hiện tại
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'status' => true,
        'message' => 'Logout successful',
    ], 200);
}
```

---

## 👥 Hệ thống Phân quyền

### 1. Cấu trúc Database

#### Bảng `roles`
```sql
CREATE TABLE roles (
    role_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    role_name VARCHAR(255) UNIQUE NOT NULL,
    role_display_name VARCHAR(255),
    role_created_at TIMESTAMP,
    role_updated_at TIMESTAMP
);
```

**Dữ liệu mẫu**:
| role_id | role_name | role_display_name |
|---------|-----------|-------------------|
| 1       | admin     | Administrator     |
| 2       | manager   | Manager           |
| 3       | customer  | Customer          |

#### Bảng `user_roles` (Many-to-Many)
```sql
CREATE TABLE user_roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    role_id BIGINT NOT NULL,
    assigned_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (role_id) REFERENCES roles(role_id),
    UNIQUE(user_id, role_id)
);
```

### 2. Model Relationships

**File: `app/Models/User.php`**
```php
class User extends Authenticatable
{
    // Many-to-Many relationship với Role
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,      // Related model
            'user_roles',     // Pivot table
            'user_id',        // Foreign key trong pivot table
            'role_id'         // Related key trong pivot table
        );
    }

    // Kiểm tra user có role cụ thể không
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('role_name', $roleName)->exists();
    }

    // Kiểm tra user có phải admin không
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
}
```

**File: `app/Models/Role.php`**
```php
class Role extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'role_id';
    
    const CREATED_AT = 'role_created_at';
    const UPDATED_AT = 'role_updated_at';

    protected $fillable = [
        'role_name',
        'role_display_name',
    ];

    // Many-to-Many relationship với User
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_roles',
            'role_id',
            'user_id'
        );
    }
}
```

### 3. Gán Role cho User (Seeder)

**File: `database/seeders/UserRoleSeeder.php`**

**Luồng hoạt động**:
```
1. Lấy tất cả users và roles từ database
   ↓
2. Lặp qua từng user
   ↓
3. Kiểm tra email pattern:
   - Nếu chứa "admin" → gán role admin
   - Nếu chứa "manager" → gán role manager
   - Ngược lại → gán role customer
   ↓
4. Tạo record trong bảng user_roles
```

**Code**:
```php
public function run(): void
{
    $users = User::all();
    $roles = Role::all();
    
    foreach ($users as $user) {
        $roleAssignment = [];
        
        if (strpos($user->email, 'admin') !== false) {
            // Email có 'admin' → gán role admin
            $adminRole = $roles->where('role_name', 'admin')->first();
            $roleAssignment[] = $adminRole->role_id;
            
        } elseif (strpos($user->email, 'manager') !== false) {
            // Email có 'manager' → gán role manager
            $managerRole = $roles->where('role_name', 'manager')->first();
            $roleAssignment[] = $managerRole->role_id;
            
        } else {
            // User thông thường → gán role customer
            $customerRole = $roles->where('role_name', 'customer')->first();
            $roleAssignment[] = $customerRole->role_id;
        }
        
        // Tạo user-role assignment
        foreach ($roleAssignment as $roleId) {
            UserRole::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ],
                [
                    'assigned_at' => now(),
                ]
            );
        }
    }
}
```

---

## 🛡️ Middleware

### 1. Built-in Middleware

#### 🔹 `auth:sanctum`

**Chức năng**: Xác thực người dùng qua Laravel Sanctum token

**Cách hoạt động**:
```
1. Lấy token từ header Authorization: Bearer {token}
   ↓
2. Tìm token trong bảng personal_access_tokens
   ↓
3. Kiểm tra token có hợp lệ không (chưa expire, chưa bị xóa)
   ↓
4. Load user từ tokenable_id
   ↓
5. Gắn user vào request ($request->user())
   ↓
6. Cho phép request tiếp tục
```

**Sử dụng trong route**:
```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user(); // User đã được authenticate
    });
});
```

---

#### 🔹 `throttle:60,1`

**Chức năng**: Giới hạn số lượng request (Rate Limiting)

**Cú pháp**: `throttle:{max_attempts},{decay_minutes}`
- `60`: Số request tối đa
- `1`: Trong 1 phút

**Ví dụ**:
```php
// Giới hạn 60 request/phút
Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
});
```

**Response khi vượt giới hạn (429)**:
```json
{
  "message": "Too Many Attempts."
}
```

**Headers trong response**:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 59
Retry-After: 60
```

---

### 2. Custom Middleware

#### 🔹 `admin` - EnsureUserIsAdmin

**File**: `app/Http/Middleware/EnsureUserIsAdmin.php`

**Chức năng**: Kiểm tra user có quyền admin không

**Luồng hoạt động**:
```
1. Lấy user từ request (đã được auth:sanctum authenticate)
   ↓
2. Kiểm tra user tồn tại
   ↓
3. Kiểm tra user có role 'admin' bằng hasRole('admin')
   ↓
4a. Nếu CÓ quyền admin → Cho phép request tiếp tục
4b. Nếu KHÔNG có quyền → Trả về lỗi 403 Forbidden
```

**Code Implementation**:
```php
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra user và role admin
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. Admin role required.',
            ], 403);
        }
        
        // User có quyền admin, cho phép tiếp tục
        return $next($request);
    }
}
```

**Đăng ký middleware alias**:

**File**: `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ]);
})
```

**Sử dụng trong route**:
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Chỉ admin mới truy cập được
    Route::post('/categories', [CategoryController::class, 'store']);
});
```

---

## 🔄 Luồng hoạt động API

### 1. Public Endpoints (Không cần authentication)

#### Xem danh sách sản phẩm

```
Client Request
   ↓
GET /api/products
   ↓
Middleware: throttle:60,1 (Kiểm tra rate limit)
   ↓
ProductController@index
   ↓
Lấy dữ liệu từ database
   ↓
Return JSON response
```

**Code route**:
```php
Route::prefix('products')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
});
```

---

### 2. Authenticated Endpoints (Cần đăng nhập)

#### Xem thông tin user hiện tại

```
Client Request với token
   ↓
GET /api/user
Headers: Authorization: Bearer {token}
   ↓
Middleware: auth:sanctum
  ├─> Kiểm tra token trong database
  ├─> Tìm user từ tokenable_id
  └─> Gắn user vào $request
   ↓
Middleware: throttle:60,1
   ↓
Return $request->user()
```

**Code route**:
```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
```

**Request Example**:
```bash
curl -X GET http://localhost/api/user \
  -H "Authorization: Bearer 1|abcdef123456..." \
  -H "Accept: application/json"
```

**Response**:
```json
{
  "id": 1,
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "0123456789",
  "address": "123 ABC Street"
}
```

---

### 3. Admin-Only Endpoints

#### Tạo category mới (Chỉ admin)

```
Client Request với token
   ↓
POST /api/categories
Headers: Authorization: Bearer {token}
   ↓
Middleware: auth:sanctum
  ├─> Xác thực token
  └─> Load user vào request
   ↓
Middleware: throttle:60,1
   ↓
Middleware: admin (EnsureUserIsAdmin)
  ├─> Kiểm tra $request->user() tồn tại?
  ├─> Kiểm tra $request->user()->hasRole('admin')
  ├─> CÓ role admin? → Tiếp tục
  └─> KHÔNG có? → Return 403 Forbidden
   ↓
CategoryController@store
  ├─> Validate dữ liệu
  ├─> Tạo category mới
  └─> Return response
```

**Code route**:
```php
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    
    Route::prefix('categories')->middleware('admin')->group(function () {
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });
    
});
```

**Request Example (Admin user)**:
```bash
curl -X POST http://localhost/api/categories \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "description": "Electronic devices"
  }'
```

**Response Success (201)**:
```json
{
  "status": true,
  "message": "Category created successfully",
  "data": {
    "id": 1,
    "name": "Electronics",
    "description": "Electronic devices"
  }
}
```

**Request Example (Non-admin user)**:
```bash
curl -X POST http://localhost/api/categories \
  -H "Authorization: Bearer {customer_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Electronics",
    "description": "Electronic devices"
  }'
```

**Response Error (403)**:
```json
{
  "status": false,
  "message": "Access denied. Admin role required."
}
```

---

### 4. Ownership-Based Endpoints

#### Xem orders (User xem của mình, Admin xem tất cả)

```
Client Request
   ↓
GET /api/orders
Headers: Authorization: Bearer {token}
   ↓
Middleware: auth:sanctum → Load user
   ↓
Middleware: throttle:60,1
   ↓
OrderController@index
  ├─> Kiểm tra $request->user()->isAdmin()
  ├─> ADMIN? → Lấy tất cả orders
  └─> USER? → Lấy orders của user đó
       └─> Query: where('user_id', $request->user()->id)
   ↓
Return orders
```

**Code Implementation**:
```php
public function index(Request $request)
{
    $query = Order::query();
    
    // User thường chỉ xem order của mình
    if (!$request->user()->isAdmin()) {
        $query->where('user_id', $request->user()->id);
    } else {
        // Admin xem tất cả, có thể apply filters
        $this->applyFilters($query, $request);
    }
    
    $orders = $query->paginate(10);
    return new OrderCollection($orders);
}
```

---

## 📚 Các Route API và Phân quyền

### Bảng tổng hợp Routes

| Method | Endpoint | Middleware | Role Required | Mô tả |
|--------|----------|------------|---------------|-------|
| **Authentication** |
| POST | `/api/register` | throttle:60,1 | Public | Đăng ký tài khoản |
| POST | `/api/login` | throttle:60,1 | Public | Đăng nhập |
| POST | `/api/logout` | auth:sanctum | Authenticated | Đăng xuất |
| GET | `/api/user` | auth:sanctum | Authenticated | Thông tin user |
| **Categories** |
| GET | `/api/categories` | throttle:60,1 | Public | Danh sách categories |
| GET | `/api/categories/{id}` | throttle:60,1 | Public | Chi tiết category |
| POST | `/api/categories` | auth:sanctum, admin | Admin | Tạo category |
| PUT | `/api/categories/{id}` | auth:sanctum, admin | Admin | Cập nhật category |
| DELETE | `/api/categories/{id}` | auth:sanctum, admin | Admin | Xóa category |
| **Products** |
| GET | `/api/products` | throttle:60,1 | Public | Danh sách sản phẩm |
| GET | `/api/products/{id}` | throttle:60,1 | Public | Chi tiết sản phẩm |
| POST | `/api/products` | auth:sanctum, admin | Admin | Tạo sản phẩm |
| PUT | `/api/products/{id}` | auth:sanctum, admin | Admin | Cập nhật sản phẩm |
| DELETE | `/api/products/{id}` | auth:sanctum, admin | Admin | Xóa sản phẩm |
| **Product Details** |
| GET | `/api/product-details` | auth:sanctum, admin | Admin | Danh sách chi tiết SP (thông số kỹ thuật điện thoại) |
| POST | `/api/product-details` | auth:sanctum, admin | Admin | Tạo chi tiết SP (màu, RAM, chip, camera...) |
| GET | `/api/product-details/{id}` | auth:sanctum, admin | Admin | Xem chi tiết SP |
| PUT/PATCH | `/api/product-details/{id}` | auth:sanctum, admin | Admin | Cập nhật chi tiết SP |
| DELETE | `/api/product-details/{id}` | auth:sanctum, admin | Admin | Xóa chi tiết SP |
| **Orders** |
| GET | `/api/orders` | auth:sanctum | User/Admin | Danh sách orders (*) |
| POST | `/api/orders` | auth:sanctum | Authenticated | Tạo order |
| GET | `/api/orders/{id}` | auth:sanctum | Owner/Admin | Chi tiết order (*) |
| PUT | `/api/orders/{id}` | auth:sanctum | Owner/Admin | Cập nhật order (*) |
| DELETE | `/api/orders/{id}` | auth:sanctum | Owner/Admin | Xóa order (*) |
| **Carts** |
| GET | `/api/carts` | auth:sanctum | Authenticated | Giỏ hàng của user |
| POST | `/api/carts` | auth:sanctum | Authenticated | Tạo giỏ hàng |
| GET | `/api/carts/{id}` | auth:sanctum | Owner | Chi tiết giỏ hàng |
| PUT | `/api/carts/{id}` | auth:sanctum | Owner | Cập nhật giỏ hàng |
| DELETE | `/api/carts/{id}` | auth:sanctum | Owner | Xóa giỏ hàng |
| **Cart Items** |
| GET | `/api/cart-items` | auth:sanctum | Authenticated | Danh sách items |
| POST | `/api/cart-items` | auth:sanctum | Authenticated | Thêm item vào cart |
| GET | `/api/cart-items/{id}` | auth:sanctum | Owner | Chi tiết item |
| PUT | `/api/cart-items/{id}` | auth:sanctum | Owner | Cập nhật item |
| DELETE | `/api/cart-items/{id}` | auth:sanctum | Owner | Xóa item |
| **Inventory** |
| GET | `/api/inventories` | auth:sanctum, admin | Admin | Danh sách tồn kho |
| POST | `/api/inventories` | auth:sanctum, admin | Admin | Tạo tồn kho |
| GET | `/api/inventories/{id}` | auth:sanctum, admin | Admin | Chi tiết tồn kho |
| PUT | `/api/inventories/{id}` | auth:sanctum, admin | Admin | Cập nhật tồn kho |
| DELETE | `/api/inventories/{id}` | auth:sanctum, admin | Admin | Xóa tồn kho |

**Chú thích (*)**:
- Orders: User chỉ xem/sửa/xóa orders của mình, Admin xem tất cả
- Ownership được kiểm tra trong Controller logic

---

## 💡 Code Examples

### 1. Test Authentication Flow

```bash
# 1. Đăng ký user mới
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Response: Lưu token để sử dụng cho các request tiếp theo
# {
#   "status": true,
#   "message": "Registration successful",
#   "token": "1|xxxxx..."
# }

# 2. Login
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# 3. Truy cập protected route
TOKEN="1|xxxxx..."
curl -X GET http://localhost/api/user \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# 4. Logout
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### 2. Test Authorization

```bash
# User không phải admin cố gắng tạo category
curl -X POST http://localhost/api/categories \
  -H "Authorization: Bearer {user_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Category",
    "description": "Test"
  }'

# Response: 403 Forbidden
# {
#   "status": false,
#   "message": "Access denied. Admin role required."
# }

# Admin tạo category
curl -X POST http://localhost/api/categories \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Category",
    "description": "Test"
  }'

# Response: 201 Created
# {
#   "status": true,
#   "message": "Category created successfully",
#   "data": {...}
# }
```

### 3. Kiểm tra Role trong Controller

```php
// Trong OrderController
public function index(Request $request)
{
    $query = Order::query();
    
    // Kiểm tra role
    if ($request->user()->isAdmin()) {
        // Admin: Xem tất cả orders, có thể filter
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    } else {
        // User thường: Chỉ xem orders của mình
        $query->where('user_id', $request->user()->id);
    }
    
    return new OrderCollection($query->paginate(10));
}
```

### 4. Kiểm tra Ownership

```php
public function show(Request $request, $id)
{
    $order = Order::with('items')->findOrFail($id);
    
    // Kiểm tra ownership: User chỉ xem order của mình
    if (!$request->user()->isAdmin() && 
        $order->user_id !== $request->user()->id) {
        return response()->json([
            'status' => false,
            'message' => 'Access denied. You can only access your own orders.',
        ], 403);
    }
    
    return new OrderResource($order);
}
```

---

## 🔍 Chi tiết kỹ thuật

### 1. Token Storage

Tokens được lưu trong bảng `personal_access_tokens`:

```php
// Khi login
$token = $user->createToken('api-token')->plainTextToken;
// Laravel tự động:
// 1. Generate random token (64 chars)
// 2. Hash token và lưu vào database
// 3. Trả về plaintext token cho client

// Database record:
// id: 1
// tokenable_type: App\Models\User
// tokenable_id: 1
// name: 'api-token'
// token: [hashed_token]
// abilities: null
// last_used_at: null
// expires_at: null
```

### 2. Middleware Stack

Khi request đến một protected route, nó đi qua middleware stack:

```
Request
  ↓
1. StartSession
  ↓
2. VerifyCsrfToken (skip for API)
  ↓
3. SubstituteBindings
  ↓
4. Throttle (nếu có)
  ↓
5. auth:sanctum
   ├─> Lấy token từ header
   ├─> Tìm token trong DB
   ├─> Load user
   └─> Set $request->user()
  ↓
6. admin (nếu có)
   ├─> Kiểm tra $request->user()->isAdmin()
   └─> 403 nếu không phải admin
  ↓
Controller
```

### 3. Database Queries trong Authentication

```php
// Login - 3 queries
// 1. Tìm user
$user = User::where('email', $request->email)->first();
// SELECT * FROM users WHERE email = ?

// 2. Xóa tokens cũ
$user->tokens()->delete();
// DELETE FROM personal_access_tokens WHERE tokenable_id = ?

// 3. Tạo token mới
$token = $user->createToken('api-token');
// INSERT INTO personal_access_tokens (tokenable_type, tokenable_id, name, token, ...)
```

```php
// Middleware auth:sanctum - 2 queries mỗi request
// 1. Tìm token
// SELECT * FROM personal_access_tokens WHERE token = ?

// 2. Load user với roles
// SELECT * FROM users WHERE id = ?
// SELECT * FROM roles INNER JOIN user_roles ON ... WHERE user_roles.user_id = ?
```

### 4. Eager Loading để tối ưu

```php
// Tránh N+1 problem
// Bad: 1 query + N queries
$orders = Order::all();
foreach ($orders as $order) {
    echo $order->user->name; // N queries
}

// Good: 2 queries
$orders = Order::with('user')->all(); // 1 query orders + 1 query users
foreach ($orders as $order) {
    echo $order->user->name; // No additional query
}
```

---

## 🎓 Best Practices

### 1. Token Management

✅ **DO**:
- Xóa token cũ khi login để tránh nhiều sessions
- Set expiration time cho tokens (production)
- Revoke tokens khi đổi password
- Sử dụng HTTPS trong production

❌ **DON'T**:
- Lưu plaintext token trong database
- Share token giữa nhiều users
- Hardcode tokens trong code

### 2. Authorization

✅ **DO**:
- Kiểm tra authorization trong Controller logic
- Sử dụng Policy classes cho complex authorization
- Log unauthorized attempts
- Return clear error messages

❌ **DON'T**:
- Tin tưởng client-side role checks
- Expose sensitive data trong error messages
- Bypass authorization trong development

### 3. Security

✅ **DO**:
- Validate tất cả inputs
- Use rate limiting (throttle)
- Hash passwords (never store plain)
- Use prepared statements (Eloquent tự động)
- Sanitize outputs

❌ **DON'T**:
- Trust user inputs
- Return stack traces trong production
- Log sensitive data (passwords, tokens)

---

## 🚀 Testing

### Postman Collection

```json
{
  "info": {
    "name": "Webshop API",
    "_postman_id": "...",
    "schema": "..."
  },
  "auth": {
    "type": "bearer",
    "bearer": [
      {
        "key": "token",
        "value": "{{auth_token}}",
        "type": "string"
      }
    ]
  },
  "item": [
    {
      "name": "Auth",
      "item": [
        {
          "name": "Register",
          "request": {
            "method": "POST",
            "url": "{{base_url}}/api/register",
            "body": {
              "mode": "raw",
              "raw": "{\n  \"name\": \"Test User\",\n  \"email\": \"test@example.com\",\n  \"password\": \"password123\",\n  \"password_confirmation\": \"password123\"\n}"
            }
          }
        }
      ]
    }
  ]
}
```

### PHPUnit Tests

```php
// tests/Feature/AuthTest.php
public function test_user_can_register()
{
    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);
    
    $response->assertStatus(201)
             ->assertJsonStructure(['status', 'message', 'user', 'token']);
}

public function test_admin_can_create_category()
{
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::where('role_name', 'admin')->first());
    
    $response = $this->actingAs($admin, 'sanctum')
                     ->postJson('/api/categories', [
                         'name' => 'Test Category',
                         'description' => 'Test',
                     ]);
    
    $response->assertStatus(201);
}

public function test_non_admin_cannot_create_category()
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('role_name', 'customer')->first());
    
    $response = $this->actingAs($user, 'sanctum')
                     ->postJson('/api/categories', [
                         'name' => 'Test Category',
                         'description' => 'Test',
                     ]);
    
    $response->assertStatus(403);
}
```

---

## � Chi tiết API Product Details (Thông số kỹ thuật điện thoại)

### 1. Cấu trúc dữ liệu Product Details

Product Details lưu trữ thông số kỹ thuật chi tiết của điện thoại, bao gồm:

**Bảng `product_details`**:
```sql
CREATE TABLE product_details (
    detail_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT NOT NULL,
    color VARCHAR(50) NULL,              -- Màu sắc: Titan Xanh, Đen, Tím, Hồng...
    storage VARCHAR(20) NULL,            -- Bộ nhớ: 128GB, 256GB, 512GB, 1TB
    ram VARCHAR(20) NULL,                -- RAM: 4GB, 6GB, 8GB, 12GB, 16GB
    screen_size VARCHAR(20) NULL,        -- Màn hình: 6.1 inch, 6.7 inch, 6.8 inch
    chip VARCHAR(100) NULL,              -- Vi xử lý: Apple A17 Pro, Snapdragon 8 Gen 3
    battery VARCHAR(50) NULL,            -- Pin: 5000 mAh, 4422 mAh
    camera_main VARCHAR(100) NULL,       -- Camera sau: 48MP Main + 12MP Ultra Wide
    camera_front VARCHAR(100) NULL,      -- Camera trước: 12MP, 32MP, 50MP
    os VARCHAR(50) NULL,                 -- Hệ điều hành: iOS 17, Android 14
    special_features TEXT NULL,          -- Tính năng đặc biệt
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);
```

### 2. GET /api/product-details (Lấy danh sách)

**Middleware**: `auth:sanctum`, `admin`

**Query Parameters** (Filters):
- `product_id`: Lọc theo ID sản phẩm
- `color`: Lọc theo màu sắc (exact match)
- `storage`: Lọc theo dung lượng (128GB, 256GB, 512GB)
- `ram`: Lọc theo RAM (4GB, 8GB, 12GB, 16GB)
- `chip`: Tìm kiếm chip (LIKE search)
- `os`: Tìm kiếm hệ điều hành (LIKE search)

**Request Examples**:
```bash
# Lấy tất cả product details
GET /api/product-details
Headers: Authorization: Bearer {admin_token}

# Lọc theo sản phẩm cụ thể
GET /api/product-details?product_id=1

# Lọc theo màu và dung lượng
GET /api/product-details?color=Titan%20Xanh&storage=256GB

# Lọc theo RAM
GET /api/product-details?ram=12GB

# Tìm kiếm theo chip
GET /api/product-details?chip=Snapdragon

# Tìm kiếm theo OS
GET /api/product-details?os=iOS
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Product detail retrieved successfully",
  "data": [
    {
      "detail_id": 1,
      "product_id": 1,
      "color": "Titan Xanh",
      "storage": "256GB",
      "ram": "8GB",
      "screen_size": "6.7 inch",
      "chip": "Apple A17 Pro",
      "battery": "4422 mAh",
      "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
      "camera_front": "12MP",
      "os": "iOS 17",
      "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button",
      "created_at": "2025-01-15T10:30:00.000000Z",
      "updated_at": "2025-01-15T10:30:00.000000Z"
    },
    {
      "detail_id": 2,
      "product_id": 2,
      "color": "Titan Đen",
      "storage": "512GB",
      "ram": "12GB",
      "screen_size": "6.8 inch",
      "chip": "Snapdragon 8 Gen 3 for Galaxy",
      "battery": "5000 mAh",
      "camera_main": "200MP Main + 50MP Periscope + 10MP Telephoto + 12MP Ultra Wide",
      "camera_front": "12MP",
      "os": "Android 14, One UI 6.1",
      "special_features": "S Pen, Galaxy AI, QHD+ AMOLED 2X, Gorilla Armor, Titanium Frame",
      "created_at": "2025-01-15T10:35:00.000000Z",
      "updated_at": "2025-01-15T10:35:00.000000Z"
    }
  ],
  "links": {
    "first": "http://localhost/api/product-details?page=1",
    "last": "http://localhost/api/product-details?page=5",
    "prev": null,
    "next": "http://localhost/api/product-details?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 5,
    "per_page": 10,
    "to": 10,
    "total": 50
  }
}
```

### 3. POST /api/product-details (Tạo mới)

**Middleware**: `auth:sanctum`, `admin`

**Validation Rules**:
```php
[
    'product_id' => 'required|exists:products,product_id',
    'color' => 'nullable|string|max:50',
    'storage' => 'nullable|string|max:20',
    'ram' => 'nullable|string|max:20',
    'screen_size' => 'nullable|string|max:20',
    'chip' => 'nullable|string|max:100',
    'battery' => 'nullable|string|max:50',
    'camera_main' => 'nullable|string|max:100',
    'camera_front' => 'nullable|string|max:100',
    'os' => 'nullable|string|max:50',
    'special_features' => 'nullable|string',
]
```

**Request Example**:
```bash
POST /api/product-details
Headers: 
  Authorization: Bearer {admin_token}
  Content-Type: application/json

Body:
{
  "product_id": 1,
  "color": "Titan Trắng",
  "storage": "512GB",
  "ram": "8GB",
  "screen_size": "6.7 inch",
  "chip": "Apple A17 Pro",
  "battery": "4422 mAh",
  "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
  "camera_front": "12MP",
  "os": "iOS 17",
  "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C"
}
```

**Response Success (201)**:
```json
{
  "success": true,
  "message": "Product detail created successfully",
  "data": {
    "detail_id": 51,
    "product_id": 1,
    "color": "Titan Trắng",
    "storage": "512GB",
    "ram": "8GB",
    "screen_size": "6.7 inch",
    "chip": "Apple A17 Pro",
    "battery": "4422 mAh",
    "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
    "camera_front": "12MP",
    "os": "iOS 17",
    "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C",
    "created_at": "2025-01-16T14:20:00.000000Z",
    "updated_at": "2025-01-16T14:20:00.000000Z"
  }
}
```

**Response Error (422) - Validation Failed**:
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "product_id": [
      "Product not found"
    ],
    "storage": [
      "Storage must not exceed 20 characters"
    ]
  }
}
```

### 4. GET /api/product-details/{id} (Chi tiết)

**Middleware**: `auth:sanctum`, `admin`

**Request Example**:
```bash
GET /api/product-details/1
Headers: Authorization: Bearer {admin_token}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Product detail retrieved successfully",
  "data": {
    "detail_id": 1,
    "product_id": 1,
    "color": "Titan Xanh",
    "storage": "256GB",
    "ram": "8GB",
    "screen_size": "6.7 inch",
    "chip": "Apple A17 Pro",
    "battery": "4422 mAh",
    "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
    "camera_front": "12MP",
    "os": "iOS 17",
    "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-15T10:30:00.000000Z"
  }
}
```

**Response Error (404)**:
```json
{
  "message": "Product detail not found"
}
```

### 5. PUT/PATCH /api/product-details/{id} (Cập nhật)

**Middleware**: `auth:sanctum`, `admin`

**Request Example**:
```bash
PUT /api/product-details/1
Headers: 
  Authorization: Bearer {admin_token}
  Content-Type: application/json

Body:
{
  "color": "Titan Đen",
  "storage": "512GB",
  "ram": "12GB",
  "battery": "5000 mAh"
}
```

**Response Success (200)**:
```json
{
  "success": true,
  "message": "Product detail updated successfully",
  "data": {
    "detail_id": 1,
    "product_id": 1,
    "color": "Titan Đen",
    "storage": "512GB",
    "ram": "12GB",
    "screen_size": "6.7 inch",
    "chip": "Apple A17 Pro",
    "battery": "5000 mAh",
    "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
    "camera_front": "12MP",
    "os": "iOS 17",
    "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button",
    "created_at": "2025-01-15T10:30:00.000000Z",
    "updated_at": "2025-01-16T15:45:00.000000Z"
  }
}
```

### 6. DELETE /api/product-details/{id} (Xóa)

**Middleware**: `auth:sanctum`, `admin`

**Request Example**:
```bash
DELETE /api/product-details/1
Headers: Authorization: Bearer {admin_token}
```

**Response Success (200)**:
```json
{
  "message": "Product detail deleted successfully"
}
```

**Response Error (404)**:
```json
{
  "message": "Product detail not found"
}
```

---

## 📱 GET /api/products - Hiển thị sản phẩm kèm chi tiết kỹ thuật

Khi lấy danh sách sản phẩm hoặc chi tiết sản phẩm, API sẽ tự động load thông tin `details` (thông số kỹ thuật).

### Request Example:
```bash
# Lấy danh sách sản phẩm
GET /api/products
Headers: Accept: application/json

# Lấy chi tiết 1 sản phẩm
GET /api/products/1
Headers: Accept: application/json
```

### Response với Product Details:
```json
{
  "data": [
    {
      "id": 1,
      "name": "iPhone 15 Pro Max 256GB",
      "description": "iPhone 15 Pro Max với chip A17 Pro mạnh mẽ, camera 48MP, màn hình Dynamic Island",
      "price": 33990000,
      "category_id": 1,
      "stock_quantity": 25,
      "image_url": "https://cdn.tgdd.vn/Products/Images/42/305658/iphone-15-pro-max-blue-1.jpg",
      "category": {
        "category_id": 1,
        "name": "iPhone",
        "description": "Dòng điện thoại cao cấp của Apple"
      },
      "details": {
        "detail_id": 1,
        "product_id": 1,
        "color": "Titan Xanh",
        "storage": "256GB",
        "ram": "8GB",
        "screen_size": "6.7 inch",
        "chip": "Apple A17 Pro",
        "battery": "4422 mAh",
        "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
        "camera_front": "12MP",
        "os": "iOS 17",
        "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button",
        "created_at": "2025-01-15T10:30:00.000000Z",
        "updated_at": "2025-01-15T10:30:00.000000Z"
      },
      "created_at": "2025-01-15T10:00:00.000000Z",
      "updated_at": "2025-01-15T10:00:00.000000Z"
    }
  ]
}
```

---

## 📊 Ví dụ thực tế: Dữ liệu mẫu

### iPhone 15 Pro Max
```json
{
  "product_id": 1,
  "color": "Titan Xanh",
  "storage": "256GB",
  "ram": "8GB",
  "screen_size": "6.7 inch",
  "chip": "Apple A17 Pro",
  "battery": "4422 mAh",
  "camera_main": "48MP Main + 12MP Telephoto + 12MP Ultra Wide",
  "camera_front": "12MP",
  "os": "iOS 17",
  "special_features": "Dynamic Island, Titanium Design, ProMotion 120Hz, USB-C, Action Button"
}
```

### Samsung Galaxy S24 Ultra
```json
{
  "product_id": 2,
  "color": "Titan Đen",
  "storage": "256GB",
  "ram": "12GB",
  "screen_size": "6.8 inch",
  "chip": "Snapdragon 8 Gen 3 for Galaxy",
  "battery": "5000 mAh",
  "camera_main": "200MP Main + 50MP Periscope + 10MP Telephoto + 12MP Ultra Wide",
  "camera_front": "12MP",
  "os": "Android 14, One UI 6.1",
  "special_features": "S Pen, Galaxy AI, QHD+ AMOLED 2X, Gorilla Armor, Titanium Frame"
}
```

### Xiaomi 14 Ultra
```json
{
  "product_id": 3,
  "color": "Đen",
  "storage": "512GB",
  "ram": "16GB",
  "screen_size": "6.73 inch",
  "chip": "Snapdragon 8 Gen 3",
  "battery": "5000 mAh",
  "camera_main": "50MP Main Leica + 50MP Periscope + 50MP Telephoto + 50MP Ultra Wide",
  "camera_front": "32MP",
  "os": "Android 14, HyperOS",
  "special_features": "Leica Professional Optics, LTPO AMOLED 120Hz, Sạc nhanh 90W"
}
```

### OPPO Reno11 F (Phân khúc tầm trung)
```json
{
  "product_id": 4,
  "color": "Xanh",
  "storage": "256GB",
  "ram": "8GB",
  "screen_size": "6.7 inch",
  "chip": "MediaTek Dimensity 7050",
  "battery": "5000 mAh",
  "camera_main": "64MP Main + 8MP Ultra Wide + 2MP Macro",
  "camera_front": "32MP",
  "os": "Android 14, ColorOS 14",
  "special_features": "AMOLED 120Hz, IP65, Sạc nhanh SUPERVOOC 67W"
}
```

---

## 🔍 Use Cases

### 1. Tìm điện thoại theo RAM
```bash
GET /api/product-details?ram=12GB
# Trả về tất cả các biến thể điện thoại có RAM 12GB
```

### 2. Tìm điện thoại theo dung lượng
```bash
GET /api/product-details?storage=256GB
# Trả về tất cả các biến thể điện thoại có bộ nhớ 256GB
```

### 3. Tìm điện thoại theo chip
```bash
GET /api/product-details?chip=Snapdragon
# Trả về tất cả điện thoại dùng chip Snapdragon (LIKE search)
```

### 4. Tìm điện thoại iOS
```bash
GET /api/product-details?os=iOS
# Trả về tất cả iPhone
```

### 5. Xem tất cả biến thể màu của 1 sản phẩm
```bash
GET /api/product-details?product_id=1
# Trả về tất cả các màu sắc của iPhone 15 Pro Max
```

---

## �📖 Tài liệu tham khảo

- [Laravel Sanctum Documentation](https://laravel.com/docs/11.x/sanctum)
- [Laravel Authentication](https://laravel.com/docs/11.x/authentication)
- [Laravel Authorization](https://laravel.com/docs/11.x/authorization)
- [Laravel Middleware](https://laravel.com/docs/11.x/middleware)
- [Laravel Eloquent Relationships](https://laravel.com/docs/11.x/eloquent-relationships)
- [Laravel API Resources](https://laravel.com/docs/11.x/eloquent-resources)

---

**Người viết**: Vinh Hoang 2004  
**Ngày tạo**: 6/10/2025  
**Cập nhật lần cuối**: 16/10/2025 - Thêm chi tiết API Product Details cho điện thoại
