# 📋 Migration Orders Table - Đã Hợp Nhất

## ✅ Đã hoàn thành:

Đã hợp nhất migration `add_status_to_orders_table` vào `create_orders_table`.

### Trước khi hợp nhất:
```
- 2025_09_27_063149_create_orders_table.php (tạo bảng orders)
- 2025_10_06_075847_add_status_to_orders_table.php (thêm cột status)
```

### Sau khi hợp nhất:
```
- 2025_09_27_063149_create_orders_table.php (tạo bảng orders + cột status)
```

## 📊 Cấu trúc bảng Orders sau khi hợp nhất:

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id('order_id');                    // ID đơn hàng (primary key)
    $table->unsignedBigInteger('user_id');    // ID người dùng (foreign key)
    $table->dateTime('order_date');           // Ngày đặt hàng
    $table->decimal('total_amount', 12, 2);   // Tổng tiền (VD: 99,999,999.99)
    $table->enum('status', [                  // ⭐ Trạng thái đơn hàng
        'pending',      // Chờ xử lý
        'processing',   // Đang xử lý
        'shipped',      // Đã gửi hàng
        'delivered',    // Đã giao hàng
        'cancelled'     // Đã hủy
    ])->default('pending');
    $table->timestamps();                     // created_at, updated_at
    
    $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('restrict');
});
```

## 🎯 Lợi ích của việc hợp nhất:

1. ✅ **Giảm số lượng migration files** - Dễ quản lý hơn
2. ✅ **Không cần chạy 2 migrations riêng** - Chỉ cần 1 lần migrate
3. ✅ **Code gọn gàng hơn** - Tất cả cấu trúc orders ở 1 file
4. ✅ **Dễ rollback** - Chỉ cần rollback 1 migration
5. ✅ **Tránh lỗi khi fresh migrate** - Không còn migration phụ thuộc

## 🚀 Cách sử dụng:

### Reset database (khuyến nghị):
```bash
# Cách này sẽ xóa toàn bộ database và tạo lại từ đầu
php artisan migrate:fresh --seed
```

### Hoặc nếu database đã có dữ liệu:
```bash
# Rollback về migration cũ
php artisan migrate:rollback --step=2

# Chạy lại migration mới
php artisan migrate
```

## 📝 Các trạng thái đơn hàng:

| Status | Mô tả | Màu sắc gợi ý |
|--------|-------|---------------|
| `pending` | Đơn hàng mới tạo, chờ xác nhận | 🟡 Vàng |
| `processing` | Đang chuẩn bị hàng | 🔵 Xanh dương |
| `shipped` | Đã giao cho đơn vị vận chuyển | 🟣 Tím |
| `delivered` | Đã giao hàng thành công | 🟢 Xanh lá |
| `cancelled` | Đơn hàng đã bị hủy | 🔴 Đỏ |

## 💡 Ví dụ sử dụng trong code:

```php
// Tạo đơn hàng mới
$order = Order::create([
    'user_id' => auth()->id(),
    'order_date' => now(),
    'total_amount' => 25990000,
    'status' => 'pending'  // Mặc định là 'pending'
]);

// Cập nhật trạng thái
$order->update(['status' => 'processing']);
$order->update(['status' => 'shipped']);
$order->update(['status' => 'delivered']);

// Kiểm tra trạng thái
if ($order->status === 'delivered') {
    // Đơn hàng đã được giao
}

// Filter theo trạng thái
$pendingOrders = Order::where('status', 'pending')->get();
$deliveredOrders = Order::where('status', 'delivered')->get();
```

## ⚠️ Lưu ý:

1. **Nếu database đã có dữ liệu cũ**, bạn cần:
   - Backup database trước
   - Chạy `migrate:fresh` để reset hoàn toàn
   - Hoặc tạo migration mới để update cấu trúc

2. **File đã xóa**: `2025_10_06_075847_add_status_to_orders_table.php`
   - File này không còn cần thiết nữa
   - Đã được hợp nhất vào `create_orders_table.php`

3. **Thứ tự migration**:
   - Users table phải được tạo trước Orders table
   - Do có foreign key `user_id` references `users.id`

---

**Cập nhật:** 16/10/2025  
**Trạng thái:** ✅ Hoàn thành
