# 🔐 Hệ thống Xác thực & Phân quyền

> **Mục đích**: Tài liệu chi tiết về hệ thống xác thực và phân quyền

## 📋 Mục lục
1. [Tổng quan](#tổng-quan)
2. [Laravel Sanctum](#laravel-sanctum)
3. [Luồng xác thực](#luồng-xác-thực)
4. [Hệ thống Middleware](#hệ-thống-middleware)
5. [Kiểm soát truy cập dựa trên vai trò](#kiểm-soát-truy-cập-dựa-trên-vai-trò-rbac)
6. [Ví dụ code](#ví-dụ-code)

---

## 🎯 Tổng quan

### Công nghệ sử dụng
- **Laravel Sanctum**: Xác thực dựa trên token
- **RBAC**: Kiểm soát truy cập dựa trên vai trò
- **Middleware**: Lọc yêu cầu & phân quyền

### Các vai trò (Roles)
- **Admin**: Toàn quyền hệ thống
- **Manager**: Quản lý sản phẩm, đơn hàng (không xóa)
- **Customer**: Mua hàng, xem đơn của mình
- **User**: Chỉ xem sản phẩm

---

## 🛡️ Laravel Sanctum

### Cài đặt

#### 1. Model User
```php
// app/Models/User.php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address',
    ];
    
    protected $hidden = [
        'password', 'remember_token',
    ];
}
```

#### 2. Configuration
```php
// config/sanctum.php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 
        'localhost,localhost:3000,127.0.0.1'
    )),
    
    'guard' => ['web'],
    
    'expiration' => null, // Token never expires
    
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
    ],
];
```

#### 3. Database Schema
```sql
CREATE TABLE personal_access_tokens (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) UNIQUE NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

---

## 🔄 Luồng xác thực

### 1. Đăng ký (Register)

**Endpoint**: `POST /api/register`

**Request Body**:
```json
{
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "0123456789",
  "address": "Ha Noi"
}
```

**Controller Code**:
```php
// app/Http/Controllers/Api/AuthController.php
public function register(Request $request)
{
    // 1. Validate
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
    ]);
    
    // 2. Create user
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'phone' => $request->phone,
        'address' => $request->address,
    ]);
    
    // 3. Create token
    $token = $user->createToken('api-token')->plainTextToken;
    
    // 4. Response
    return response()->json([
        'status' => true,
        'message' => 'Registration successful',
        'user' => $user,
        'token' => $token,
    ], 201);
}
```

**Response Success (201)**:
```json
{
  "status": true,
  "message": "Registration successful",
  "user": {
    "id": 1,
    "name": "Nguyen Van A",
    "email": "user@example.com"
  },
  "token": "1|abcdefghijklmnopqrstuvwxyz123456"
}
```

---

### 2. Đăng nhập (Login) 

**Endpoint**: `POST /api/login`

**Request Body**:
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Controller Code**:
```php
public function login(Request $request)
{
    // 1. Validate
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    // 2. Find user
    $user = User::where('email', $request->email)->first();
    
    // 3. Check credentials
    if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }
    
    // 4. Delete old tokens
    $user->tokens()->delete();
    
    // 5. Create new token
    $token = $user->createToken('api-token')->plainTextToken;
    
    return response()->json([
        'status' => true,
        'message' => 'Login successful',
        'user' => $user,
        'token' => $token,
    ]);
}
```

**Response Success (200)**:
```json
{
  "status": true,
  "message": "Login successful",
  "user": {...},
  "token": "2|zyxwvutsrqponmlkjihgfedcba654321"
}
```

---

### 3. Đăng xuất (Logout)

**Endpoint**: `POST /api/logout`

**Headers**:
```
Authorization: Bearer {token}
```

**Controller Code**:
```php
public function logout(Request $request)
{
    // Delete current token
    $request->user()->currentAccessToken()->delete();
    
    return response()->json([
        'status' => true,
        'message': 'Logout successful',
    ]);
}
```

---

### 4. Lấy thông tin người dùng hiện tại

**Endpoint**: `GET /api/user`

**Headers**:
```
Authorization: Bearer {token}
```

**Route**:
```php
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
```

**Response**:
```json
{
  "id": 1,
  "name": "Nguyen Van A",
  "email": "user@example.com",
  "phone": "0123456789",
  "address": "Ha Noi"
}
```

---

## 🛡️ Hệ thống Middleware

### 1. Middleware có sẵn

#### auth:sanctum
**Chức năng**: Xác thực API token

**Luồng xử lý**:
```
1. Trích xuất token từ header: Authorization: Bearer {token}
2. Băm token và tìm kiếm trong personal_access_tokens
3. Xác minh tính hợp lệ của token (chưa hết hạn, chưa bị xóa)
4. Tải user từ tokenable_id
5. Inject user vào $request->user()
6. Cập nhật last_used_at
7. Tiếp tục đến controller
```

**Usage**:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn($r) => $r->user());
});
```

---

#### throttle
**Chức năng**: Giới hạn tốc độ

**Cú pháp**: `throttle:{max_attempts},{decay_minutes}`

**Ví dụ**:
```php
// 60 yêu cầu mỗi phút
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
});

// 30 yêu cầu mỗi phút cho nhóm cụ thể
Route::middleware('throttle:30,1')->group(function () {
    // Các thao tác nhạy cảm
});
```

**Phản hồi khi vượt giới hạn (429)**:
```json
{
  "message": "Too Many Attempts."
}
```

---

### 2. Middleware tùy chỉnh

#### AdminMiddleware
**File**: `app/Http/Middleware/AdminMiddleware.php`

**Code**:
```php
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu user đã xác thực và có vai trò admin
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message': 'Truy cập bị từ chối. Yêu cầu vai trò admin.',
            ], 403);
        }
        
        return $next($request);
    }
}
```

**Đăng ký** (`bootstrap/app.php`):
```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

**Usage**:
```php
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});
```

---

## 👥 Kiểm soát truy cập dựa trên vai trò (RBAC)

### Schema cơ sở dữ liệu

#### Bảng Roles
```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Vai trò mặc định
INSERT INTO roles (role_name, description) VALUES
('admin', 'Quản trị viên - Toàn quyền'),
('manager', 'Quản lý - Xem và chỉnh sửa'),
('customer', 'Khách hàng - Mua sản phẩm'),
('user', 'Người dùng thường - Chỉ xem');
```

#### User Roles (Bảng trung gian)
```sql
CREATE TABLE user_roles (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    role_id BIGINT,
    created_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY (user_id, role_id)
);
```

---

### Quan hệ Model

```php
// app/Models/User.php
class User extends Authenticatable
{
    // Quan hệ nhiều-nhiều
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        );
    }
    
    // Các phương thức trợ giúp
    public function hasRole(string $roleName): bool
    {
        return $this->roles()
            ->where('role_name', $roleName)
            ->exists();
    }
    
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    
    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }
    
    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
    
    public function canAccessDashboard(): bool
    {
        return $this->isAdmin() || $this->isManager();
    }
}
```

---

### Ma trận phân quyền

| Tài nguyên | Admin | Manager | Customer | Guest |
|----------|-------|---------|----------|-------|
| **Sản phẩm** |
| Xem | ✅ | ✅ | ✅ | ✅ |
| Tạo | ✅ | ❌ | ❌ | ❌ |
| Sửa | ✅ | ✅ | ❌ | ❌ |
| Xóa | ✅ | ❌ | ❌ | ❌ |
| **Danh mục** |
| Xem | ✅ | ✅ | ✅ | ✅ |
| Tạo | ✅ | ❌ | ❌ | ❌ |
| Sửa | ✅ | ❌ | ❌ | ❌ |
| Xóa | ✅ | ❌ | ❌ | ❌ |
| **Đơn hàng** |
| Xem tất cả | ✅ | ✅ | ❌ | ❌ |
| Xem của mình | ✅ | ✅ | ✅ | ❌ |
| Tạo | ✅ | ✅ | ✅ | ❌ |
| Sửa | ✅ | ✅ | ❌ | ❌ |
| Xóa | ✅ | ❌ | ❌ | ❌ |
| **Người dùng** |
| Xem | ✅ | ❌ | ❌ | ❌ |
| Tạo | ✅ | ❌ | ❌ | ❌ |
| Sửa | ✅ | ❌ | ❌ | ❌ |
| Xóa | ✅ | ❌ | ❌ | ❌ |
| **Tồn kho** |
| Xem | ✅ | ✅ | ❌ | ❌ |
| Điều chỉnh | ✅ | ✅ | ❌ | ❌ |

---

## 💡 Ví dụ code

### 1. Kiểm tra xác thực API

```bash
# 1. Register
curl -X POST http://localhost/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# 2. Login
curl -X POST http://localhost/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'

