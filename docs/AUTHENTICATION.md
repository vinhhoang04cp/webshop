# 🔐 Authentication & Authorization System

> **Mục đích**: Tài liệu chi tiết về hệ thống xác thực và phân quyền

## 📋 Mục lục
1. [Tổng quan](#tổng-quan)
2. [Laravel Sanctum](#laravel-sanctum)
3. [Authentication Flow](#authentication-flow)
4. [Middleware System](#middleware-system)
5. [Role-Based Access Control](#role-based-access-control-rbac)
6. [Code Examples](#code-examples)

---

## 🎯 Tổng quan

### Công nghệ sử dụng
- **Laravel Sanctum**: Token-based authentication
- **RBAC**: Role-Based Access Control
- **Middleware**: Request filtering & authorization

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

## 🔄 Authentication Flow

### 1. Register (Đăng ký)

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

### 2. Login (Đăng nhập)

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

### 3. Logout (Đăng xuất)

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

### 4. Get Current User

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

## 🛡️ Middleware System

### 1. Built-in Middleware

#### auth:sanctum
**Chức năng**: Xác thực API token

**Flow**:
```
1. Extract token from header: Authorization: Bearer {token}
2. Hash token and search in personal_access_tokens
3. Verify token validity (not expired, not deleted)
4. Load user from tokenable_id
5. Inject user into $request->user()
6. Update last_used_at
7. Continue to controller
```

**Usage**:
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn($r) => $r->user());
});
```

---

#### throttle
**Chức năng**: Rate limiting

**Syntax**: `throttle:{max_attempts},{decay_minutes}`

**Examples**:
```php
// 60 requests per minute
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
});

// 30 requests per minute for specific group
Route::middleware('throttle:30,1')->group(function () {
    // Sensitive operations
});
```

**Response khi vượt limit (429)**:
```json
{
  "message": "Too Many Attempts."
}
```

---

### 2. Custom Middleware

#### AdminMiddleware
**File**: `app/Http/Middleware/AdminMiddleware.php`

**Code**:
```php
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has admin role
        if (!$request->user() || !$request->user()->hasRole('admin')) {
            return response()->json([
                'status' => false,
                'message': 'Access denied. Admin role required.',
            ], 403);
        }
        
        return $next($request);
    }
}
```

**Registration** (`bootstrap/app.php`):
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

## 👥 Role-Based Access Control (RBAC)

### Database Schema

#### Roles Table
```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    role_name VARCHAR(50) UNIQUE,
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Default roles
INSERT INTO roles (role_name, description) VALUES
('admin', 'Administrator - Full permissions'),
('manager', 'Manager - View and edit'),
('customer', 'Customer - Purchase products'),
('user', 'Regular user - View only');
```

#### User Roles (Pivot Table)
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

### Model Relationships

```php
// app/Models/User.php
class User extends Authenticatable
{
    // Many-to-Many relationship
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
        );
    }
    
    // Helper methods
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

### Permission Matrix

| Resource | Admin | Manager | Customer | Guest |
|----------|-------|---------|----------|-------|
| **Products** |
| View | ✅ | ✅ | ✅ | ✅ |
| Create | ✅ | ❌ | ❌ | ❌ |
| Edit | ✅ | ✅ | ❌ | ❌ |
| Delete | ✅ | ❌ | ❌ | ❌ |
| **Categories** |
| View | ✅ | ✅ | ✅ | ✅ |
| Create | ✅ | ❌ | ❌ | ❌ |
| Edit | ✅ | ❌ | ❌ | ❌ |
| Delete | ✅ | ❌ | ❌ | ❌ |
| **Orders** |
| View All | ✅ | ✅ | ❌ | ❌ |
| View Own | ✅ | ✅ | ✅ | ❌ |
| Create | ✅ | ✅ | ✅ | ❌ |
| Edit | ✅ | ✅ | ❌ | ❌ |
| Delete | ✅ | ❌ | ❌ | ❌ |
| **Users** |
| View | ✅ | ❌ | ❌ | ❌ |
| Create | ✅ | ❌ | ❌ | ❌ |
| Edit | ✅ | ❌ | ❌ | ❌ |
| Delete | ✅ | ❌ | ❌ | ❌ |
| **Inventory** |
| View | ✅ | ✅ | ❌ | ❌ |
| Adjust | ✅ | ✅ | ❌ | ❌ |

---

## 💡 Code Examples

### 1. API Authentication Test

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

# Save token from response
TOKEN="1|xxxxx..."

# 3. Access protected route
curl -X GET http://localhost/api/user \
  -H "Authorization: Bearer $TOKEN"

# 4. Logout
curl -X POST http://localhost/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

---

### 2. Check Role in Controller

```php
public function index(Request $request)
{
    $query = Order::query();
    
    // Admin/Manager: View all orders
    if ($request->user()->isAdmin() || $request->user()->isManager()) {
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
    } 
    // Customer: View own orders only
    else {
        $query->where('user_id', $request->user()->id);
    }
    
    return OrderResource::collection($query->paginate());
}
```

---

### 3. Ownership Check

```php
public function show(Request $request, $id)
{
    $order = Order::findOrFail($id);
    
    // Admin can view any order
    if ($request->user()->isAdmin()) {
        return new OrderResource($order);
    }
    
    // Others can only view their own orders
    if ($order->user_id !== $request->user()->id) {
        return response()->json([
            'status' => false,
            'message': 'Access denied.',
        ], 403);
    }
    
    return new OrderResource($order);
}
```

---

### 4. Multiple Roles Check

```php
// Allow admin OR manager
Route::middleware(['auth:sanctum', function ($request, $next) {
    $user = $request->user();
    
    if (!$user->isAdmin() && !$user->isManager()) {
        return response()->json([
            'status' => false,
            'message': 'Requires admin or manager role.',
        ], 403);
    }
    
    return $next($request);
}])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

---

## 🔒 Security Best Practices

### 1. Token Management

✅ **DO**:
- Delete old tokens before creating new ones
- Set meaningful token names
- Use HTTPS in production
- Store tokens securely on client-side

❌ **DON'T**:
- Log tokens in plain text
- Share tokens between users
- Send tokens via URL parameters

### 2. Password Security

✅ **DO**:
```php
// Hash passwords
'password' => Hash::make($request->password)

// Verify passwords
Hash::check($plainPassword, $hashedPassword)
```

❌ **DON'T**:
```php
// Store plain text passwords
'password' => $request->password // ❌ NEVER!
```

### 3. Input Validation

✅ **DO**:
```php
$request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|confirmed',
]);
```

### 4. Rate Limiting

✅ **DO**:
```php
Route::middleware('throttle:60,1')->group(function () {
    // 60 requests per minute
});
```

---

## 🧪 Testing Authentication

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

- **[API_REFERENCE.md](./API_REFERENCE.md)** - API endpoints list
- **[ARCHITECTURE.md](./ARCHITECTURE.md)** - System architecture
- **[DATABASE.md](./DATABASE.md)** - Database schema

### External Resources
- [Laravel Sanctum Docs](https://laravel.com/docs/sanctum)
- [Laravel Authentication Docs](https://laravel.com/docs/authentication)
- [Laravel Authorization Docs](https://laravel.com/docs/authorization)

---

**Cập nhật lần cuối**: 19/10/2025  
**Version**: 2.0  
**Author**: Hoàng Quang Vinh
