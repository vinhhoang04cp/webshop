# QUY TẮC CHUNG KHI CODE - WEB CONTROLLERS VÀ MODELS

## MỤC LỤC
1. [Quy tắc chung cho Controllers](#quy-tắc-chung-cho-controllers)
2. [Quy tắc chung cho Models](#quy-tắc-chung-cho-models)
3. [Quy ước đặt tên](#quy-ước-đặt-tên)
4. [Xử lý lỗi và Exception](#xử-lý-lỗi-và-exception)
5. [Validation và Request Handling](#validation-và-request-handling)
6. [Database Queries và Eloquent](#database-queries-và-eloquent)
7. [Comment và Documentation](#comment-và-documentation)

---

## QUY TẮC CHUNG CHO CONTROLLERS

### 1. NAMESPACE VÀ IMPORT

**Quy tắc:**
- Luôn đặt namespace phù hợp với cấu trúc thư mục
- Import đầy đủ các class cần thiết ở đầu file
- Sử dụng alias khi cần thiết để tránh conflict

**Ví dụ:**
```php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
```

### 2. CẤU TRÚC CONTROLLER

**Quy tắc:**
- Kế thừa từ `Controller` base class
- Tổ chức các method theo thứ tự: CRUD operations (index, create, store, show, edit, update, destroy)
- Mỗi method phải có comment PHPDoc mô tả chức năng
- Giữ controller "thin" - logic phức tạp nên chuyển sang Service hoặc Repository

**Cấu trúc chuẩn:**
```php
class ProductController extends Controller
{
    /**
     * Display a listing of products for admin UI.
     */
    public function index(Request $request)
    {
        // Implementation
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        // Implementation
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        // Implementation
    }

    /**
     * Display the specified product.
     */
    public function show($id)
    {
        // Implementation
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit($id)
    {
        // Implementation
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, $id)
    {
        // Implementation
    }

    /**
     * Remove the specified product.
     */
    public function destroy($id)
    {
        // Implementation
    }
}
```

### 3. METHOD INDEX - HIỂN THỊ DANH SÁCH

**Quy tắc:**
- Luôn sử dụng `try-catch` để bắt exception
- Hỗ trợ tìm kiếm (search) qua Request parameter
- Sử dụng pagination cho danh sách dài
- Load relationships cần thiết bằng `with()`
- Trả về view với dữ liệu thông qua `compact()` hoặc array

**Template chuẩn:**
```php
public function index(Request $request)
{
    try {
        // 1. Khởi tạo query với relationships
        $query = Product::with('category');
        
        // 2. Xử lý search
        if ($request->has('search') && $request->search) {
            $searchTerm = $request->search;
            $query->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%");
        }
        
        // 3. Xử lý filter (nếu có)
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        // 4. Sắp xếp
        $query->orderBy('created_at', 'desc');
        
        // 5. Pagination
        $perPage = 10;
        $items = $query->paginate($perPage);
        
        // 6. Load dữ liệu bổ sung (categories, statuses, etc.)
        $categories = Category::all();
        
        // 7. Return view
        return view('dashboard.products.index', compact('items', 'categories'));
        
    } catch (\Exception $e) {
        // 8. Xử lý lỗi - trả về view với dữ liệu rỗng và thông báo lỗi
        return view('dashboard.products.index', [
            'items' => collect()->paginate(10),
            'categories' => collect(),
            'error' => 'Lỗi khi tải danh sách: ' . $e->getMessage(),
        ]);
    }
}
```

**Giải thích chi tiết:**
- `$request->has('search')`: Kiểm tra xem parameter 'search' có tồn tại không
- `$request->search`: Lấy giá trị của parameter 'search'
- `LIKE "%{$searchTerm}%"`: Tìm kiếm gần đúng (có chứa từ khóa)
- `with('category')`: Eager loading để tránh N+1 query problem
- `paginate($perPage)`: Tự động phân trang và tạo links pagination
- `collect()->paginate(10)`: Tạo collection rỗng với pagination khi có lỗi

### 4. METHOD STORE - TẠO MỚI

**Quy tắc:**
- Validate dữ liệu đầu vào trước khi xử lý
- Sử dụng `try-catch` để bắt exception
- Sử dụng Eloquent `create()` thay vì SQL thuần
- Redirect về trang phù hợp sau khi tạo thành công
- Sử dụng session flash để hiển thị thông báo

**Template chuẩn:**
```php
public function store(Request $request)
{
    // 1. Validate dữ liệu đầu vào
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|integer|exists:categories,category_id',
        'image_url' => 'nullable|url',
        'stock_quantity' => 'nullable|integer|min:0',
    ]);
    
    try {
        // 2. Tạo mới record sử dụng Eloquent
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
            'image_url' => $request->image_url,
            'stock_quantity' => $request->stock_quantity ?? 0,
        ]);
        
        // 3. Redirect với thông báo thành công
        return redirect()->route('dashboard.products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công!');
            
    } catch (\Exception $e) {
        // 4. Xử lý lỗi - redirect với thông báo lỗi
        return redirect()->route('dashboard.products.index')
            ->with('error', 'Lỗi khi tạo sản phẩm: ' . $e->getMessage());
    }
}
```

**Validation Rules thường dùng:**
- `required`: Trường bắt buộc phải có
- `nullable`: Trường có thể null
- `string`: Phải là chuỗi
- `max:255`: Độ dài tối đa 255 ký tự
- `min:8`: Độ dài tối thiểu 8 ký tự
- `numeric`: Phải là số
- `integer`: Phải là số nguyên
- `email`: Phải đúng định dạng email
- `url`: Phải đúng định dạng URL
- `unique:table,column`: Giá trị phải unique trong bảng
- `exists:table,column`: Giá trị phải tồn tại trong bảng
- `confirmed`: Phải match với trường `{field}_confirmation`
- `min:0`: Giá trị tối thiểu là 0
- `max:100`: Giá trị tối đa là 100

### 5. METHOD UPDATE - CẬP NHẬT

**Quy tắc:**
- Validate dữ liệu đầu vào
- Sử dụng `findOrFail()` để tìm record
- Update bằng Eloquent methods
- Xử lý exception khi không tìm thấy record

**Template chuẩn:**
```php
public function update(Request $request, $id)
{
    // 1. Validate
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric|min:0',
        'category_id' => 'required|integer|exists:categories,category_id',
    ]);
    
    try {
        // 2. Tìm record (tự động throw exception nếu không tìm thấy)
        $product = Product::findOrFail($id);
        
        // 3. Update
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'category_id' => $request->category_id,
        ]);
        
        // 4. Redirect với thông báo thành công
        return redirect()->route('dashboard.products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công!');
            
    } catch (\Exception $e) {
        // 5. Xử lý lỗi
        return redirect()->route('dashboard.products.index')
            ->with('error', 'Lỗi khi cập nhật sản phẩm: ' . $e->getMessage());
    }
}
```

### 6. METHOD DESTROY - XÓA

**Quy tắc:**
- Sử dụng `findOrFail()` để tìm record
- Kiểm tra ràng buộc dữ liệu trước khi xóa (nếu cần)
- Sử dụng `delete()` method
- Xử lý exception phù hợp

**Template chuẩn:**
```php
public function destroy($id)
{
    try {
        // 1. Tìm record
        $product = Product::findOrFail($id);
        
        // 2. Kiểm tra ràng buộc (nếu cần)
        if ($product->orderItems()->exists()) {
            return redirect()->route('dashboard.products.index')
                ->with('error', 'Không thể xóa sản phẩm đã có đơn hàng!');
        }
        
        // 3. Xóa
        $product->delete();
        
        // 4. Redirect với thông báo thành công
        return redirect()->route('dashboard.products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công!');
            
    } catch (\Exception $e) {
        // 5. Xử lý lỗi
        return redirect()->route('dashboard.products.index')
            ->with('error', 'Lỗi khi xóa sản phẩm: ' . $e->getMessage());
    }
}
```

### 7. AUTHENTICATION & AUTHORIZATION

**Quy tắc:**
- Kiểm tra authentication bằng `Auth::check()`
- Sử dụng `Auth::user()` để lấy user hiện tại
- Redirect dựa trên role của user
- Sử dụng custom methods từ Model để kiểm tra quyền

**Ví dụ từ AuthController:**
```php
public function login(Request $request)
{
    // 1. Validate
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);
    
    // 2. Tìm user
    $user = User::where('email', $request->email)->first();
    
    // 3. Kiểm tra credentials
    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput();
    }
    
    // 4. Đăng nhập
    Auth::login($user);
    
    // 5. Redirect dựa trên role
    if ($user->hasRole('admin') || $user->hasRole('manager')) {
        return redirect()->route('dashboard')
            ->with('success', 'Đăng nhập thành công!');
    } elseif ($user->hasRole('customer')) {
        return redirect()->route('products.index')
            ->with('success', 'Đăng nhập thành công!');
    } else {
        return redirect()->route('home')
            ->with('success', 'Đăng nhập thành công!');
    }
}
```

**Giải thích:**
- `Auth::check()`: Kiểm tra user đã đăng nhập chưa
- `Auth::user()`: Lấy thông tin user hiện tại
- `Auth::login($user)`: Đăng nhập user
- `Auth::logout()`: Đăng xuất user
- `Hash::check($plain, $hashed)`: So sánh password plain với hashed
- `Hash::make($password)`: Mã hóa password
- `back()`: Quay lại trang trước
- `withErrors()`: Gửi lỗi về view
- `withInput()`: Giữ lại dữ liệu đã nhập

---

## QUY TẮC CHUNG CHO MODELS

### 1. CẤU TRÚC MODEL

**Quy tắc:**
- Kế thừa từ `Model` hoặc `Authenticatable` (với User)
- Sử dụng các Traits phù hợp (HasFactory, Notifiable, HasApiTokens)
- Định nghĩa các property theo thứ tự: table, primaryKey, timestamps, fillable, hidden, casts
- Định nghĩa relationships
- Định nghĩa helper methods và business logic

**Template chuẩn:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    // 1. Traits
    use HasFactory;
    
    // 2. Table configuration
    protected $table = 'products';
    protected $primaryKey = 'product_id';
    public $timestamps = true;
    
    // 3. Mass assignment
    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'stock_quantity',
        'image_url',
    ];
    
    // 4. Hidden attributes (for serialization)
    protected $hidden = [
        'deleted_at',
    ];
    
    // 5. Type casting
    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'created_at' => 'datetime',
    ];
    
    // 6. Constants (nếu có)
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    
    // 7. Relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }
    
    // 8. Helper methods & Business logic
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0;
    }
    
    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock_quantity < $quantity) {
            return false;
        }
        
        $this->stock_quantity -= $quantity;
        return $this->save();
    }
}
```

### 2. TABLE CONFIGURATION

**Quy tắc:**
- Luôn định nghĩa `$table` nếu tên bảng không theo convention
- Định nghĩa `$primaryKey` nếu không phải `id`
- Sử dụng `public $timestamps = true/false` để bật/tắt timestamps

**Ví dụ:**
```php
// Tên bảng không theo convention (số nhiều của tên model)
protected $table = 'categories';

// Primary key không phải 'id'
protected $primaryKey = 'category_id';

// Bật timestamps (created_at, updated_at)
public $timestamps = true;

// Hoặc tắt timestamps
public $timestamps = false;
```

### 3. MASS ASSIGNMENT - FILLABLE

**Quy tắc:**
- Luôn định nghĩa `$fillable` để bảo vệ khỏi mass assignment vulnerability
- Chỉ thêm các field an toàn vào `$fillable`
- Không thêm các field nhạy cảm như `is_admin`, `role`, `verified`

**Ví dụ:**
```php
// ĐÚNG - Chỉ field an toàn
protected $fillable = [
    'name',
    'description',
    'price',
    'category_id',
    'stock_quantity',
    'image_url',
];

// SAI - Không nên thêm field nhạy cảm
protected $fillable = [
    'name',
    'email',
    'password',
    'is_admin', // ❌ Nguy hiểm!
    'verified', // ❌ Nguy hiểm!
];
```

### 4. HIDDEN ATTRIBUTES

**Quy tắc:**
- Ẩn các field nhạy cảm khi serialize model thành JSON/Array
- Thường dùng cho password, token, private data

**Ví dụ:**
```php
protected $hidden = [
    'password',
    'remember_token',
    'api_token',
];
```

### 5. TYPE CASTING

**Quy tắc:**
- Sử dụng `$casts` để tự động convert kiểu dữ liệu
- Giúp code sạch hơn và tránh lỗi type mismatch

**Các kiểu cast phổ biến:**
```php
protected $casts = [
    // Boolean
    'is_active' => 'boolean',
    
    // Integer
    'quantity' => 'integer',
    'user_id' => 'integer',
    
    // Float/Decimal
    'price' => 'decimal:2',
    'rating' => 'float',
    
    // Date/DateTime
    'created_at' => 'datetime',
    'published_at' => 'date',
    'expires_at' => 'datetime:Y-m-d H:i:s',
    
    // JSON
    'metadata' => 'array',
    'settings' => 'json',
    
    // Hashed (Laravel 9+)
    'password' => 'hashed',
];
```

### 6. RELATIONSHIPS - QUAN HỆ

#### 6.1. ONE TO ONE (1-1)
**hasOne & belongsTo**

```php
// User model - Mỗi user có 1 cart
public function cart()
{
    return $this->hasOne(Cart::class, 'user_id', 'id');
    //                    ^Model      ^FK         ^PK
}

// Cart model - Mỗi cart thuộc về 1 user
public function user()
{
    return $this->belongsTo(User::class, 'user_id', 'id');
    //                       ^Model      ^FK         ^PK
}
```

#### 6.2. ONE TO MANY (1-N)
**hasMany & belongsTo**

```php
// Category model - Mỗi category có nhiều products
public function products()
{
    return $this->hasMany(Product::class, 'category_id', 'category_id');
    //                    ^Model          ^FK             ^PK
}

// Product model - Mỗi product thuộc về 1 category
public function category()
{
    return $this->belongsTo(Category::class, 'category_id', 'category_id');
    //                       ^Model           ^FK             ^PK
}
```

#### 6.3. MANY TO MANY (N-N)
**belongsToMany**

```php
// User model - User có nhiều roles, Role có nhiều users
public function roles()
{
    return $this->belongsToMany(
        Role::class,        // Model liên kết
        'user_roles',       // Bảng trung gian
        'user_id',          // Foreign key của model hiện tại
        'role_id'           // Foreign key của model liên kết
    );
}

// Với pivot data bổ sung
public function roles()
{
    return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')
        ->withPivot('assigned_at', 'assigned_by')  // Thêm field từ bảng pivot
        ->withTimestamps();                         // Thêm created_at, updated_at
}
```

#### 6.4. HAS MANY THROUGH
**hasManyThrough**

```php
// Country model - Lấy tất cả posts của users trong country
public function posts()
{
    return $this->hasManyThrough(
        Post::class,    // Model cuối cùng
        User::class,    // Model trung gian
        'country_id',   // Foreign key trên model trung gian
        'user_id',      // Foreign key trên model cuối cùng
        'id',           // Local key của model hiện tại
        'id'            // Local key của model trung gian
    );
}
```

**Quy tắc khi định nghĩa relationships:**
- Tham số 1: Model class liên kết
- Tham số 2: Foreign key (trên bảng liên kết hoặc bảng hiện tại)
- Tham số 3: Local key/Primary key
- Đặt tên method số nhiều cho hasMany, belongsToMany
- Đặt tên method số ít cho hasOne, belongsTo

### 7. CONSTANTS - ĐỊNH NGHĨA HẰNG SỐ

**Quy tắc:**
- Sử dụng constants cho các giá trị cố định (status, type, role)
- Đặt tên constants theo UPPER_SNAKE_CASE
- Nhóm constants theo chức năng

**Ví dụ từ Order Model:**
```php
class Order extends Model
{
    // Định nghĩa các trạng thái hợp lệ
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    
    // Định nghĩa workflow chuyển trạng thái
    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];
}
```

### 8. HELPER METHODS & BUSINESS LOGIC

**Quy tắc:**
- Đặt business logic vào Model thay vì Controller
- Tạo helper methods cho các thao tác thường dùng
- Methods trả về boolean nên bắt đầu bằng `is`, `has`, `can`
- Methods thực hiện hành động nên dùng động từ

**Ví dụ từ User Model:**
```php
/**
 * Kiểm tra user có role cụ thể không
 */
