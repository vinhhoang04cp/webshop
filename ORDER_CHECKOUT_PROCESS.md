# 🛒 Quy trình Đặt hàng & Thanh toán (Order & Checkout Process)

## 📋 Tổng quan

Tài liệu này mô tả **QUY TRÌNH HOÀN CHỈNH** từ khi khách hàng xem sản phẩm, thêm vào giỏ hàng, đến khi thanh toán và hoàn tất đơn hàng.

---

## 🔄 QUY TRÌNH ĐẶT HÀNG HOÀN CHỈNH

```
┌─────────────────────────────────────────────────────────────────┐
│                  BƯỚC 1: XEM SẢN PHẨM                           │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ Khách hàng truy cập        │
                │ http://localhost/          │
                └────────────┬───────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ CustomerProductController  │
                │ - index(): Danh sách SP    │
                │ - show($id): Chi tiết SP   │
                └────────────┬───────────────┘
                             │
                             ▼
                   ┌──────────────────────┐
                   │ Hiển thị thông tin:  │
                   │ - Tên sản phẩm       │
                   │ - Giá                │
                   │ - Mô tả              │
                   │ - stock_quantity ✅  │
                   └──────────┬───────────┘
                             │
                             ▼
                ✅ Khách xem được số lượng còn hàng
                ✅ Chưa ảnh hưởng đến tồn kho

┌─────────────────────────────────────────────────────────────────┐
│               BƯỚC 2: THÊM VÀO GIỎ HÀNG                         │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ Khách nhấn "Thêm vào giỏ"  │
                └────────────┬───────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ CustomerCartController     │
                │ - add($productId)          │
                └────────────┬───────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │ Kiểm tra:                    │
              │ 1. Sản phẩm có tồn tại?      │
              │ 2. Còn hàng trong kho?       │
              └──────────────┬───────────────┘
                             │
                   ┌─────────┴─────────┐
                   │                   │
                   ▼ (Có hàng)        ▼ (Hết hàng)
        ┌──────────────────┐    ┌──────────────────┐
        │ Tạo/Cập nhật     │    │ Báo lỗi:         │
        │ CartItem         │    │ "Sản phẩm hết    │
        │                  │    │  hàng!"          │
        │ - cart_id        │    └──────────────────┘
        │ - product_id     │
        │ - quantity       │
        └────────┬─────────┘
                 │
                 ▼
    ✅ Lưu vào bảng cart_items
    ✅ CHƯA TRỪ stock_quantity
    ✅ Chỉ lưu tạm trong giỏ hàng

┌─────────────────────────────────────────────────────────────────┐
│               BƯỚC 3: XEM VÀ SỬA GIỎ HÀNG                       │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ Khách vào trang giỏ hàng   │
                │ /cart                      │
                └────────────┬───────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ CustomerCartController     │
                │ - index()                  │
                └────────────┬───────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │ Hiển thị:                    │
              │ - Danh sách sản phẩm         │
              │ - Số lượng từng SP           │
              │ - Giá từng SP                │
              │ - Tổng tiền                  │
              └──────────────┬───────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │ Khách có thể:                │
              │ - Tăng/giảm số lượng         │
              │ - Xóa sản phẩm               │
              │ - Tiếp tục mua hàng          │
              │ - Thanh toán →               │
              └──────────────┬───────────────┘
                             │
                             ▼
                ✅ Tồn kho vẫn chưa thay đổi
                ✅ Khách có thể sửa đổi thoải mái

┌─────────────────────────────────────────────────────────────────┐
│         BƯỚC 4: THANH TOÁN (CHECKOUT) - QUAN TRỌNG! ⚠️          │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ Khách nhấn "Đặt hàng"      │
                └────────────┬───────────────┘
                             │
                             ▼
                ┌────────────────────────────┐
                │ CustomerCartController     │
                │ - checkout()               │
                └────────────┬───────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │ Validate thông tin:          │
              │ - Họ tên                     │
              │ - Số điện thoại              │
              │ - Địa chỉ giao hàng          │
              └──────────────┬───────────────┘
                             │
                             ▼
              ┌──────────────────────────────┐
              │ 🔒 KIỂM TRA TỒN KHO          │
              │ foreach cart item:           │
              │   if (stock < quantity)      │
              │     → throw Exception        │
              │     → "Không đủ hàng!"       │
              └──────────────┬───────────────┘
                             │
                    ┌────────┴────────┐
                    │                 │
                    ▼ (Đủ hàng)      ▼ (Không đủ)
         ┌──────────────────┐  ┌──────────────────┐
         │ Tiếp tục xử lý   │  │ Báo lỗi chi tiết │
         └────────┬─────────┘  │ "SP X chỉ còn Y" │
                  │            └──────────────────┘
                  │
                  ▼
         ┌─────────────────────────────────┐
         │ 🔐 DB::transaction {            │
         │                                 │
         │   1️⃣ TẠO ORDER                 │
         │   $order = Order::create([      │
         │     'user_id' => auth()->id(),  │
         │     'order_id' => unique_id,    │
         │     'status' => 'pending',      │
         │     'total_amount' => $total,   │
         │     'customer_name' => $name,   │
         │     'phone' => $phone,          │
         │     'address' => $address,      │
         │     'payment_method' => 'cod',  │
         │   ]);                           │
         │                                 │
         │   2️⃣ TẠO ORDER ITEMS           │
         │   foreach ($cart->items) {      │
         │     OrderItem::create([         │
         │       'order_id' => $order->id, │
         │       'product_id' => $item->id,│
         │       'quantity' => $qty,       │
         │       'price' => $price,        │
         │     ]);                         │
         │   }                             │
         │                                 │
         │   3️⃣ ⚠️ TRỪ TỒN KHO NGAY      │
         │   foreach ($cart->items) {      │
         │     $product = Product::find(); │
         │     $product->decrement(        │
         │       'stock_quantity',         │
         │       $item->quantity           │
         │     );                          │
         │   }                             │
         │                                 │
         │   4️⃣ CẬP NHẬT INVENTORY        │
         │   foreach ($cart->items) {      │
         │     $inventory = Inventory::    │
         │       firstOrCreate([...]);     │
         │     $inventory->increment(      │
         │       'stock_out', $qty         │
         │     );                          │
         │     $inventory->decrement(      │
         │       'current_stock', $qty     │
         │     );                          │
         │   }                             │
         │                                 │
         │   5️⃣ XÓA GIỎ HÀNG              │
         │   $cart->items()->delete();     │
         │                                 │
         │ } // End transaction            │
         └─────────┬───────────────────────┘
                   │
                   ▼
         🔥 QUAN TRỌNG: TỒN KHO ĐÃ BỊ TRỪ NGAY!
                   │
                   ▼
         ┌─────────────────────────────────┐
         │ Kết quả:                        │
         │ ✅ Order status = 'pending'     │
         │ ✅ stock_quantity -= X          │
         │ ✅ stock_out += X               │
         │ ✅ current_stock -= X           │
         │ ✅ Giỏ hàng đã xóa              │
         └─────────┬───────────────────────┘
                   │
                   ▼
         ┌─────────────────────────────────┐
         │ Chuyển hướng:                   │
         │ → Trang "Đặt hàng thành công"   │
         │ → Hiển thị mã đơn hàng          │
         │ → Thông tin đơn hàng            │
         └─────────────────────────────────┘
```

