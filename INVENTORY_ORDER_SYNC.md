# Tự động Cập nhật Inventory khi Hoàn thành Đơn hàng

## 📋 Tổng quan

Hệ thống tự động cập nhật **inventory (tồn kho)** khi:
- ✅ Đơn hàng được **giao thành công** (status = `delivered`)
- ✅ Đơn hàng bị **hủy** (status = `cancelled`)

## 🎯 Mục đích

Đảm bảo số lượng tồn kho luôn chính xác và đồng bộ với trạng thái đơn hàng:
- Khi giao hàng thành công → Giảm tồn kho
- Khi hủy đơn hàng → Hoàn trả tồn kho

## ✨ Tính năng

### 1. Tự động giảm inventory khi đơn hàng hoàn thành

**Khi admin cập nhật trạng thái đơn hàng sang "Đã giao hàng":**

```
Order Status: shipped → delivered
    ↓
Hệ thống tự động:
    ↓
Với mỗi sản phẩm trong đơn hàng:
├─ products.stock_quantity -= order_item.quantity
├─ inventory.stock_out += order_item.quantity
└─ inventory.current_stock -= order_item.quantity
```

**Ví dụ cụ thể:**

```
Đơn hàng #123 có:
- iPhone 15 Pro: 2 cái
- MacBook Pro: 1 cái

Trước khi giao hàng:
┌─────────────────┬─────────────┬───────────┬────────────┬───────────────┐
│ Sản phẩm        │ stock_qty   │ stock_in  │ stock_out  │ current_stock │
├─────────────────┼─────────────┼───────────┼────────────┼───────────────┤
│ iPhone 15 Pro   │     50      │    100    │     50     │      50       │
│ MacBook Pro     │     20      │     50    │     30     │      20       │
└─────────────────┴─────────────┴───────────┴────────────┴───────────────┘

Admin đánh dấu: "Đã giao hàng" ✅

Sau khi giao hàng (TỰ ĐỘNG):
┌─────────────────┬─────────────┬───────────┬────────────┬───────────────┐
│ Sản phẩm        │ stock_qty   │ stock_in  │ stock_out  │ current_stock │
├─────────────────┼─────────────┼───────────┼────────────┼───────────────┤
│ iPhone 15 Pro   │  50 → 48    │    100    │  50 → 52   │   50 → 48     │
│                 │    (-2)     │           │    (+2)    │     (-2)      │
├─────────────────┼─────────────┼───────────┼────────────┼───────────────┤
│ MacBook Pro     │  20 → 19    │     50    │  30 → 31   │   20 → 19     │
│                 │    (-1)     │           │    (+1)    │     (-1)      │
└─────────────────┴─────────────┴───────────┴────────────┴───────────────┘
```

### 2. Tự động hoàn trả inventory khi đơn hàng bị hủy

**Khi admin hủy đơn hàng:**

```
Order Status: pending/processing → cancelled
    ↓
Hệ thống tự động:
    ↓
Với mỗi sản phẩm trong đơn hàng:
├─ products.stock_quantity += order_item.quantity
├─ inventory.stock_out -= order_item.quantity (nếu có)
└─ inventory.current_stock += order_item.quantity
```

**Ví dụ cụ thể:**

```
Đơn hàng #124 bị hủy:
- Samsung Galaxy S24: 3 cái

Trước khi hủy:
┌─────────────────────┬─────────────┬───────────┬────────────┬───────────────┐
│ Sản phẩm            │ stock_qty   │ stock_in  │ stock_out  │ current_stock │
├─────────────────────┼─────────────┼───────────┼────────────┼───────────────┤
│ Samsung Galaxy S24  │     30      │    100    │     70     │      30       │
└─────────────────────┴─────────────┴───────────┴────────────┴───────────────┘

Admin hủy đơn hàng ❌

Sau khi hủy (TỰ ĐỘNG):
┌─────────────────────┬─────────────┬───────────┬────────────┬───────────────┐
│ Sản phẩm            │ stock_qty   │ stock_in  │ stock_out  │ current_stock │
├─────────────────────┼─────────────┼───────────┼────────────┼───────────────┤
│ Samsung Galaxy S24  │  30 → 33    │    100    │  70 → 67   │   30 → 33     │
│                     │    (+3)     │           │    (-3)    │     (+3)      │
└─────────────────────┴─────────────┴───────────┴────────────┴───────────────┘
```

## 🔧 Cách hoạt động (Technical Details)

### OrderController - update() method