public function hasRole(string $roleName): bool
{
    return $this->roles()->where('role_name', $roleName)->exists();
}

/**
 * Kiểm tra user có phải admin không
 */
public function isAdmin(): bool
{
    return $this->hasRole('admin');
}

/**
 * Kiểm tra user có phải manager không
 */
public function isManager(): bool
{
    return $this->hasRole('manager');
}

/**
 * Kiểm tra user có quyền truy cập dashboard
 */
public function canAccessDashboard(): bool
{
    return $this->isAdmin() || $this->isManager();
}
```

**Ví dụ từ Order Model:**
```php
/**
 * Kiểm tra xem có thể chuyển sang trạng thái mới không
 */
public function canTransitionTo(string $newStatus): bool
{
    $currentStatus = $this->status ?? self::STATUS_PENDING;
    
    if (!isset(self::STATUS_TRANSITIONS[$currentStatus])) {
        return false;
    }
    
    return in_array($newStatus, self::STATUS_TRANSITIONS[$currentStatus]);
}

/**
 * Chuyển sang trạng thái mới nếu hợp lệ
 */
public function transitionTo(string $newStatus): bool
{
    if (!$this->canTransitionTo($newStatus)) {
        return false;
    }
    
    $this->status = $newStatus;
    return $this->save();
}
```

**Ví dụ từ Cart Model:**
```php
/**
 * Tính tổng giá trị giỏ hàng
 */