---

## 💡 TẠI SAO TRỪ TỒN KHO NGAY KHI CHECKOUT?

### ✅ Lợi ích

#### 1. **Tránh Overselling (Bán quá số lượng)**

```
Tình huống: Còn 5 iPhone, 10 khách cùng đặt mỗi người 1 cái

❌ Nếu trừ khi giao hàng:
User 1: Đặt 1 → OK (còn 5)
User 2: Đặt 1 → OK (còn 5)  ← Vẫn thấy còn 5!
...
User 10: Đặt 1 → OK (còn 5) ← Vẫn thấy còn 5!
→ Có 10 đơn nhưng chỉ 5 sản phẩm! ❌

✅ Nếu trừ khi checkout:
User 1: Checkout → OK (còn 4)
User 2: Checkout → OK (còn 3)
User 3: Checkout → OK (còn 2)
User 4: Checkout → OK (còn 1)
User 5: Checkout → OK (còn 0)
User 6: Checkout → FAIL "Hết hàng!" ✅
→ Chính xác 5 đơn, 5 sản phẩm!
```

#### 2. **Giữ hàng cho khách đã đặt**

- Khách đã thanh toán (checkout) → Hàng đã được "giữ" cho họ
- Không bị người khác đặt mất
- Đảm bảo có hàng giao cho khách