```php
public function update(Request $request, $id)
{
    $order = Order::findOrFail($id);
    $oldStatus = $order->status;
    $newStatus = $request->status;

    // Sử dụng DB transaction để đảm bảo tính toàn vẹn
    DB::transaction(function () use ($order, $newStatus, $oldStatus) {
        // 1. Cập nhật trạng thái đơn hàng
        $order->update(['status' => $newStatus]);

        // 2. Nếu chuyển sang "Đã giao hàng"
        if ($newStatus === Order::STATUS_DELIVERED && 
            $oldStatus !== Order::STATUS_DELIVERED) {
            $this->updateInventoryOnDelivered($order);
        }

        // 3. Nếu chuyển sang "Đã hủy"
        if ($newStatus === Order::STATUS_CANCELLED && 
            $oldStatus !== Order::STATUS_CANCELLED) {
            $this->restoreInventoryOnCancelled($order);
        }
    });
}
```

### updateInventoryOnDelivered() - Giảm tồn kho

```php
private function updateInventoryOnDelivered(Order $order)
{
    // Lấy tất cả sản phẩm trong đơn hàng
    $orderItems = $order->items()->with('product')->get();

    foreach ($orderItems as $item) {
        $product = $item->product;
        $quantity = $item->quantity;

        // 1. Giảm stock_quantity trong products
        $product->decrement('stock_quantity', $quantity);

        // 2. Tìm hoặc tạo inventory
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $product->product_id],
            ['stock_in' => 0, 'stock_out' => 0, 'current_stock' => 0]
        );

        // 3. Cập nhật inventory
        $inventory->increment('stock_out', $quantity);      // Tăng xuất kho
        $inventory->decrement('current_stock', $quantity);  // Giảm tồn kho
    }
}
```

### restoreInventoryOnCancelled() - Hoàn trả tồn kho

```php
private function restoreInventoryOnCancelled(Order $order)
{
    // Lấy tất cả sản phẩm trong đơn hàng
    $orderItems = $order->items()->with('product')->get();

    foreach ($orderItems as $item) {
        $product = $item->product;
        $quantity = $item->quantity;

        // 1. Tăng lại stock_quantity trong products
        $product->increment('stock_quantity', $quantity);

        // 2. Tìm hoặc tạo inventory
        $inventory = Inventory::firstOrCreate(
            ['product_id' => $product->product_id],
            ['stock_in' => 0, 'stock_out' => 0, 'current_stock' => 0]
        );

        // 3. Cập nhật inventory
        if ($inventory->stock_out >= $quantity) {
            $inventory->decrement('stock_out', $quantity);  // Giảm xuất kho
        }
        $inventory->increment('current_stock', $quantity);  // Tăng tồn kho
    }
}
```

## 📊 Luồng hoạt động

### Luồng 1: Giao hàng thành công

```
┌─────────────────────────────────────────────────────────────┐
│  Admin vào trang: /dashboard/orders/{id}/edit              │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  Chọn trạng thái: "Đã giao hàng"                           │
│  Click "Cập nhật"                                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  OrderController::update()                                  │
│  1. Validate status                                         │
│  2. Check can transition                                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  DB::transaction {                                          │
│    1. Update order status                                   │
│    2. Check: newStatus == 'delivered'? YES                 │
│    3. Call updateInventoryOnDelivered()                     │
│  }                                                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  updateInventoryOnDelivered()                               │
│  Foreach order item:                                        │
│    • product.stock_quantity -= quantity                     │
│    • inventory.stock_out += quantity                        │
│    • inventory.current_stock -= quantity                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  Success! Inventory updated ✅                              │
│  Redirect to order details with success message             │
└─────────────────────────────────────────────────────────────┘
```

### Luồng 2: Hủy đơn hàng

```
┌─────────────────────────────────────────────────────────────┐
│  Admin chọn trạng thái: "Đã hủy"                           │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  OrderController::update()                                  │
│  Check: newStatus == 'cancelled'? YES                       │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  DB::transaction {                                          │
│    1. Update order status                                   │
│    2. Call restoreInventoryOnCancelled()                    │
│  }                                                          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  restoreInventoryOnCancelled()                              │
│  Foreach order item:                                        │
│    • product.stock_quantity += quantity                     │
│    • inventory.stock_out -= quantity                        │
│    • inventory.current_stock += quantity                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│  Success! Inventory restored ✅                             │
│  Redirect to order details with success message             │
└─────────────────────────────────────────────────────────────┘
```

## 🎯 Workflow trạng thái đơn hàng