public function totalPrice()
{
    return $this->items->sum(function ($item) {
        return $item->quantity * ($item->price ?? $item->product->price ?? 0);
    });
}

/**
 * Tính tổng số lượng items trong giỏ hàng
 */
public function totalItems()
{
    return $this->items->sum('quantity');
}
```

---

## QUY ƯỚC ĐẶT TÊN

### 1. CONTROLLERS

**Quy tắc:**
- Tên Controller: `PascalCase` + `Controller`
- Tên file: trùng với tên class
- Method names: `camelCase`
- CRUD methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

**Ví dụ:**
```php
// File: ProductController.php
class ProductController extends Controller
{
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($id) {}
    public function edit($id) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
    
    // Custom methods
    public function exportToCsv() {}
    public function importFromExcel() {}
}
```

### 2. MODELS

**Quy tắc:**
- Tên Model: `PascalCase`, số ít
- Tên file: trùng với tên class
- Tên bảng: `snake_case`, số nhiều (trừ khi override)
- Relationship methods: `camelCase`
- Helper methods: `camelCase`

**Ví dụ:**
```php
// File: Product.php
class Product extends Model
{
    protected $table = 'products'; // Số nhiều
    
    // Relationships - camelCase
    public function category() {}       // belongsTo - số ít
    public function orderItems() {}     // hasMany - số nhiều
    public function cartItems() {}      // hasMany - số nhiều
    