#### 3. **Quản lý tồn kho chính xác**

- Admin biết chính xác còn bao nhiêu hàng có thể bán
- `stock_quantity` = Số hàng còn lại có thể bán cho khách mới
- Không cần lo tính toán "hàng đang chờ giao"

---

## 🔐 CƠ CHẾ TRANSACTION (DB::transaction)

### Đảm bảo tính toàn vẹn dữ liệu

```php
DB::transaction(function () {
    // Tất cả thao tác trong đây là 1 đơn vị:
    // - Thành công → Commit (lưu vào DB)
    // - Lỗi → Rollback (không thay đổi gì)
    
    // 1. Tạo Order
    $order = Order::create([...]);
    
    // 2. Tạo OrderItems
    OrderItem::create([...]);
    
    // 3. Trừ stock
    $product->decrement('stock_quantity', $qty);
    
    // 4. Cập nhật inventory
    $inventory->increment('stock_out', $qty);
    
    // 5. Xóa cart
    $cart->items()->delete();
    
    // Nếu có 1 bước nào lỗi → Rollback tất cả
});
```

### Ví dụ lỗi và Rollback

```
Kịch bản:
1. Tạo Order → OK ✅
2. Tạo OrderItems → OK ✅
3. Trừ stock → OK ✅
4. Cập nhật inventory → LỖI ❌ (DB connection timeout)
5. Xóa cart → (Không chạy được)

→ Transaction tự động ROLLBACK:
   - Order KHÔNG được tạo
   - OrderItems KHÔNG được tạo
   - stock KHÔNG bị trừ
   - inventory KHÔNG thay đổi
   - cart KHÔNG bị xóa

→ Dữ liệu vẫn nguyên vẹn! ✅
```

---

## 📊 THAY ĐỔI TỒN KHO QUA TỪNG BƯỚC

### Ví dụ: Đặt mua 2 iPhone 15 Pro Max

**Ban đầu:**
```
Product: iPhone 15 Pro Max 256GB
├─ stock_quantity: 50
└─ Inventory:
   ├─ stock_in: 50      (Nhập kho ban đầu)
   ├─ stock_out: 0      (Chưa xuất)
   └─ current_stock: 50 (= 50 - 0)
```

**Sau khi xem sản phẩm:**
```
✅ Không thay đổi
```

**Sau khi thêm vào giỏ (2 cái):**
```
✅ Vẫn không thay đổi

CartItem:
├─ product_id: 1
├─ quantity: 2
└─ (Chỉ lưu tạm)
```

**Sau khi CHECKOUT (Thanh toán COD):**
```
Product: iPhone 15 Pro Max 256GB
├─ stock_quantity: 48 (-2) ⚠️ ĐÃ TRỪ
└─ Inventory:
   ├─ stock_in: 50
   ├─ stock_out: 2 (+2) ⚠️ TĂNG
   └─ current_stock: 48 (-2) ⚠️ GIẢM

Order #ORD-20251018-001:
├─ status: pending
├─ payment_method: cod
├─ total_amount: 49,990,000 VNĐ
└─ items:
   └─ 2x iPhone 15 Pro Max @ 24,995,000 VNĐ

CartItem:
└─ (Đã xóa)
```

**Công thức kiểm tra:**
```
current_stock = stock_in - stock_out
48 = 50 - 2 ✅ ĐỒNG BỘ
```

---

## 🛡️ PHÒNG CHỐNG LỖI & EDGE CASES