```
┌─────────────┐
│   PENDING   │ (Chờ xử lý)
│             │
└──────┬──────┘
       │
       ├─────→ [CANCELLED] → Hoàn trả inventory ✅
       │
       ▼
┌─────────────┐
│ PROCESSING  │ (Đang xử lý)
│             │
└──────┬──────┘
       │
       ├─────→ [CANCELLED] → Hoàn trả inventory ✅
       │
       ▼
┌─────────────┐
│   SHIPPED   │ (Đã gửi hàng)
│             │
└──────┬──────┘
       │
       ▼
┌─────────────┐
│  DELIVERED  │ (Đã giao hàng) → Giảm inventory ✅
│             │
└─────────────┘
```

## 🔒 Transaction Safety

### Đảm bảo tính toàn vẹn dữ liệu

```php
DB::transaction(function () use ($order, $newStatus) {
    // Tất cả thao tác trong này sẽ:
    // - Được commit nếu thành công
    // - Được rollback nếu có lỗi
    
    $order->update(['status' => $newStatus]);
    $this->updateInventoryOnDelivered($order);
});
```

**Lợi ích:**
- ✅ Nếu có lỗi, dữ liệu sẽ được rollback
- ✅ Không bao giờ có trạng thái không nhất quán
- ✅ Order status và inventory luôn đồng bộ

## 📝 Ví dụ sử dụng

### Ví dụ 1: Giao hàng thành công

**Bước 1:** Tạo đơn hàng
```
Order #456:
- Product: Laptop Dell XPS 15
- Quantity: 2
- Status: pending
```

**Bước 2:** Admin xử lý đơn hàng
```
Status: pending → processing → shipped
```

**Bước 3:** Admin xác nhận giao hàng
```
Status: shipped → delivered ✅

TỰ ĐỘNG xảy ra:
┌──────────────────────────────────────┐
│ Laptop Dell XPS 15                   │
├──────────────────────────────────────┤
│ stock_quantity: 10 → 8 (-2)         │
│ stock_out: 50 → 52 (+2)             │
│ current_stock: 10 → 8 (-2)          │
└──────────────────────────────────────┘
```

### Ví dụ 2: Hủy đơn hàng

**Bước 1:** Đơn hàng đang xử lý
```
Order #457:
- Product: iPhone 15 Pro
- Quantity: 3
- Status: processing
```

**Bước 2:** Khách hàng yêu cầu hủy
```
Admin: Status processing → cancelled ❌

TỰ ĐỘNG xảy ra:
┌──────────────────────────────────────┐
│ iPhone 15 Pro                        │
├──────────────────────────────────────┤
│ stock_quantity: 20 → 23 (+3)        │
│ stock_out: 80 → 77 (-3)             │
│ current_stock: 20 → 23 (+3)         │
└──────────────────────────────────────┘
```

### Ví dụ 3: Đơn hàng phức tạp (nhiều sản phẩm)

**Đơn hàng #458:**
```
Items:
1. MacBook Pro M3: 1 cái
2. Magic Mouse: 2 cái
3. AirPods Pro: 1 cái
Status: pending → processing → shipped → delivered ✅
```

**Kết quả tự động:**
```
┌───────────────────┬──────────────┬────────────┬────────────┬────────────────┐
│ Product           │ Quantity Sold│ Before     │ After      │ Change         │
├───────────────────┼──────────────┼────────────┼────────────┼────────────────┤
│ MacBook Pro M3    │      1       │ stock: 15  │ stock: 14  │ -1            │
│                   │              │ out: 35    │ out: 36    │ +1            │
│                   │              │ curr: 15   │ curr: 14   │ -1            │
├───────────────────┼──────────────┼────────────┼────────────┼────────────────┤
│ Magic Mouse       │      2       │ stock: 50  │ stock: 48  │ -2            │
│                   │              │ out: 100   │ out: 102   │ +2            │
│                   │              │ curr: 50   │ curr: 48   │ -2            │
├───────────────────┼──────────────┼────────────┼────────────┼────────────────┤
│ AirPods Pro       │      1       │ stock: 30  │ stock: 29  │ -1            │
│                   │              │ out: 70    │ out: 71    │ +1            │
│                   │              │ curr: 30   │ curr: 29   │ -1            │
└───────────────────┴──────────────┴────────────┴────────────┴────────────────┘
```

## ⚠️ Lưu ý quan trọng

### 1. Chỉ cập nhật khi thay đổi trạng thái

```php
// ✅ Đúng: Chỉ cập nhật khi chuyển SANG delivered
if ($newStatus === Order::STATUS_DELIVERED && 
    $oldStatus !== Order::STATUS_DELIVERED)

// ❌ Sai: Sẽ cập nhật nhiều lần nếu không check
if ($newStatus === Order::STATUS_DELIVERED)
```

