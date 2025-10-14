# Tích hợp Database vào WebShop - Tài liệu hoàn chỉnh

## 📋 Tổng quan

Đã hoàn thành việc tích hợp dữ liệu thực từ database vào tất cả các trang của WebShop. Hệ thống hiện có thể:
- ✅ Lấy và hiển thị sản phẩm từ database
- ✅ Lấy và hiển thị danh mục từ database
- ✅ Quản lý giỏ hàng với dữ liệu thực
- ✅ Tìm kiếm và lọc sản phẩm
- ✅ Thêm/xóa/cập nhật giỏ hàng

---

## 🗂️ Files đã tạo/cập nhật

### 1. Controllers

#### `app/Http/Controllers/Web/HomeController.php`
- **Chức năng**: Controller cho trang chủ
- **Methods**:
  - `index()`: Lấy categories, sản phẩm nổi bật, sản phẩm mới từ DB

#### `app/Http/Controllers/Web/CustomerProductController.php`
- **Chức năng**: Controller cho các trang sản phẩm
- **Methods**:
  - `index()`: Danh sách sản phẩm với pagination, filter, sort
  - `show($id)`: Chi tiết sản phẩm
  - `search()`: Tìm kiếm sản phẩm
  - `category($id)`: Sản phẩm theo danh mục

#### `app/Http/Controllers/Web/CustomerCartController.php`
- **Chức năng**: Controller cho giỏ hàng
- **Methods**:
  - `index()`: Hiển thị giỏ hàng
  - `add($productId)`: Thêm sản phẩm vào giỏ (API)
  - `update($cartItemId)`: Cập nhật số lượng (API)
  - `remove($cartItemId)`: Xóa sản phẩm (API)
  - `clear()`: Xóa toàn bộ giỏ hàng

### 2. View Composer

#### `app/View/Composers/NavigationComposer.php`
- **Chức năng**: Share dữ liệu categories và cartCount cho tất cả views
- **Dữ liệu share**:
  - `$categories`: Danh sách categories với số lượng sản phẩm
  - `$cartCount`: Số lượng sản phẩm trong giỏ hàng

#### Đăng ký trong `app/Providers/AppServiceProvider.php`
```php
View::composer([
    'layouts.customer',
    'home',
    'products.*',
    'cart.*',
], NavigationComposer::class);
```

### 3. Routes

#### `routes/web.php` - Đã cập nhật
```php
// Trang chủ
GET /                           → HomeController@index

// Sản phẩm
GET /products                   → CustomerProductController@index
GET /products/search            → CustomerProductController@search
GET /product/{id}              → CustomerProductController@show
GET /category/{id}             → CustomerProductController@category

// Giỏ hàng
GET /cart                       → CustomerCartController@index
POST /cart/add/{productId}      → CustomerCartController@add
PUT /cart/update/{cartItemId}   → CustomerCartController@update
DELETE /cart/remove/{cartItemId}→ CustomerCartController@remove
DELETE /cart/clear              → CustomerCartController@clear
```

### 4. Views

#### `resources/views/home.blade.php` ✅ Cập nhật
- Hiển thị categories thực từ DB
- Hiển thị featured products (8 sản phẩm random)
- Hiển thị new products (8 sản phẩm mới nhất)
- Hàm addToCart() tích hợp API

#### `resources/views/products/index.blade.php` ✅ Cập nhật
- Danh sách sản phẩm với pagination
- Filter theo category (radio buttons)
- Filter theo giá (checkboxes - cần implement)
- Sort products (mới nhất, giá, tên)
- Tìm kiếm sản phẩm
- Hiển thị "Không có sản phẩm" nếu rỗng

#### `resources/views/products/show.blade.php` ✅ Tạo mới
- Chi tiết sản phẩm đầy đủ
- Hiển thị hình ảnh, giá, mô tả
- Thông tin chi tiết (màu sắc, kích thước, v.v.)
- Số lượng tồn kho
- Chọn số lượng mua
- Thêm vào giỏ hàng
- Sản phẩm liên quan (cùng danh mục)

#### `resources/views/products/category.blade.php` ✅ Tạo mới
- Trang danh mục với header đẹp
- Danh sách sản phẩm trong danh mục
- Sort và pagination
- Add to cart