### 1. Kiểm tra tồn kho trước khi thanh toán

```php
foreach ($cart->items as $item) {
    $product = $item->product;
    
    if ($product->stock_quantity < $item->quantity) {
        return back()->with('error', 
            "Sản phẩm '{$product->name}' chỉ còn {$product->stock_quantity} trong kho, " .
            "không đủ cho {$item->quantity} sản phẩm bạn đặt!"
        );
    }
}
```

### 2. Race condition (2 người đặt cùng lúc)

```
Còn 1 iPhone, User A và User B cùng checkout:

Time 0:00:00.000 - User A: Kiểm tra stock = 1 ✅
Time 0:00:00.001 - User B: Kiểm tra stock = 1 ✅
Time 0:00:00.002 - User A: Trừ stock → 0 ✅
Time 0:00:00.003 - User B: Trừ stock → -1 ❌

→ Laravel Transaction + Database Lock tự động xử lý:
  - User A: Thành công ✅
  - User B: Fail (stock < 0) ❌
```

### 3. Giỏ hàng có sản phẩm đã bị xóa

```php
foreach ($cart->items as $item) {
    $product = $item->product;
    
    if (!$product || $product->deleted_at !== null) {
        return back()->with('error', 
            "Sản phẩm '{$item->product_name}' không còn tồn tại. " .
            "Vui lòng xóa khỏi giỏ hàng!"
        );
    }
}
```

### 4. Số lượng âm hoặc 0

```php
if ($request->quantity <= 0) {
    return back()->with('error', 'Số lượng phải lớn hơn 0!');
}

if ($request->quantity > 999) {
    return back()->with('error', 'Số lượng tối đa là 999!');
}
```

---

## 📱 FLOW TRÊN GIAO DIỆN NGƯỜI DÙNG

### 1. Trang sản phẩm (`/products/{id}`)

**Hiển thị:**
```
┌─────────────────────────────────────────┐
│ iPhone 15 Pro Max 256GB                 │
│ ─────────────────────────────────────── │
│ [Hình ảnh sản phẩm]                     │
│                                         │
│ Giá: 24,995,000 VNĐ                     │
│ Còn hàng: 50 sản phẩm ✅                │
│                                         │
│ Mô tả: ...                              │
│                                         │
│ Số lượng: [  2  ] [- / +]               │
│                                         │
│ [ Thêm vào giỏ hàng ]                   │
└─────────────────────────────────────────┘
```

**Khi nhấn "Thêm vào giỏ":**
```
→ POST /cart/add
→ "Đã thêm 2 sản phẩm vào giỏ hàng!" ✅
→ Badge giỏ hàng: 🛒 (2)
```

### 2. Trang giỏ hàng (`/cart`)

**Hiển thị:**
```
┌─────────────────────────────────────────────────────┐
│ GIỎ HÀNG CỦA BẠN                                    │
│ ─────────────────────────────────────────────────── │
│                                                     │
│ ┌───┬────────────────┬────────┬──────────┬────────┐│
│ │   │ Sản phẩm       │ SL     │ Đơn giá  │ Tổng   ││
│ ├───┼────────────────┼────────┼──────────┼────────┤│
│ │ X │ iPhone 15 Pro  │ [- 2 +]│ 24,995k  │ 49,990k││
│ │   │ Max 256GB      │        │          │        ││
│ ├───┼────────────────┼────────┼──────────┼────────┤│
│ │ X │ Samsung Galaxy │ [- 1 +]│ 22,990k  │ 22,990k││
│ │   │ S24 Ultra      │        │          │        ││
│ └───┴────────────────┴────────┴──────────┴────────┘│
│                                                     │
│                          Tổng cộng: 72,980,000 VNĐ  │
│                                                     │
│ [ Tiếp tục mua hàng ]           [ Thanh toán ] ──┐ │
└──────────────────────────────────────────────────┼─┘
                                                   │
                                                   ▼
```

### 3. Trang thanh toán (`/cart/checkout`)

