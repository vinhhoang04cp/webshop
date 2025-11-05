# Custom Exceptions Implementation Guide

## 📋 Tổng Quan

Custom Exceptions đã được implement để thay thế generic `\Exception`, giúp code rõ ràng hơn, dễ maintain và xử lý lỗi chính xác.

## ✅ Đã Hoàn Thành

### 1. Base Exception Class

**File:** `app/Exceptions/BusinessException.php`

```php
class BusinessException extends Exception
{
    protected $statusCode = 422;
    protected $errorCode;
    protected $userMessage;
    protected $technicalMessage;
    
    // Features:
    // - Auto-generate error codes from class name
    // - Support both API (JSON) and Web (redirect) responses
    // - Vietnamese user-friendly messages
    // - Technical messages for logging
    // - Automatic exception reporting
}
```

---

## 🗂️ Exception Categories (18 exceptions)

### 2. Cart Exceptions (4)

| Exception | Code | Status | Usage |
|-----------|------|--------|-------|
| `EmptyCartException` | `CART_EMPTY` | 422 | Khi giỏ hàng trống |
| `CartItemNotFoundException` | `CART_ITEM_NOT_FOUND` | 404 | Không tìm thấy sản phẩm trong giỏ |
| `UnauthorizedCartAccessException` | `CART_UNAUTHORIZED_ACCESS` | 403 | Không có quyền truy cập giỏ hàng |
| `CartNotFoundException` | `CART_NOT_FOUND` | 404 | Không tìm thấy giỏ hàng |

### 3. Coupon Exceptions (7)

| Exception | Code | Status | Usage |
|-----------|------|--------|-------|
| `InvalidCouponException` | `COUPON_INVALID` | 422 | Base cho tất cả lỗi coupon |
| `CouponNotFoundException` | `COUPON_NOT_FOUND` | 404 | Mã giảm giá không tồn tại |
| `CouponExpiredException` | `COUPON_EXPIRED` | 422 | Mã đã hết hạn |
| `CouponUsageLimitExceededException` | `COUPON_USAGE_LIMIT_EXCEEDED` | 422 | Đã dùng hết số lần |
| `MinimumOrderAmountNotMetException` | `COUPON_MINIMUM_ORDER_NOT_MET` | 422 | Chưa đủ giá trị tối thiểu |
| `CouponNotYetActiveException` | `COUPON_NOT_YET_ACTIVE` | 422 | Mã chưa được kích hoạt |
| `CouponInactiveException` | `COUPON_INACTIVE` | 422 | Mã đã bị vô hiệu hóa |

### 4. Product Exceptions (3)

| Exception | Code | Status | Usage |
|-----------|------|--------|-------|
| `InsufficientStockException` | `PRODUCT_INSUFFICIENT_STOCK` | 422 | Không đủ hàng trong kho |
| `ProductNotFoundException` | `PRODUCT_NOT_FOUND` | 404 | Sản phẩm không tồn tại |
| `ProductOutOfStockException` | `PRODUCT_OUT_OF_STOCK` | 422 | Sản phẩm hết hàng |

### 5. Order Exceptions (4)

| Exception | Code | Status | Usage |
|-----------|------|--------|-------|
| `OrderNotFoundException` | `ORDER_NOT_FOUND` | 404 | Đơn hàng không tồn tại |
| `InvalidOrderStatusTransitionException` | `ORDER_INVALID_STATUS_TRANSITION` | 422 | Không thể chuyển trạng thái |
| `OrderCannotBeDeletedException` | `ORDER_CANNOT_BE_DELETED` | 422 | Không thể xóa đơn hàng |
| `UnauthorizedOrderAccessException` | `ORDER_UNAUTHORIZED_ACCESS` | 403 | Không có quyền truy cập |

### 6. Payment Exceptions (3)

| Exception | Code | Status | Usage |
|-----------|------|--------|-------|
| `PaymentFailedException` | `PAYMENT_FAILED` | 422 | Thanh toán thất bại |
| `InvalidPaymentSignatureException` | `PAYMENT_INVALID_SIGNATURE` | 400 | Chữ ký thanh toán không hợp lệ |
| `PaymentCancelledException` | `PAYMENT_CANCELLED` | 422 | Người dùng hủy thanh toán |

---

## 🔧 Services Updated

### CartService