#### `resources/views/cart/index.blade.php` ✅ Cập nhật
- Hiển thị cart items từ DB
- Cập nhật số lượng (AJAX)
- Xóa sản phẩm (AJAX)
- Tính tổng tiền tự động
- Phí vận chuyển (miễn phí nếu >500k)
- Nút xóa toàn bộ giỏ hàng
- Hiển thị "Giỏ hàng trống" nếu không có sản phẩm

#### `resources/views/layouts/customer.blade.php`
- Dropdown categories từ DB
- Cart count badge từ DB
- Đăng nhập/đăng ký/đăng xuất

---

## 🔄 Luồng dữ liệu

### 1. Trang chủ (Home)
```
User → HomeController@index
       ↓
    - Query categories (withCount products)
    - Query featured products (random 8)
    - Query new products (latest 8)
    - Count cart items
       ↓
    View: home.blade.php
```

### 2. Danh sách sản phẩm
```
User → CustomerProductController@index
       ↓
    - Query products with filters:
      * Search by name/description
      * Filter by category
      * Filter by price range
      * Sort (latest, price, name)
    - Paginate (12 per page)
       ↓
    View: products/index.blade.php
```

### 3. Chi tiết sản phẩm
```
User → CustomerProductController@show($id)
       ↓
    - Find product by ID
    - Load relationships (category, details, inventory)
    - Query related products (same category)
       ↓
    View: products/show.blade.php
```

### 4. Giỏ hàng
```
User → CustomerCartController@index
       ↓
    - Get user's cart
    - Load cart items with products
    - Calculate totals
       ↓
    View: cart/index.blade.php
```

### 5. Thêm vào giỏ (AJAX)
```
User clicks "Thêm vào giỏ"
       ↓
    POST /cart/add/{productId}
       ↓
    CustomerCartController@add
       ↓
    - Check authentication
    - Get/Create cart
    - Check if product exists in cart
      * If yes: Increase quantity
      * If no: Create new cart item
    - Update total amount
       ↓
    Return JSON response
       ↓
    JavaScript: Reload page or update UI
```

---

## 🎨 Features triển khai

### ✅ Trang chủ
- [x] Hiển thị categories từ DB
- [x] Sản phẩm nổi bật (random)
- [x] Sản phẩm mới nhất
- [x] Thêm vào giỏ hàng

### ✅ Danh sách sản phẩm
- [x] Pagination (12 sản phẩm/trang)
- [x] Tìm kiếm theo tên/mô tả
- [x] Lọc theo danh mục
- [x] Sắp xếp (mới nhất, giá, tên)
- [x] Hiển thị tổng số sản phẩm
- [x] Empty state (không có sản phẩm)

### ✅ Chi tiết sản phẩm
- [x] Thông tin đầy đủ
- [x] Số lượng tồn kho
- [x] Chọn số lượng
- [x] Thêm vào giỏ với số lượng tùy chỉnh
- [x] Sản phẩm liên quan

### ✅ Giỏ hàng
- [x] Hiển thị danh sách sản phẩm
- [x] Cập nhật số lượng (AJAX)
- [x] Xóa sản phẩm (AJAX)
- [x] Tính tổng tự động
- [x] Phí vận chuyển động
- [x] Xóa toàn bộ giỏ hàng

### ✅ Navigation
- [x] Categories dropdown động
- [x] Cart count badge
- [x] Đăng nhập/đăng xuất

---

## 🔧 Cấu hình Database

### Models sử dụng

#### Product
- `product_id`: Primary key
- `name`: Tên sản phẩm
- `description`: Mô tả
- `price`: Giá
- `category_id`: Foreign key
- `image_url`: URL hình ảnh
- Relationships: `category`, `details`, `inventory`

#### Category
- `category_id`: Primary key
- `name`: Tên danh mục
- `description`: Mô tả
- Relationships: `products`

#### Cart
- `cart_id`: Primary key
- `user_id`: Foreign key
- `total_amount`: Tổng tiền
- Relationships: `user`, `items`, `products`

#### CartItem
- `id`: Primary key
- `cart_id`: Foreign key
- `product_id`: Foreign key
- `quantity`: Số lượng
- `price`: Giá tại thời điểm thêm
- Relationships: `cart`, `product`

---

## 🚀 Cách sử dụng