**Form nhập thông tin:**
```
┌─────────────────────────────────────────────────────┐
│ THÔNG TIN GIAO HÀNG                                 │
│ ─────────────────────────────────────────────────── │
│                                                     │
│ Họ và tên (*):                                      │
│ [_____________________________________________]     │
│                                                     │
│ Số điện thoại (*):                                  │
│ [_____________________________________________]     │
│                                                     │
│ Địa chỉ giao hàng (*):                              │
│ [_____________________________________________]     │
│ [_____________________________________________]     │
│                                                     │
│ Ghi chú (tùy chọn):                                 │
│ [_____________________________________________]     │
│                                                     │
│ ─────────────────────────────────────────────────── │
│ PHƯƠNG THỨC THANH TOÁN                              │
│                                                     │
│ ⦿ Thanh toán khi nhận hàng (COD)                    │
│                                                     │
│ ─────────────────────────────────────────────────── │
│ ĐƠN HÀNG CỦA BẠN                                    │
│                                                     │
│ • 2x iPhone 15 Pro Max.......... 49,990,000 VNĐ     │
│ • 1x Samsung Galaxy S24......... 22,990,000 VNĐ     │
│                                                     │
│ Tạm tính:........................ 72,980,000 VNĐ     │
│ Phí vận chuyển:.................. Miễn phí          │
│                                                     │
│ Tổng cộng:................ 72,980,000 VNĐ           │
│                                                     │
│              [ ĐẶT HÀNG NGAY ]                      │
└─────────────────────────────────────────────────────┘
```

**Khi nhấn "Đặt hàng":**
```
→ POST /cart/checkout
→ Validate dữ liệu
→ Kiểm tra tồn kho
→ Tạo order + Trừ stock ⚠️
→ Redirect: /orders/success
```

### 4. Trang đặt hàng thành công

**Hiển thị:**
```
┌─────────────────────────────────────────────────────┐
│          ✅ ĐẶT HÀNG THÀNH CÔNG!                    │
│ ─────────────────────────────────────────────────── │
│                                                     │
│ Cảm ơn bạn đã đặt hàng!                             │
│                                                     │
│ Mã đơn hàng: ORD-20251018-001234                    │
│ Trạng thái: Chờ xử lý                               │
│ Tổng tiền: 72,980,000 VNĐ                           │
│ Thanh toán: COD (Khi nhận hàng)                     │
│                                                     │
│ Thông tin giao hàng:                                │
│ • Người nhận: Nguyễn Văn A                          │
│ • SĐT: 0123456789                                   │
│ • Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM           │
│                                                     │
│ Chúng tôi sẽ liên hệ với bạn trong thời gian sớm    │
│ nhất để xác nhận đơn hàng.                          │
│                                                     │
│ [ Xem chi tiết đơn hàng ]  [ Tiếp tục mua hàng ]   │
└─────────────────────────────────────────────────────┘
```

---

## 🔍 KIỂM TRA & DEBUG

### 1. Kiểm tra tồn kho sau checkout

```bash
sail artisan tinker
```

```php
// Xem tồn kho sản phẩm
$product = Product::find(1);
echo "Stock: {$product->stock_quantity}\n";

// Xem inventory
$inv = Inventory::where('product_id', 1)->first();
echo "In: {$inv->stock_in}, Out: {$inv->stock_out}, Current: {$inv->current_stock}\n";

// Kiểm tra đồng bộ
echo "Đồng bộ: " . ($inv->stock_in - $inv->stock_out == $inv->current_stock ? 'OK' : 'FAIL') . "\n";
```

### 2. Xem log checkout

```bash
tail -f storage/logs/laravel.log | grep -i checkout
```

### 3. Kiểm tra đơn hàng vừa tạo

```php
$order = Order::latest()->first();

echo "Order: #{$order->order_id}\n";
echo "Status: {$order->status}\n";
echo "Total: " . number_format($order->total_amount) . " VNĐ\n";
echo "Items:\n";

foreach ($order->items as $item) {
    echo "  - {$item->product->name} x{$item->quantity} @ " . 
         number_format($item->price) . " VNĐ\n";
}
```

---

## 📁 FILES LIÊN QUAN