### 2. Transaction đảm bảo an toàn

```php
DB::transaction(function () {
    // Nếu bất kỳ lệnh nào fail
    // Tất cả sẽ được rollback
    $order->update([...]);
    $this->updateInventory($order);
});
```

### 3. Kiểm tra sản phẩm tồn tại

```php
foreach ($orderItems as $item) {
    if (!$item->product) {
        continue; // Bỏ qua nếu sản phẩm đã bị xóa
    }
    // Xử lý inventory...
}
```

### 4. Inventory luôn được tạo nếu chưa có

```php
$inventory = Inventory::firstOrCreate(
    ['product_id' => $product->product_id],
    ['stock_in' => 0, 'stock_out' => 0, 'current_stock' => 0]
);
```

## 🔍 Testing & Debugging

### Test Case 1: Giao hàng thành công

```php
// Arrange
$order = Order::create([...]);
$order->items()->create(['product_id' => 1, 'quantity' => 5]);
$product = Product::find(1);
$initialStock = $product->stock_quantity;

// Act
$order->update(['status' => 'delivered']);

// Assert
$product->refresh();
assertEquals($initialStock - 5, $product->stock_quantity);

$inventory = Inventory::where('product_id', 1)->first();
assertEquals($initialStock - 5, $inventory->current_stock);
```

### Test Case 2: Hủy đơn hàng

```php
// Arrange
$order = Order::create(['status' => 'processing']);
$order->items()->create(['product_id' => 1, 'quantity' => 3]);
$product = Product::find(1);
$initialStock = $product->stock_quantity;

// Act
$order->update(['status' => 'cancelled']);

// Assert
$product->refresh();
assertEquals($initialStock + 3, $product->stock_quantity);
```

### Kiểm tra logs

```php
// Thêm logging để debug
Log::info('Inventory updated on order delivered', [
    'order_id' => $order->order_id,
    'items' => $orderItems->count(),
    'updated_at' => now(),
]);
```

## 📈 Thống kê & Báo cáo

### Xem lịch sử xuất kho

```sql
SELECT 
    p.name as product_name,
    i.stock_out,
    i.current_stock,
    i.updated_at
FROM inventory i
JOIN products p ON i.product_id = p.product_id
ORDER BY i.updated_at DESC;
```

### Xem đơn hàng đã giao

```sql
SELECT 
    o.order_id,
    o.total_amount,
    COUNT(oi.order_item_id) as total_items,
    SUM(oi.quantity) as total_quantity
FROM orders o
JOIN order_items oi ON o.order_id = oi.order_id
WHERE o.status = 'delivered'
GROUP BY o.order_id;
```

## 🚀 Future Improvements

### 1. Event-Driven Architecture

```php
// OrderDelivered Event
event(new OrderDelivered($order));

// Listener
class UpdateInventoryOnOrderDelivered
{
    public function handle(OrderDelivered $event)
    {
        // Update inventory logic
    }
}
```

### 2. Inventory History/Log

```php
// Tạo bảng inventory_logs
CREATE TABLE inventory_logs (
    id INT PRIMARY KEY,
    product_id INT,
    order_id INT,
    action VARCHAR(50), // 'order_delivered', 'order_cancelled'
    quantity INT,
    old_stock INT,
    new_stock INT,
    created_at TIMESTAMP
);
```

### 3. Stock Reservation (Giữ hàng)

```php
// Giữ hàng khi đơn hàng được tạo
// Giảm stock khi giao hàng
// Hoàn trả nếu hủy
```

### 4. Real-time Notifications

```php
// Thông báo khi hết hàng
if ($inventory->current_stock < 10) {
    event(new LowStockAlert($product));
}
```

## 📚 Related Files

- `app/Http/Controllers/Web/OrderController.php` - Main controller
- `app/Models/Order.php` - Order model with status constants
- `app/Models/OrderItem.php` - Order item model
- `app/Models/Inventory.php` - Inventory model
- `app/Models/Product.php` - Product model
- `resources/views/dashboard/orders/edit.blade.php` - Edit order form

## 🎓 Best Practices

1. **Luôn sử dụng DB Transaction** khi cập nhật nhiều bảng
2. **Kiểm tra điều kiện** trước khi cập nhật (oldStatus !== newStatus)
3. **Log mọi thay đổi quan trọng** để dễ debug
4. **Validate dữ liệu** trước khi xử lý
5. **Handle exceptions** đúng cách và thông báo lỗi rõ ràng

---

**Version**: 1.0.0  
**Last Updated**: 18/10/2025  
**Author**: Hoàng Quang Vinh  
**Project**: WebShop E-commerce Platform