    // Helper methods - camelCase
    public function isInStock() {}
    public function hasDiscount() {}
    public function calculateFinalPrice() {}
}
```

### 3. BIẾN VÀ PARAMETERS

**Quy tắc:**
- Biến: `camelCase`
- Tham số: `camelCase`
- Biến tạm: tên ngắn gọn nhưng có ý nghĩa

**Ví dụ:**
```php
// ĐÚNG
$user = Auth::user();
$products = Product::all();
$searchTerm = $request->search;
$perPage = 10;

// SAI
$u = Auth::user();              // Quá ngắn
$ProductsList = Product::all(); // PascalCase sai
$search_term = $request->search; // snake_case sai
```

### 4. CONSTANTS

**Quy tắc:**
- Constants: `UPPER_SNAKE_CASE`
- Nhóm constants có liên quan

**Ví dụ:**
```php
const STATUS_PENDING = 'pending';
const STATUS_PROCESSING = 'processing';
const STATUS_SHIPPED = 'shipped';

const ROLE_ADMIN = 'admin';
const ROLE_MANAGER = 'manager';
const ROLE_CUSTOMER = 'customer';
```

---

## XỬ LÝ LỖI VÀ EXCEPTION

### 1. TRY-CATCH TRONG CONTROLLERS

**Quy tắc:**
- Luôn wrap code có thể gây lỗi trong `try-catch`
- Bắt `\Exception` hoặc specific exception types
- Log lỗi nếu cần debug
- Trả về thông báo lỗi user-friendly
- Không expose sensitive information trong error messages

**Template chuẩn:**
```php
public function store(Request $request)
{
    $request->validate([...]);
    
    try {
        // Code có thể gây lỗi
        $product = Product::create([...]);
        
        return redirect()->route('dashboard.products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công!');
            
    } catch (\Exception $e) {
        // Log lỗi (optional)
        \Log::error('Error creating product: ' . $e->getMessage());
        
        // Trả về thông báo lỗi
        return redirect()->route('dashboard.products.index')
            ->with('error', 'Lỗi khi tạo sản phẩm: ' . $e->getMessage());
    }
}
```

### 2. FINDORFAIL VS FIND

**Quy tắc:**
- Sử dụng `findOrFail()` khi expect record phải tồn tại
- Sử dụng `find()` khi cần xử lý trường hợp không tìm thấy

**Ví dụ:**
```php
// ĐÚNG - Sử dụng findOrFail
try {
    $product = Product::findOrFail($id);
    // Xử lý product
} catch (\Exception $e) {
    return redirect()->back()
        ->with('error', 'Không tìm thấy sản phẩm!');
}