**Before:**
```php
if (!$cart || $cart->items()->count() == 0) {
    throw new \Exception('Giỏ hàng trống!');
}

if (!$product) {
    throw new \Exception('Sản phẩm không tồn tại!');
}

if ($cartItem->cart->user_id != Auth::id()) {
    throw new \Exception('Không có quyền!');
}
```

**After:**
```php
if (!$cart || $cart->items()->count() == 0) {
    throw new EmptyCartException();
}

if (!$product) {
    throw new ProductNotFoundException($item->product_id);
}

if ($cartItem->cart->user_id != Auth::id()) {
    throw new UnauthorizedCartAccessException();
}
```

**Updated Methods:**
- ✅ `processCheckout()` - EmptyCartException
- ✅ `validateStock()` - ProductNotFoundException, InsufficientStockException
- ✅ `applyCoupon()` - CouponNotFoundException + uses CouponService.validateCoupon()
- ✅ `updateCartItem()` - UnauthorizedCartAccessException
- ✅ `removeFromCart()` - UnauthorizedCartAccessException
- ✅ `findOrCreateCartForUser()` - CartNotFoundException

---

### OrderService

**Before:**
```php
if (!$order->canTransitionTo($newStatus)) {
    throw new \Exception("Không thể chuyển đổi...");
}

if (!in_array($order->status, [...])) {
    throw new \Exception('Chỉ có thể xóa đơn hàng đã hủy hoặc đã giao!');
}
```

**After:**
```php
if (!$order->canTransitionTo($newStatus)) {
    throw new InvalidOrderStatusTransitionException($oldStatus, $newStatus);
}

if (!in_array($order->status, [...])) {
    throw new OrderCannotBeDeletedException($order->status);
}
```

**Updated Methods:**
- ✅ `updateOrderStatus()` - InvalidOrderStatusTransitionException
- ✅ `deleteOrder()` - OrderCannotBeDeletedException
- ✅ `validateStock()` - ProductNotFoundException, InsufficientStockException
- ✅ `createOrder()` - Now uses exception-based validateStock()
- ✅ `getOrderForPayment()` - UnauthorizedOrderAccessException

---

### PaymentService

**Before:**
```php
if ($secureHash != $vnp_SecureHash) {
    return false;
}

return [
    'success' => false,
    'message' => $errorMessage
];
```

**After:**
```php
if ($secureHash != $vnp_SecureHash) {
    throw new InvalidPaymentSignatureException();
}

if ($vnp_ResponseCode == '24') {
    throw new PaymentCancelledException();
} else {
    throw new PaymentFailedException($errorMessage, $vnp_ResponseCode);
}
```

**Updated Methods:**
- ✅ `validateVNPayCallback()` - InvalidPaymentSignatureException
- ✅ `processVNPayReturn()` - PaymentFailedException, PaymentCancelledException

---

### CouponService

**New Method Added:**
```php
/**
 * Validate coupon - throws exceptions instead of returning array
 * 
 * @throws CouponInactiveException
 * @throws CouponNotYetActiveException
 * @throws CouponExpiredException
 * @throws CouponUsageLimitExceededException
 * @throws MinimumOrderAmountNotMetException
 */
public function validateCoupon(Coupon $coupon, $totalAmount): void
{
    if (!$coupon->is_active) {
        throw new CouponInactiveException($coupon->code);
    }
    
    if ($coupon->start_date && now() < $coupon->start_date) {
        throw new CouponNotYetActiveException($coupon->code, $coupon->start_date);
    }
    
    // ... more validations
}
```

**Backward Compatibility:**
- ✅ Old `isValid()` method still exists, returns `['valid' => bool, 'message' => string]`
- ✅ New `validateCoupon()` throws exceptions for new code
- ✅ Both methods can coexist

---

## 🎯 Benefits

### 1. Type Safety
```php
// Before: Khó biết exception nào sẽ throw
try {
    $this->processCheckout($data);
} catch (\Exception $e) {
    // Xử lý tất cả lỗi như nhau
}

// After: Rõ ràng và có thể xử lý riêng
try {
    $this->processCheckout($data);
} catch (EmptyCartException $e) {
    return redirect()->route('cart.index')->with('error', 'Vui lòng thêm sản phẩm!');
} catch (InsufficientStockException $e) {
    return redirect()->back()->with('error', $e->getMessage());
} catch (CouponExpiredException $e) {
    // Xử lý riêng cho coupon hết hạn
}
```

