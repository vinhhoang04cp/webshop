# Tài liệu Database - Webshop

## Tổng quan

Hệ thống database được thiết kế cho một ứng dụng thương mại điện tử (webshop) bán điện thoại, sử dụng MySQL làm hệ quản trị cơ sở dữ liệu. Database bao gồm 15 bảng chính, quản lý người dùng, sản phẩm, đơn hàng, giỏ hàng, kho và báo cáo doanh thu.

### Thông tin kết nối

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

---

## Sơ đồ quan hệ (ERD)

### Các nhóm chức năng chính:

1. **Quản lý người dùng**: `users`, `roles`, `user_roles`, `password_reset_tokens`, `sessions`
2. **Quản lý sản phẩm**: `products`, `product_details`, `categories`, `inventory`
3. **Quản lý giỏ hàng**: `carts`, `cart_items`
4. **Quản lý đơn hàng**: `orders`, `order_items`
5. **Báo cáo**: `revenue_reports`
6. **Hệ thống**: `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `personal_access_tokens`

---

## Chi tiết các bảng

### 1. Users - Bảng người dùng

**Tên bảng**: `users`

**Mô tả**: Lưu trữ thông tin người dùng của hệ thống (khách hàng và admin)

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `id` | BIGINT UNSIGNED | ID người dùng (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `name` | VARCHAR(255) | Tên người dùng | NOT NULL |
| `email` | VARCHAR(255) | Email đăng nhập | UNIQUE, NOT NULL |
| `email_verified_at` | TIMESTAMP | Thời điểm xác thực email | NULLABLE |
| `password` | VARCHAR(255) | Mật khẩu đã mã hóa | NOT NULL |
| `phone` | VARCHAR(20) | Số điện thoại | NULLABLE |
| `address` | VARCHAR(500) | Địa chỉ | NULLABLE |
| `remember_token` | VARCHAR(100) | Token ghi nhớ đăng nhập | NULLABLE |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `id`
- UNIQUE: `email`

**Quan hệ**:
- One-to-Many với `carts` (một user có một giỏ hàng)
- One-to-Many với `orders` (một user có nhiều đơn hàng)
- Many-to-Many với `roles` thông qua `user_roles`

---

### 2. Roles - Bảng vai trò

**Tên bảng**: `roles`

**Mô tả**: Định nghĩa các vai trò trong hệ thống (admin, customer, etc.)

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `role_id` | BIGINT UNSIGNED | ID vai trò (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `role_name` | VARCHAR(100) | Tên vai trò (key) | NOT NULL |
| `role_display_name` | VARCHAR(150) | Tên hiển thị vai trò | NOT NULL |
| `role_created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `role_updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `role_id`

**Quan hệ**:
- Many-to-Many với `users` thông qua `user_roles`

**Dữ liệu mẫu**:
- `admin` - Administrator
- `customer` - Customer

---

### 3. User Roles - Bảng phân quyền người dùng

**Tên bảng**: `user_roles`

**Mô tả**: Bảng trung gian liên kết người dùng với vai trò (Many-to-Many relationship)

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `role_id` | BIGINT UNSIGNED | ID vai trò | NOT NULL, FK |
| `user_id` | BIGINT UNSIGNED | ID người dùng | NOT NULL, FK |
| `assigned_at` | TIMESTAMP | Thời điểm gán vai trò | NULLABLE |

**Index**: 
- PRIMARY KEY: `role_id`, `user_id`
- FOREIGN KEY: `role_id` -> `roles(role_id)` ON DELETE RESTRICT
- FOREIGN KEY: `user_id` -> `users(id)` ON DELETE CASCADE

---

### 4. Categories - Bảng danh mục sản phẩm

**Tên bảng**: `categories`

**Mô tả**: Lưu trữ các danh mục sản phẩm (Samsung, iPhone, Xiaomi, v.v.)

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `category_id` | BIGINT UNSIGNED | ID danh mục (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `name` | VARCHAR(150) | Tên danh mục | NOT NULL |
| `description` | TEXT | Mô tả danh mục | NULLABLE |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `category_id`

**Quan hệ**:
- One-to-Many với `products` (một danh mục có nhiều sản phẩm)

---

### 5. Products - Bảng sản phẩm

**Tên bảng**: `products`

**Mô tả**: Lưu trữ thông tin sản phẩm điện thoại

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `product_id` | BIGINT UNSIGNED | ID sản phẩm (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `name` | VARCHAR(200) | Tên sản phẩm | NOT NULL |
| `description` | TEXT | Mô tả sản phẩm | NULLABLE |
| `price` | DECIMAL(10,2) | Giá bán | NOT NULL |
| `category_id` | BIGINT UNSIGNED | ID danh mục | NOT NULL, FK |
| `stock_quantity` | INT UNSIGNED | Số lượng tồn kho | NOT NULL |
| `image_url` | VARCHAR(2048) | URL hình ảnh sản phẩm | NULLABLE |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `product_id`
- FOREIGN KEY: `category_id` -> `categories(category_id)` ON DELETE RESTRICT

**Quan hệ**:
- Many-to-One với `categories`
- One-to-One với `inventory`
- One-to-One với `product_details`
- One-to-Many với `cart_items`
- One-to-Many với `order_items`

---

### 6. Product Details - Bảng thông số kỹ thuật sản phẩm

**Tên bảng**: `product_details`

**Mô tả**: Lưu trữ thông số kỹ thuật chi tiết của điện thoại

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `detail_id` | BIGINT UNSIGNED | ID thông số (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `product_id` | BIGINT UNSIGNED | ID sản phẩm | NOT NULL, FK |
| `color` | VARCHAR(50) | Màu sắc | NULLABLE |
| `storage` | VARCHAR(20) | Dung lượng bộ nhớ (128GB, 256GB, ...) | NULLABLE |
| `ram` | VARCHAR(20) | RAM (8GB, 12GB, ...) | NULLABLE |
| `screen_size` | VARCHAR(20) | Kích thước màn hình (6.7 inch) | NULLABLE |
| `chip` | VARCHAR(100) | Chip xử lý | NULLABLE |
| `battery` | VARCHAR(50) | Pin (mAh) | NULLABLE |
| `camera_main` | VARCHAR(100) | Camera chính | NULLABLE |
| `camera_front` | VARCHAR(100) | Camera trước | NULLABLE |
| `os` | VARCHAR(50) | Hệ điều hành | NULLABLE |
| `special_features` | TEXT | Tính năng đặc biệt | NULLABLE |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `detail_id`
- FOREIGN KEY: `product_id` -> `products(product_id)` ON DELETE CASCADE

**Quan hệ**:
- One-to-One với `products`

---

### 7. Inventory - Bảng quản lý kho

**Tên bảng**: `inventory`

**Mô tả**: Theo dõi xuất nhập tồn kho của sản phẩm

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `inventory_id` | BIGINT UNSIGNED | ID kho (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `product_id` | BIGINT UNSIGNED | ID sản phẩm | NOT NULL, FK |
| `stock_in` | INT UNSIGNED | Số lượng nhập kho | DEFAULT 0 |
| `stock_out` | INT UNSIGNED | Số lượng xuất kho | DEFAULT 0 |
| `current_stock` | INT | Tồn kho hiện tại | NOT NULL |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `inventory_id`
- UNIQUE: `product_id`
- FOREIGN KEY: `product_id` -> `products(product_id)` ON DELETE CASCADE

**Quan hệ**:
- One-to-One với `products`

---

### 8. Carts - Bảng giỏ hàng

**Tên bảng**: `carts`

**Mô tả**: Lưu trữ giỏ hàng của người dùng (mỗi user có 1 giỏ hàng)

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `cart_id` | BIGINT UNSIGNED | ID giỏ hàng (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `user_id` | BIGINT UNSIGNED | ID người dùng | NOT NULL, FK |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `cart_id`
- UNIQUE: `user_id`
- FOREIGN KEY: `user_id` -> `users(id)` ON DELETE CASCADE

**Quan hệ**:
- One-to-One với `users`
- One-to-Many với `cart_items`

---

### 9. Cart Items - Bảng chi tiết giỏ hàng

**Tên bảng**: `cart_items`

**Mô tả**: Lưu trữ các sản phẩm trong giỏ hàng

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `cart_item_id` | BIGINT UNSIGNED | ID mục giỏ hàng (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `cart_id` | BIGINT UNSIGNED | ID giỏ hàng | NOT NULL, FK |
| `product_id` | BIGINT UNSIGNED | ID sản phẩm | NOT NULL, FK |
| `quantity` | INT UNSIGNED | Số lượng | NOT NULL |
| `price` | DECIMAL(10,2) | Giá tại thời điểm thêm vào giỏ | DEFAULT 0 |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `cart_item_id`
- UNIQUE: `cart_id`, `product_id` (một sản phẩm chỉ xuất hiện 1 lần trong giỏ)
- FOREIGN KEY: `cart_id` -> `carts(cart_id)` ON DELETE CASCADE
- FOREIGN KEY: `product_id` -> `products(product_id)` ON DELETE RESTRICT

**Quan hệ**:
- Many-to-One với `carts`
- Many-to-One với `products`

---

### 10. Orders - Bảng đơn hàng

**Tên bảng**: `orders`

**Mô tả**: Lưu trữ thông tin đơn hàng của khách hàng

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `order_id` | BIGINT UNSIGNED | ID đơn hàng (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `user_id` | BIGINT UNSIGNED | ID người đặt hàng | NOT NULL, FK |
| `shipping_name` | VARCHAR(255) | Tên người nhận | NULLABLE |
| `shipping_phone` | VARCHAR(20) | Số điện thoại người nhận | NULLABLE |
| `shipping_address` | TEXT | Địa chỉ giao hàng | NULLABLE |
| `note` | TEXT | Ghi chú đơn hàng | NULLABLE |
| `order_date` | DATETIME | Ngày đặt hàng | NOT NULL |
| `total_amount` | DECIMAL(12,2) | Tổng tiền đơn hàng | NOT NULL |
| `status` | ENUM | Trạng thái đơn hàng | DEFAULT 'pending' |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Giá trị ENUM cho status**:
- `pending` - Chờ xử lý
- `processing` - Đang xử lý
- `shipped` - Đã giao vận chuyển
- `delivered` - Đã giao hàng
- `cancelled` - Đã hủy

**Index**: 
- PRIMARY KEY: `order_id`
- FOREIGN KEY: `user_id` -> `users(id)` ON DELETE RESTRICT

**Quan hệ**:
- Many-to-One với `users`
- One-to-Many với `order_items`

---

### 11. Order Items - Bảng chi tiết đơn hàng

**Tên bảng**: `order_items`

**Mô tả**: Lưu trữ các sản phẩm trong đơn hàng

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `order_item_id` | BIGINT UNSIGNED | ID mục đơn hàng (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `order_id` | BIGINT UNSIGNED | ID đơn hàng | NOT NULL, FK |
| `product_id` | BIGINT UNSIGNED | ID sản phẩm | NOT NULL, FK |
| `quantity` | INT UNSIGNED | Số lượng | NOT NULL |
| `price` | DECIMAL(10,2) | Giá tại thời điểm đặt hàng | NOT NULL |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `order_item_id`
- FOREIGN KEY: `order_id` -> `orders(order_id)` ON DELETE CASCADE
- FOREIGN KEY: `product_id` -> `products(product_id)` ON DELETE RESTRICT

**Quan hệ**:
- Many-to-One với `orders`
- Many-to-One với `products`

---

### 12. Revenue Reports - Bảng báo cáo doanh thu

**Tên bảng**: `revenue_reports`

**Mô tả**: Lưu trữ báo cáo doanh thu theo ngày

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `report_id` | BIGINT UNSIGNED | ID báo cáo (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `date` | DATE | Ngày báo cáo | UNIQUE, NOT NULL |
| `total_orders` | INT UNSIGNED | Tổng số đơn hàng | NOT NULL |
| `total_revenue` | DECIMAL(14,2) | Tổng doanh thu | NOT NULL |
| `total_profit` | DECIMAL(14,2) | Tổng lợi nhuận | NOT NULL |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `report_id`
- UNIQUE: `date`

---

### 13. Password Reset Tokens - Bảng token đặt lại mật khẩu

**Tên bảng**: `password_reset_tokens`

**Mô tả**: Lưu trữ token để đặt lại mật khẩu

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `email` | VARCHAR(255) | Email người dùng (Primary Key) | NOT NULL |
| `token` | VARCHAR(255) | Token đặt lại mật khẩu | NOT NULL |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |

**Index**: 
- PRIMARY KEY: `email`

---

### 14. Sessions - Bảng phiên làm việc

**Tên bảng**: `sessions`

**Mô tả**: Lưu trữ thông tin phiên làm việc của người dùng

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `id` | VARCHAR(255) | ID phiên (Primary Key) | NOT NULL |
| `user_id` | BIGINT UNSIGNED | ID người dùng | NULLABLE, INDEX |
| `ip_address` | VARCHAR(45) | Địa chỉ IP | NULLABLE |
| `user_agent` | TEXT | User agent của trình duyệt | NULLABLE |
| `payload` | LONGTEXT | Dữ liệu phiên | NOT NULL |
| `last_activity` | INT | Thời gian hoạt động cuối | INDEX, NOT NULL |

**Index**: 
- PRIMARY KEY: `id`
- INDEX: `user_id`, `last_activity`

---

### 15. Personal Access Tokens - Bảng token truy cập API

**Tên bảng**: `personal_access_tokens`

**Mô tả**: Lưu trữ token để xác thực API (Laravel Sanctum)

| Tên cột | Kiểu dữ liệu | Mô tả | Ràng buộc |
|---------|-------------|-------|-----------|
| `id` | BIGINT UNSIGNED | ID token (Primary Key) | AUTO_INCREMENT, NOT NULL |
| `tokenable_type` | VARCHAR(255) | Loại model sở hữu token | NOT NULL |
| `tokenable_id` | BIGINT UNSIGNED | ID của model sở hữu token | NOT NULL |
| `name` | VARCHAR(255) | Tên token | NOT NULL |
| `token` | VARCHAR(64) | Token (hashed) | UNIQUE, NOT NULL |
| `abilities` | TEXT | Quyền của token | NULLABLE |
| `last_used_at` | TIMESTAMP | Thời điểm sử dụng cuối | NULLABLE |
| `expires_at` | TIMESTAMP | Thời điểm hết hạn | NULLABLE |
| `created_at` | TIMESTAMP | Thời điểm tạo | NULLABLE |
| `updated_at` | TIMESTAMP | Thời điểm cập nhật | NULLABLE |

**Index**: 
- PRIMARY KEY: `id`
- UNIQUE: `token`
- INDEX: `tokenable_type`, `tokenable_id`

---

## Sơ đồ quan hệ chi tiết

### Quan hệ chính:

```
users (1) -------- (1) carts
users (1) -------- (n) orders
users (n) -------- (n) roles [through user_roles]