// ĐÚNG - Sử dụng find với kiểm tra
$product = Product::find($id);
if (!$product) {
    return redirect()->back()
        ->with('error', 'Không tìm thấy sản phẩm!');
}
// Xử lý product
```

### 3. VALIDATION ERRORS

**Quy tắc:**
- Laravel tự động redirect back với validation errors
- Sử dụng `@error` directive trong Blade để hiển thị lỗi
- Custom error messages trong validation

**Ví dụ:**
```php
// Controller - Custom error messages
$request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8',
], [
    'email.required' => 'Email là bắt buộc',
    'email.email' => 'Email không đúng định dạng',
    'email.unique' => 'Email đã tồn tại',
    'password.required' => 'Mật khẩu là bắt buộc',
    'password.min' => 'Mật khẩu phải có ít nhất 8 ký tự',
]);

// Blade - Hiển thị lỗi
@error('email')
    <span class="error">{{ $message }}</span>
@enderror
```

---

## VALIDATION VÀ REQUEST HANDLING

### 1. INLINE VALIDATION

**Quy tắc:**
- Sử dụng cho validation đơn giản
- Đặt validation ngay đầu method
- Custom messages nếu cần

**Ví dụ:**
```php
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
        'password' => 'required|min:8|confirmed',
        'phone' => 'nullable|string|max:20',
    ]);
    
    // Xử lý tiếp...
}
```

### 2. FORM REQUEST (Nâng cao)

**Quy tắc:**
- Tạo Form Request class cho validation phức tạp
- Giữ Controller sạch hơn
- Tái sử dụng validation rules

**Ví dụ:**
```php
// app/Http/Requests/StoreProductRequest.php
class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,category_id',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'price.min' => 'Giá phải lớn hơn 0',
        ];
    }
}