### 1. Đảm bảo database đã có dữ liệu
```bash
# Chạy migration
php artisan migrate

# Chạy seeder (nếu có)
php artisan db:seed
```

### 2. Khởi động server
```bash
php artisan serve
```

### 3. Truy cập các trang

- **Trang chủ**: `http://localhost:8000/`
- **Danh sách sản phẩm**: `http://localhost:8000/products`
- **Tìm kiếm**: `http://localhost:8000/products?q=keyword`
- **Lọc theo danh mục**: `http://localhost:8000/category/1`
- **Chi tiết sản phẩm**: `http://localhost:8000/product/1`
- **Giỏ hàng**: `http://localhost:8000/cart`

---

## 📝 API Endpoints (AJAX)

### Thêm vào giỏ hàng
```javascript
POST /cart/add/{productId}
Content-Type: application/json
X-CSRF-TOKEN: {token}

Body: {
    "quantity": 1
}

Response: {
    "success": true,
    "message": "Đã thêm sản phẩm vào giỏ hàng!",
    "cartCount": 5
}
```

### Cập nhật số lượng
```javascript
PUT /cart/update/{cartItemId}
Content-Type: application/json
X-CSRF-TOKEN: {token}

Body: {
    "quantity": 3
}

Response: {
    "success": true,
    "message": "Đã cập nhật giỏ hàng!",
    "itemTotal": 1500000,
    "cartTotal": 5000000
}
```

### Xóa sản phẩm
```javascript
DELETE /cart/remove/{cartItemId}
X-CSRF-TOKEN: {token}

Response: {
    "success": true,
    "message": "Đã xóa sản phẩm khỏi giỏ hàng!",
    "cartTotal": 3500000,
    "cartCount": 3
}
```

---

## 🔐 Authentication

- Tất cả cart operations yêu cầu đăng nhập
- Nếu chưa đăng nhập, redirect về `/login`
- Session-based authentication với Laravel Sanctum

---

## 🎯 Các tính năng có thể mở rộng

### Chưa implement (có thể thêm sau)
- [ ] Filter theo khoảng giá (UI đã có, cần thêm logic)
- [ ] Reviews và ratings
- [ ] Wishlist
- [ ] Product images gallery
- [ ] Stock validation khi thêm vào giỏ
- [ ] Checkout process
- [ ] Order management
- [ ] Email notifications
- [ ] Product variants (size, color selection)
- [ ] Quick view modal

---

## ⚠️ Lưu ý quan trọng

1. **CSRF Token**: Tất cả AJAX requests phải có CSRF token
2. **Authentication**: Cart operations yêu cầu đăng nhập
3. **Validation**: Server-side validation trong controllers
4. **Error Handling**: Try-catch và transactions trong cart operations
5. **Images**: Nếu `image_url` null, hiển thị placeholder
6. **Empty States**: Xử lý các trường hợp không có dữ liệu

---

## 🐛 Troubleshooting

### Lỗi "CSRF token mismatch"
- Đảm bảo có `<meta name="csrf-token">` trong layout
- Check JavaScript lấy đúng token

### Giỏ hàng không cập nhật
- Check console.log trong browser
- Verify routes trong `web.php`
- Check authentication middleware

### Sản phẩm không hiển thị
- Kiểm tra database có dữ liệu
- Check relationship trong models
- Verify queries trong controllers

---

## 📊 Database Schema cần thiết

```sql
-- Products table phải có
products (
    product_id,
    name,
    description,
    price,
    category_id,
    image_url,
    created_at,
    updated_at
)

-- Categories table
categories (
    category_id,
    name,
    description,
    created_at,
    updated_at
)

-- Carts table
carts (
    cart_id,
    user_id,
    total_amount,
    created_at,
    updated_at
)

-- Cart items table
cart_items (
    id,
    cart_id,
    product_id,
    quantity,
    price,
    created_at,
    updated_at
)
```

---

## ✅ Kết luận

Hệ thống đã được tích hợp hoàn chỉnh với database:
- ✅ Tất cả dữ liệu lấy từ database thực
- ✅ CRUD operations cho giỏ hàng
- ✅ Search và filter products
- ✅ Pagination
- ✅ AJAX operations
- ✅ Error handling
- ✅ Authentication check

Hệ thống sẵn sàng để sử dụng và có thể mở rộng thêm nhiều tính năng!