categories (1) -------- (n) products

products (1) -------- (1) product_details
products (1) -------- (1) inventory
products (1) -------- (n) cart_items
products (1) -------- (n) order_items

carts (1) -------- (n) cart_items

orders (1) -------- (n) order_items
```

---

## Quy tắc xóa (ON DELETE)

| Bảng cha | Bảng con | Hành động |
|----------|----------|-----------|
| users | carts | CASCADE (xóa user → xóa giỏ hàng) |
| users | user_roles | CASCADE (xóa user → xóa phân quyền) |
| users | orders | RESTRICT (không cho xóa user có đơn hàng) |
| carts | cart_items | CASCADE (xóa giỏ → xóa items) |
| categories | products | RESTRICT (không cho xóa danh mục có sản phẩm) |
| products | product_details | CASCADE (xóa sản phẩm → xóa thông số) |
| products | inventory | CASCADE (xóa sản phẩm → xóa kho) |
| products | cart_items | RESTRICT (không cho xóa sản phẩm trong giỏ) |
| products | order_items | RESTRICT (không cho xóa sản phẩm đã bán) |
| orders | order_items | CASCADE (xóa đơn → xóa items) |
| roles | user_roles | RESTRICT (không cho xóa role đang dùng) |

---

## Migration và Seeding

### Chạy migration

```bash
# Chạy tất cả migrations
php artisan migrate