# Lưu token từ phản hồi
TOKEN="1|xxxxx..."

# 3. Truy cập route được bảo vệ
curl -X GET http://localhost/api/user \
  -H "Authorization: Bearer $TOKEN"

# 4. Logout
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

### 2. Kiểm tra vai trò trong Controller

```php
public function index(Request $request)
{
    $query = Order::query();
    
    // Admin/Manager: Xem tất cả đơn hàng
    if ($request->user()->isAdmin() || $request->user()->isManager()) {
        // Áp dụng bộ lọc
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    } 
    // Customer: Chỉ xem đơn hàng của mình
    else {
        $query->where('user_id', $request->user()->id);
    }
    
    return OrderResource::collection($query->paginate());
}
```

---

### 3. Kiểm tra quyền sở hữu

```php
public function show(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Admin có thể xem bất kỳ đơn hàng nào
    if ($request->user()->isAdmin()) {
        return new OrderResource($order);
    }
    
    // Người khác chỉ có thể xem đơn hàng của mình
    if ($order->user_id !== $request->user()->id) {
        return response()->json([
            'status' => false,
            'message': 'Truy cập bị từ chối.',
        ], 403);
    }
    
    return new OrderResource($order);
}
```

---

### 4. Kiểm tra nhiều vai trò

```php
// Cho phép admin HOẶC manager
Route::middleware(['auth:sanctum', function ($request, $next) {
    $user = $request->user();
    
    if (!$user->isAdmin() && !$user->isManager()) {
        return response()->json([
            'status' => false,
            'message': 'Yêu cầu vai trò admin hoặc manager.',
        ], 403);
    }
    
    return $next($request);
}])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

---

## 🔒 Thực hành bảo mật tốt nhất

### 1. Quản lý Token

✅ **NÊN**:
- Xóa token cũ trước khi tạo token mới
- Đặt tên token có ý nghĩa
- Sử dụng HTTPS trong production
- Lưu trữ token an toàn ở phía client

❌ **KHÔNG NÊN**:
- Log token dưới dạng plain text
- Chia sẻ token giữa các user
- Gửi token qua URL parameters

### 2. Bảo mật password

✅ **NÊN**:
```php
// Băm password
'password' => Hash::make($request->password)