### 2. Better Error Responses

**API Response (automatic):**
```json
{
    "success": false,
    "error_code": "CART_EMPTY",
    "message": "Giỏ hàng trống!",
    "status_code": 422
}
```

**Web Response (automatic):**
- Redirect với flash message
- User-friendly error messages in Vietnamese

### 3. Easier Debugging
```php
// Exception class name tells you exactly what happened
InsufficientStockException
// vs generic
Exception: "Sản phẩm 'iPhone 15' chỉ còn 2 sản phẩm trong kho!"
```

### 4. IDE Autocomplete
```php
// IDE can suggest which exceptions a method throws
/** @throws EmptyCartException */
/** @throws InsufficientStockException */
public function processCheckout(array $data) { }
```

---

## 📊 Statistics

- **Total Exceptions Created:** 18
- **Services Updated:** 4 (CartService, OrderService, PaymentService, CouponService)
- **Methods Updated:** 15+
- **Generic Exceptions Removed:** ~20+
- **Code Quality Improvement:** 🟢 Significant

---

## 🚀 Next Steps

### Recommended Improvements:
1. ✅ **Custom Exceptions** - COMPLETED
2. 🔲 **DTOs (Data Transfer Objects)** - Create CheckoutDTO, OrderDTO, PaymentDTO
3. 🔲 **Events & Listeners** - OrderCreated, PaymentProcessed, ProductStockChanged
4. 🔲 **Constants** - Extract magic strings/numbers (OrderStatus, PaymentMethod, etc.)
5. 🔲 **Validation Rules** - Move validation to Form Requests
6. 🔲 **API Resources** - Standardize API responses with Laravel Resources

---

## 📝 Usage Examples

### Example 1: Cart Checkout with Exception Handling
```php
// Controller
public function checkout(Request $request)
{
    try {
        $result = $this->cartService->processCheckout($request->all());
        
        return redirect()
            ->route('order.success', $result['order']->order_id)
            ->with('success', 'Đặt hàng thành công!');
            
    } catch (EmptyCartException $e) {
        return redirect()
            ->route('cart.index')
            ->with('error', 'Vui lòng thêm sản phẩm vào giỏ hàng!');
            
    } catch (InsufficientStockException $e) {
        return redirect()
            ->back()
            ->with('error', $e->getMessage());
            
    } catch (CouponExpiredException $e) {
        return redirect()
            ->back()
            ->with('error', 'Mã giảm giá đã hết hạn!')
            ->withInput();
    }
}
```

### Example 2: API with Exception Handling
```php
// API Controller
public function checkout(Request $request)
{
    try {
        $result = $this->cartService->processCheckout($request->all(), Auth::id());
        
        return response()->json([
            'success' => true,
            'order' => new OrderResource($result['order']),
            'discount_amount' => $result['discount_amount']
        ], 201);
        
    } catch (BusinessException $e) {
        // BusinessException auto-formats JSON response
        return $e->render($request);
    }
}
```

### Example 3: Using New CouponService validateCoupon()
```php
// Instead of checking isValid() array
$couponService = app(CouponService::class);

try {
    $couponService->validateCoupon($coupon, $totalAmount);
    // If we reach here, coupon is valid
    $discount = $couponService->calculateDiscount($coupon, $totalAmount);
    
} catch (CouponExpiredException $e) {
    // Handle expired coupon
} catch (MinimumOrderAmountNotMetException $e) {
    // Handle minimum amount not met
}
```

---

## 🧪 Testing

### Test Custom Exceptions:
```php
public function test_empty_cart_throws_exception()
{
    $this->expectException(EmptyCartException::class);
    
    $this->cartService->processCheckout([]);
}

public function test_insufficient_stock_throws_exception()
{
    $this->expectException(InsufficientStockException::class);
    
    // Setup: Product with stock_quantity = 5
    // Request quantity = 10
    
    $this->cartService->validateStock($cart);
}
```

---

## 📚 References

- Clean Code principles - Single Responsibility
- SOLID principles - Dependency Inversion
- Laravel Exception Handling: https://laravel.com/docs/11.x/errors
- PHP Exception Best Practices

---

**Created:** 2024
**Last Updated:** 2024
**Author:** Development Team