// Controller
public function store(StoreProductRequest $request)
{
    // Validation đã được thực hiện tự động
    $product = Product::create($request->validated());
    // ...
}
```

### 3. REQUEST PARAMETERS

**Quy tắc:**
- Sử dụng `$request->input('key')` hoặc `$request->key`
- Kiểm tra parameter tồn tại: `$request->has('key')`
- Kiểm tra parameter có giá trị: `$request->filled('key')`
- Default value: `$request->input('key', 'default')`

**Ví dụ:**
```php
// Lấy parameter
$search = $request->input('search');
$search = $request->search; // Cách viết ngắn gọn

// Kiểm tra tồn tại
if ($request->has('search')) {
    // Parameter 'search' tồn tại (có thể rỗng)
}

if ($request->filled('search')) {
    // Parameter 'search' có giá trị (không rỗng)
}

// Default value
$perPage = $request->input('per_page', 10);

// Lấy nhiều parameters
$data = $request->only(['name', 'email', 'phone']);
$data = $request->except(['_token', '_method']);

// Lấy tất cả parameters
$allData = $request->all();
```

---

## DATABASE QUERIES VÀ ELOQUENT

### 1. QUERY BUILDER

**Quy tắc:**
- Sử dụng Eloquent thay vì raw SQL
- Chain methods để tạo query rõ ràng
- Sử dụng `get()`, `first()`, `find()`, `all()` phù hợp

**Ví dụ:**
```php
// ĐÚNG - Eloquent
$products = Product::where('category_id', $categoryId)
    ->where('price', '>', 100)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// SAI - Raw SQL (tránh sử dụng)
$products = DB::select('SELECT * FROM products WHERE category_id = ?', [$categoryId]);

// Các methods thường dùng
$product = Product::find($id);              // Tìm theo ID
$product = Product::findOrFail($id);        // Tìm hoặc throw exception
$product = Product::where(...)->first();    // Lấy record đầu tiên
$products = Product::where(...)->get();     // Lấy tất cả records matching
$products = Product::all();                 // Lấy tất cả records

// Đếm
$count = Product::where('category_id', $categoryId)->count();

// Kiểm tra tồn tại
$exists = Product::where('name', $name)->exists();

