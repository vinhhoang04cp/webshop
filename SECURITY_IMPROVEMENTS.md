# Cải Tiến Bảo Mật và Logic Nghiệp Vụ

## Tổng Quan
Tài liệu này mô tả các cải tiến về bảo mật và logic nghiệp vụ đã được thực hiện cho hệ thống webshop.

---

## 1. Bảo Mật và Phân Quyền

### 1.1. Middleware Admin
**Vấn đề:** Middleware admin bị comment, routes admin không được bảo vệ đúng cách.

**Giải pháp:**
- ✅ Thêm method `hasRole()` và `isAdmin()` vào User model (`app/Models/User.php`)
- ✅ Đăng ký middleware `EnsureUserIsAdmin` với alias `admin` trong `bootstrap/app.php`
- ✅ Áp dụng middleware `admin` cho các routes:
  - Categories management (POST, PUT, DELETE)
  - Products management (POST, PUT, DELETE)
  - Product details management (tất cả routes)
  - Inventory management (tất cả routes)

**Files đã thay đổi:**
```
app/Models/User.php
bootstrap/app.php
routes/api.php
```

### 1.2. Kiểm Tra Ownership - Cart
**Vấn đề:** User có thể truy cập và quản lý cart của người khác.

**Giải pháp:**
- ✅ `index()`: User thường chỉ xem cart của mình, Admin xem tất cả
- ✅ `show()`: Kiểm tra cart thuộc về user hiện tại
- ✅ `update()`: Kiểm tra quyền sở hữu trước khi cập nhật
- ✅ `destroy()`: Kiểm tra quyền sở hữu trước khi xóa
- ✅ `getUserId()`: Sử dụng authenticated user thay vì default user_id = 1

**Files đã thay đổi:**
```
app/Http/Controllers/Api/CartController.php
```

**Ví dụ response khi vi phạm:**
```json
{
  "status": false,
  "message": "Access denied. You can only access your own cart."
}
```

### 1.3. Kiểm Tra Ownership - Order
**Vấn đề:** User có thể truy cập order của người khác.

**Giải pháp:**
- ✅ `index()`: User thường chỉ xem order của mình, Admin xem tất cả
- ✅ `show()`: Kiểm tra order thuộc về user hiện tại
- ✅ `update()`: Kiểm tra quyền sở hữu trước khi cập nhật
- ✅ `destroy()`: Kiểm tra quyền sở hữu trước khi xóa

**Files đã thay đổi:**
```
app/Http/Controllers/Api/OrderController.php
```

---

## 2. Logic Nghiệp Vụ

### 2.1. Workflow Trạng Thái Đơn Hàng
**Vấn đề:** Thiếu validation trạng thái đơn hàng, có thể thay đổi trạng thái tùy ý.

**Giải pháp:**
- ✅ Tạo migration thêm cột `status` vào bảng `orders`
- ✅ Định nghĩa các trạng thái hợp lệ trong Order model:
  - `pending` (mặc định)
  - `processing`
  - `shipped`
  - `delivered`
  - `cancelled`

- ✅ Định nghĩa workflow chuyển trạng thái:
  ```
  pending → processing hoặc cancelled
  processing → shipped hoặc cancelled
  shipped → delivered
  delivered → (không thể chuyển)
  cancelled → (không thể chuyển)
  ```

- ✅ Thêm methods trong Order model:
  - `canTransitionTo(string $newStatus)`: Kiểm tra có thể chuyển trạng thái
  - `transitionTo(string $newStatus)`: Chuyển trạng thái nếu hợp lệ

- ✅ Validation trong OrderController:
  - Chỉ admin mới được thay đổi status
  - Kiểm tra workflow hợp lệ trước khi chuyển trạng thái
  - Trả về thông báo lỗi rõ ràng khi vi phạm

**Files đã thay đổi:**
```
database/migrations/2025_10_06_075847_add_status_to_orders_table.php
app/Models/Order.php
app/Http/Controllers/Api/OrderController.php
app/Http/Requests/OrderRequest.php
```

**Ví dụ response khi vi phạm workflow:**
```json
{
  "status": false,
  "message": "Cannot change status from 'delivered' to 'processing'. Invalid status transition.",
  "current_status": "delivered",
  "allowed_transitions": []
}
```