### Controllers

**`app/Http/Controllers/Web/CustomerCartController.php`**
- `add()`: Thêm sản phẩm vào giỏ
- `index()`: Xem giỏ hàng
- `update()`: Cập nhật số lượng
- `remove()`: Xóa sản phẩm khỏi giỏ
- **`checkout()`**: **TẠO ĐƠN HÀNG VÀ TRỪ TỒN KHO** ⚠️

**`app/Http/Controllers/Web/CustomerProductController.php`**
- `index()`: Danh sách sản phẩm
- `show()`: Chi tiết sản phẩm

### Models

**`app/Models/Cart.php`**
- Relationship: user, items

**`app/Models/CartItem.php`**
- Relationship: cart, product

**`app/Models/Order.php`**
- Status constants
- Relationship: user, items

**`app/Models/Product.php`**
- `stock_quantity` field
- Relationship: inventory, orderItems

**`app/Models/Inventory.php`**
- `stock_in`, `stock_out`, `current_stock`
- Relationship: product

### Routes

**`routes/web.php`**
```php
// Customer routes
Route::get('/products', [CustomerProductController::class, 'index']);
Route::get('/products/{id}', [CustomerProductController::class, 'show']);

Route::middleware('auth')->group(function () {
    Route::get('/cart', [CustomerCartController::class, 'index']);
    Route::post('/cart/add', [CustomerCartController::class, 'add']);
    Route::patch('/cart/update/{id}', [CustomerCartController::class, 'update']);
    Route::delete('/cart/remove/{id}', [CustomerCartController::class, 'remove']);
    Route::post('/cart/checkout', [CustomerCartController::class, 'checkout']); // ⚠️
});
```

---

## ✅ CHECKLIST HOÀN CHỈNH

### Trước khi checkout:
- [ ] Giỏ hàng có ít nhất 1 sản phẩm
- [ ] Tất cả sản phẩm còn tồn tại (không bị xóa)
- [ ] Số lượng > 0 và < 999
- [ ] User đã đăng nhập

### Trong quá trình checkout:
- [ ] Validate thông tin giao hàng đầy đủ
- [ ] Kiểm tra tồn kho của từng sản phẩm
- [ ] Sử dụng `DB::transaction()` cho toàn bộ quy trình
- [ ] Tạo Order với status = 'pending'
- [ ] Tạo OrderItems cho từng sản phẩm
- [ ] **Trừ `stock_quantity`** ⚠️
- [ ] **Tăng `stock_out` trong Inventory** ⚠️
- [ ] **Giảm `current_stock` trong Inventory** ⚠️
- [ ] Xóa CartItems sau khi tạo order thành công

### Sau khi checkout thành công:
- [ ] Redirect đến trang "Đặt hàng thành công"
- [ ] Hiển thị mã đơn hàng
- [ ] Tồn kho đã giảm
- [ ] Inventory đã cập nhật
- [ ] Giỏ hàng đã trống
- [ ] Có log trong `storage/logs/laravel.log`

---

## 🎯 KẾT LUẬN

### Quy trình thanh toán đúng:

1. ✅ **Xem sản phẩm**: Không ảnh hưởng tồn kho
2. ✅ **Thêm giỏ hàng**: Chỉ lưu tạm, chưa trừ tồn kho
3. ✅ **Sửa giỏ hàng**: Thoải mái tăng/giảm, chưa trừ tồn kho
4. ⚠️ **CHECKOUT**: **TRỪ TỒN KHO NGAY** (giữ hàng cho khách)
5. ✅ **Giao hàng**: Không trừ nữa (đã trừ từ bước 4)

### Lợi ích:

- 🛡️ Tránh overselling (bán quá số lượng)
- 🔒 Giữ hàng cho khách đã đặt
- 📊 Quản lý tồn kho chính xác
- ⚡ Hiệu suất cao (không cần tính toán phức tạp)

---

**Version**: 1.0.0  
**Last Updated**: 18/10/2025  
**Author**: Hoàng Quang Vinh  
**Project**: WebShop E-commerce Platform