// Aggregate functions
$max = Product::max('price');
$min = Product::min('price');
$avg = Product::avg('price');
$sum = Product::sum('price');
```

### 2. EAGER LOADING - TRÁNH N+1 PROBLEM

**Quy tắc:**
- Sử dụng `with()` để load relationships trước
- Load nested relationships với dot notation
- Sử dụng `load()` để lazy eager loading

**Ví dụ:**
```php
// SAI - N+1 Problem
$products = Product::all();
foreach ($products as $product) {
    echo $product->category->name; // Query mới cho mỗi product!
}

// ĐÚNG - Eager Loading
$products = Product::with('category')->get();
foreach ($products as $product) {
    echo $product->category->name; // Không query thêm
}

// Load nhiều relationships
$orders = Order::with(['user', 'items', 'items.product'])->get();

// Conditional Eager Loading
$products = Product::with(['category' => function($query) {
    $query->where('status', 'active');
}])->get();

// Lazy Eager Loading
$products = Product::all();
$products->load('category'); // Load sau khi đã có products
```

### 3. PAGINATION

**Quy tắc:**
- Luôn sử dụng pagination cho danh sách dài
- Default 10-15 items per page
- Sử dụng `paginate()` thay vì `get()`

**Ví dụ:**
```php
// Controller
$products = Product::with('category')
    ->orderBy('created_at', 'desc')
    ->paginate(10);

return view('products.index', compact('products'));

// Blade View
@foreach($products as $product)
    <!-- Display product -->
@endforeach

{{ $products->links() }} <!-- Pagination links -->

// Custom pagination
$products = Product::paginate(
    $perPage = 15,
    $columns = ['*'],
    $pageName = 'page',
    $page = null
);

// Thêm query string vào pagination links
{{ $products->appends(['search' => $searchTerm])->links() }}
```

### 4. TRANSACTIONS

**Quy tắc:**
- Sử dụng transactions cho operations phức tạp
- Wrap trong `try-catch`
- Rollback nếu có lỗi

**Ví dụ:**
```php
use Illuminate\Support\Facades\DB;

public function createOrder(Request $request)
{
    DB::beginTransaction();
    
    try {
        // Tạo order
        $order = Order::create([...]);
        
        // Tạo order items
        foreach ($cartItems as $item) {
            OrderItem::create([...]);
            
            // Giảm stock
            $product = Product::findOrFail($item->product_id);
            $product->stock_quantity -= $item->quantity;
            $product->save();
        }
        
        // Xóa cart
        $cart->items()->delete();
        
        DB::commit();
        
        return redirect()->route('orders.show', $order->id)
            ->with('success', 'Đơn hàng đã được tạo thành công!');
            
    } catch (\Exception $e) {
        DB::rollBack();
        
        return redirect()->back()
            ->with('error', 'Lỗi khi tạo đơn hàng: ' . $e->getMessage());
    }
}
```

---

## COMMENT VÀ DOCUMENTATION

### 1. PHPDOC COMMENTS

**Quy tắc:**
- Mỗi method phải có PHPDoc comment
- Mô tả ngắn gọn, rõ ràng
- Ghi rõ parameters và return type nếu cần

**Template chuẩn:**
```php
/**
 * Display a listing of products for admin UI.
 *
 * @param Request $request
 * @return \Illuminate\View\View
 */
public function index(Request $request)
{
    // Implementation
}

/**
 * Store a newly created product.
 *
 * @param Request $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function store(Request $request)
{
    // Implementation
}

/**
 * Kiểm tra user có role cụ thể không
 *
 * @param string $roleName
 * @return bool
 */
public function hasRole(string $roleName): bool
{
    return $this->roles()->where('role_name', $roleName)->exists();
}
```

### 2. INLINE COMMENTS

**Quy tắc:**
- Comment giải thích LOGIC phức tạp, không comment code tự giải thích
- Comment bằng tiếng Việt hoặc tiếng Anh nhất quán
- Trong dự án này: comment tiếng Việt giải thích cho người mới

**Ví dụ tốt:**
```php
// Lấy danh sách products với relationship category
$query = Product::with('category');

// Nếu có search, filter dữ liệu
if ($request->has('search') && $request->search) {
    $searchTerm = $request->search;
    $query->where('name', 'LIKE', "%{$searchTerm}%");
}

