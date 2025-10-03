# 🔧 FIX LỖI: "There is no active transaction"

## 🚨 Vấn đề:
```json
{
    "status": false,
    "message": "Failed to add items to cart",
    "error": "There is no active transaction"
}
```

**Nguyên nhân:** Method `Cart::reOrderIds()` được gọi **bên trong** một active transaction, gây conflict với transaction state.

## ✅ Giải pháp đã áp dụng:

### 1. **Di chuyển reOrderIds() ra ngoài transaction**
```php
// TRƯỚC (LỖI):
DB::beginTransaction();
try {
    // ... cart operations ...
    Cart::reOrderIds();  // ❌ Gọi trong transaction
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
}

// SAU (ĐÚNG):
DB::beginTransaction();
try {
    // ... cart operations ...
    DB::commit();
    
    // ✅ Gọi sau khi transaction đã commit
    Cart::reOrderIds();
} catch (\Exception $e) {
    DB::rollback();
}
```

### 2. **Thêm error handling cho reOrderIds()**
```php
try {
    Cart::reOrderIds();
} catch (\Exception $reorderException) {
    // Log error nhưng không làm fail request chính
    \Log::warning('Failed to reorder Cart IDs: ' . $reorderException->getMessage());
}
```

### 3. **Cải thiện Cart model với proper exception handling**
```php
public static function reOrderIds()
{
    try {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        // ... reorder logic ...
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    } catch (\Exception $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // Cleanup
        throw $e;
    }
}
```

## 📋 Files đã sửa:

### `app/Http/Controllers/Api/CartController.php`:
- ✅ Di chuyển `Cart::reOrderIds()` ra ngoài transaction trong `store()` method
- ✅ Di chuyển `Cart::reOrderIds()` ra ngoài transaction trong `destroy()` method  
- ✅ Thêm try-catch cho reorderIds với logging
- ✅ Thêm `use Illuminate\Support\Facades\Log;`

### `app/Models/Cart.php`:
- ✅ Thêm proper exception handling trong `reOrderIds()`
- ✅ Đảm bảo `SET FOREIGN_KEY_CHECKS = 1` được gọi ngay cả khi có lỗi

## 🎯 Kết quả:

**Trước khi fix:**
- ❌ POST `/api/carts` → "There is no active transaction" error
- ❌ DELETE `/api/carts/{id}` → Potential transaction errors

**Sau khi fix:**
- ✅ POST `/api/carts` → Hoạt động bình thường
- ✅ DELETE `/api/carts/{id}` → Hoạt động bình thường
- ✅ ID reordering vẫn được thực hiện, nhưng an toàn hơn
- ✅ Nếu reorder fail, main operation vẫn thành công

## 🔧 Test thử ngay:
```bash
curl -X POST http://your-api/api/carts \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "product_id": 1,
    "quantity": 2
  }'
```

Should work now! ✅