# Chạy lại từ đầu (cảnh báo: xóa toàn bộ dữ liệu)
php artisan migrate:fresh

# Rollback migration cuối
php artisan migrate:rollback

# Rollback tất cả
php artisan migrate:reset
```

### Chạy seeding

```bash
# Chạy tất cả seeders
php artisan db:seed

# Chạy migrate + seed
php artisan migrate:fresh --seed

# Chạy seeder cụ thể
php artisan db:seed --class=ProductSeeder
```

### Thứ tự seeders (theo DatabaseSeeder.php)

1. RoleSeeder - Tạo vai trò
2. AdminUserSeeder - Tạo admin
3. CustomerUserSeeder - Tạo khách hàng mẫu
4. UserRoleSeeder - Phân quyền
5. CategorySeeder - Tạo danh mục
6. ProductSeeder - Tạo sản phẩm
7. ProductDetailSeeder - Tạo thông số kỹ thuật
8. InventorySeeder - Tạo dữ liệu kho
9. CartSeeder - Tạo giỏ hàng
10. CartItemSeeder - Tạo items trong giỏ
11. OrderSeeder - Tạo đơn hàng
12. OrderItemSeeder - Tạo items trong đơn
13. RevenueReportSeeder - Tạo báo cáo doanh thu

---

## Indexes và Performance

### Indexes quan trọng

- **users**: `email` (UNIQUE) - để login nhanh
- **products**: `category_id` - để lọc theo danh mục
- **cart_items**: `(cart_id, product_id)` (UNIQUE) - tránh trùng lặp
- **order_items**: `order_id`, `product_id` - để truy vấn nhanh
- **revenue_reports**: `date` (UNIQUE) - để query theo ngày
- **personal_access_tokens**: `token` (UNIQUE) - để xác thực API

### Tối ưu hóa

- Sử dụng `DECIMAL` cho tiền tệ để tránh sai số làm tròn
- Sử dụng `ENUM` cho status để giới hạn giá trị
- Đặt index trên các foreign key
- Sử dụng `RESTRICT` để bảo vệ dữ liệu quan trọng
- Sử dụng `CASCADE` để tự động dọn dẹp dữ liệu liên quan

---

## Lưu ý quan trọng

### Bảo mật

1. **Mật khẩu**: Luôn được hash bằng bcrypt (BCRYPT_ROUNDS=12)
2. **Token**: Sử dụng Laravel Sanctum cho API authentication
3. **Sessions**: Lưu trong database để quản lý tốt hơn
4. **Email verification**: Có hỗ trợ xác thực email

### Quy tắc nghiệp vụ

1. **Giỏ hàng**: Mỗi user chỉ có 1 giỏ hàng (UNIQUE constraint)
2. **Sản phẩm trong giỏ**: Không được trùng lặp (UNIQUE constraint)
3. **Xóa user**: Không được phép nếu có đơn hàng (RESTRICT)
4. **Xóa sản phẩm**: Không được phép nếu có trong giỏ/đơn hàng (RESTRICT)
5. **Giá**: Được lưu tại thời điểm thêm vào giỏ/đặt hàng để tránh thay đổi

### Kiểu dữ liệu

- **VARCHAR(255)**: Độ dài mặc định cho string
- **DECIMAL(10,2)**: Cho giá sản phẩm (tối đa 99,999,999.99)
- **DECIMAL(12,2)**: Cho tổng tiền đơn hàng (tối đa 9,999,999,999.99)
- **DECIMAL(14,2)**: Cho doanh thu (tối đa 999,999,999,999.99)
- **TEXT**: Cho mô tả, ghi chú dài
- **TIMESTAMP**: Tự động quản lý bởi Laravel

---

## Tài liệu tham khảo

- [Laravel Migrations](https://laravel.com/docs/11.x/migrations)
- [Laravel Eloquent ORM](https://laravel.com/docs/11.x/eloquent)
- [Laravel Database Seeding](https://laravel.com/docs/11.x/seeding)
- [MySQL Data Types](https://dev.mysql.com/doc/refman/8.0/en/data-types.html)

---

**Cập nhật lần cuối**: 19/10/2025