### 2.2. Quản Lý Tồn Kho
**Vấn đề:** 
- Không có cơ chế lock để tránh race condition
- Kiểm tra stock không đủ chặt chẽ

**Giải pháp:**
- ✅ Sử dụng `lockForUpdate()` trong `validateStock()`:
  - Lock record khi kiểm tra stock
  - Tránh 2 request đồng thời order cùng sản phẩm cuối cùng

- ✅ Sử dụng `lockForUpdate()` trong `updateStock()`:
  - Lock record trước khi update
  - Kiểm tra lại stock trước khi decrement
  - Đảm bảo stock không bị âm

**Files đã thay đổi:**
```
app/Http/Controllers/Api/OrderController.php
```

**Cải tiến:**
```php
// TRƯỚC
$product = Product::find($item['product_id']);
if ($product) {
    $product->decrement('stock_quantity', $item['quantity']);
}

// SAU
$product = Product::where('product_id', $item['product_id'])
    ->lockForUpdate()
    ->first();
if ($product && $product->stock_quantity >= $item['quantity']) {
    $product->decrement('stock_quantity', $item['quantity']);
}
```

---

## 3. Hướng Dẫn Migration và Seeding

### Chạy Migration
```bash
sail artisan migrate
# hoặc
php artisan migrate
```

Migration sẽ thêm cột `status` vào bảng `orders` với giá trị mặc định là `pending`.

### Chạy Seeder
```bash
sail artisan db:seed
# hoặc chỉ seed roles và user roles
sail artisan db:seed --class=RoleSeeder
sail artisan db:seed --class=UserRoleSeeder
```

**Lưu ý quan trọng:**
- Bảng `roles` sử dụng cột `role_name` (không phải `name`)
- User đầu tiên (user_id = 1) mặc định được gán role 'admin' qua UserRoleSeeder
- Các role có sẵn: admin, manager, customer, guest

### Rollback (nếu cần)
```bash
php artisan migrate:rollback
```

---

## 4. Testing

### Test Middleware Admin
1. Đăng nhập với user thường
2. Thử tạo/sửa/xóa category, product → Phải nhận 403 Forbidden
3. Đăng nhập với admin → Thành công

### Test Cart Ownership
1. User A tạo cart
2. User B thử xem cart của User A → 403 Forbidden
3. Admin xem cart của User A → Thành công

### Test Order Status Workflow
1. Tạo order mới (status = pending)
2. Admin update status → processing → Thành công
3. Admin update status → delivered (bỏ qua shipped) → 422 Error
4. User thường update status → 403 Forbidden

### Test Stock Management
1. Product có stock = 5
2. Tạo order với quantity = 3 → Thành công, stock = 2
3. Tạo order với quantity = 5 → 422 Error (insufficient stock)
4. Test concurrent requests với Apache Bench hoặc k6

---

## 5. Best Practices Đã Áp Dụng

1. **Principle of Least Privilege**: User chỉ truy cập tài nguyên của mình
2. **Defense in Depth**: Nhiều lớp bảo vệ (middleware + controller validation)
3. **Fail Secure**: Mặc định từ chối, chỉ cho phép khi đủ điều kiện
4. **Transaction Isolation**: Sử dụng database lock tránh race condition
5. **Clear Error Messages**: Thông báo lỗi rõ ràng giúp debug và user experience

---

## 6. Các Cải Tiến Tiếp Theo (Khuyến Nghị)

1. **Rate Limiting**: Thêm rate limit cho sensitive endpoints
2. **Audit Logging**: Log các thao tác quan trọng (create/update/delete order)
3. **Email Notifications**: Gửi email khi order thay đổi trạng thái
4. **Soft Delete**: Sử dụng soft delete thay vì hard delete
5. **API Versioning**: Version API để dễ maintain và backward compatibility
6. **Input Sanitization**: Thêm HTML purifier cho user input
7. **CSRF Protection**: Đảm bảo CSRF token cho web routes
8. **Two-Factor Authentication**: Thêm 2FA cho admin accounts

---

## Tác Giả
- Ngày tạo: 06/10/2025
- Version: 1.0