// Pagination
$perPage = 10;
$products = $query->paginate($perPage);
```

**Ví dụ xấu:**
```php
// Tạo biến query
$query = Product::with('category'); // ❌ Không cần comment này

// Set x = 10
$x = 10; // ❌ Comment không có ý nghĩa

$products = $query->paginate($perPage); // Phân trang ❌ Quá rõ ràng rồi
```

### 3. COMMENT GIẢI THÍCH CODE

**Trong dự án này, mỗi dòng code quan trọng đều có comment giải thích chi tiết:**

```php
public function login(Request $request)
{
    // $request là đối tượng chứa các tham số truyền từ client qua URL đến controller
    $request->validate([
        'email' => 'required|email', // email bắt buộc và phải đúng định dạng email
        'password' => 'required', // password bắt buộc
    ]);
    
    // Tìm user theo email
    // User lấy từ model User, tìm user đầu tiên có email giống với email từ form
    // ham first() để lấy user đầu tiên nếu có nhiều user trùng email
    $user = User::where('email', $request->email)->first();
    
    // Kiểm tra credentials
    // !user nếu không tìm thấy user hoặc password không đúng
    // Hash::check để kiểm tra password nếu không trùng
    if (!$user || !Hash::check($request->password, $user->password)) {
        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ])->withInput(); // withInput để giữ lại dữ liệu người dùng đã nhập
    }
    
    // Đăng nhập user
    Auth::login($user); // Auth::login để đăng nhập user
    
    // Redirect dựa trên role
    if ($user->hasRole('admin')) {
        return redirect()->route('dashboard')
            ->with('success', 'Đăng nhập thành công!');
    }
    
    return redirect()->route('home')
        ->with('success', 'Đăng nhập thành công!');
}
```

**Quy tắc comment chi tiết:**
- Giải thích code phức tạp
- Giải thích các methods, facades Laravel
- Giải thích logic nghiệp vụ
- Hướng dẫn cho người mới học Laravel

---

## CHECKLIST KHI CODE

### ✅ CONTROLLER CHECKLIST

- [ ] Import đầy đủ các class cần thiết
- [ ] Các method có PHPDoc comment
- [ ] Sử dụng `try-catch` cho code có thể gây lỗi
- [ ] Validate dữ liệu đầu vào
- [ ] Sử dụng Eloquent thay vì raw SQL
- [ ] Eager loading relationships với `with()`
- [ ] Sử dụng pagination cho danh sách
- [ ] Xử lý search/filter đúng cách
- [ ] Redirect với thông báo success/error
- [ ] Code có comment giải thích logic

### ✅ MODEL CHECKLIST

- [ ] Sử dụng trait `HasFactory`
- [ ] Định nghĩa `$table`, `$primaryKey` nếu cần
- [ ] Định nghĩa `$fillable` đầy đủ và an toàn
- [ ] Định nghĩa `$hidden` cho field nhạy cảm
- [ ] Định nghĩa `$casts` cho type casting
- [ ] Định nghĩa relationships đúng
- [ ] Thêm constants cho fixed values
- [ ] Thêm helper methods cho business logic
- [ ] Method names rõ ràng, dễ hiểu

### ✅ SECURITY CHECKLIST

- [ ] Validate tất cả input từ user
- [ ] Không thêm field nhạy cảm vào `$fillable`
- [ ] Hash password trước khi lưu
- [ ] Sử dụng Eloquent để tránh SQL injection
- [ ] Check authorization trước khi xử lý
- [ ] Không expose sensitive info trong error messages

---

## KẾT LUẬN

Tài liệu này tổng hợp các quy tắc chung khi code Web Controllers và Models trong dự án Laravel webshop. Tuân thủ các quy tắc này sẽ giúp:

1. **Code nhất quán** - Dễ đọc, dễ maintain
2. **Tránh bugs** - Xử lý lỗi đầy đủ, validation chặt chẽ
3. **Security** - Bảo vệ khỏi các lỗ hổng phổ biến
4. **Performance** - Tránh N+1 problem, sử dụng pagination
5. **Maintainability** - Comment đầy đủ, code tự giải thích

**Nhớ:**
- Luôn validate input
- Luôn sử dụng try-catch
- Luôn eager loading relationships
- Luôn sử dụng pagination
- Luôn comment code phức tạp
- Luôn follow naming conventions

---

**Tài liệu được tạo dựa trên phân tích code thực tế của dự án webshop**
**Version: 1.0 - Date: 2025-10-16**