// Xác minh password
Hash::check($plainPassword, $hashedPassword)
```

❌ **KHÔNG NÊN**:
```php
// Lưu password dạng plain text
'password' => $request->password // ❌ TUYỆT ĐỐI KHÔNG!
```

### 3. Xác thực đầu vào

✅ **NÊN**:
```php
$request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
]);
```

### 4. Giới hạn tốc độ

✅ **NÊN**:
```php
Route::middleware('throttle:60,1')->group(function () {
    // 60 yêu cầu mỗi phút
});
```

---

## 🧪 Kiểm thử xác thực

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
             ->assertJsonStructure(['status', 'user', 'token']);
}

public function test_user_can_login()
{
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
    ]);
    
    $response = $this->postJson('/api/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);
    
    $response->assertStatus(200)
             ->assertJsonStructure(['status', 'user', 'token']);
}

public function test_admin_can_access_protected_route()
{
    $admin = User::factory()->create();
    $admin->roles()->attach(Role::where('role_name', 'admin')->first());
    
    $token = $admin->createToken('test-token')->plainTextToken;
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->postJson('/api/products', [...]);
    
    $response->assertStatus(201);
}

public function test_customer_cannot_access_admin_route()
{
    $customer = User::factory()->create();
    $customer->roles()->attach(Role::where('role_name', 'customer')->first());
    
    $token = $customer->createToken('test-token')->plainTextToken;
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->postJson('/api/products', [...]);
    
    $response->assertStatus(403);
}
```

---

## 📚 Tài liệu liên quan

- **[API_REFERENCE.md](./API_REFERENCE.md)** - Danh sách API endpoints
- **[ARCHITECTURE.md](./ARCHITECTURE.md)** - Kiến trúc hệ thống
- **[DATABASE.md](./DATABASE.md)** - Schema cơ sở dữ liệu

### Tài nguyên bên ngoài
- [Tài liệu Laravel Sanctum](https://laravel.com/docs/sanctum)
- [Tài liệu Laravel Authentication](https://laravel.com/docs/authentication)
- [Tài liệu Laravel Authorization](https://laravel.com/docs/authorization)

---

**Cập nhật lần cuối**: 21/10/2025  
**Phiên bản**: 3.0  
**Tác giả**: Hoàng Quang Vinh
