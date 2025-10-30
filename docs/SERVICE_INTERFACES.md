# 🔌 Service Interfaces Documentation

> **Mục đích**: Hướng dẫn sử dụng Service Interfaces để tuân thủ SOLID principles và dễ dàng testing

## 📋 Mục lục
1. [Giới thiệu](#giới-thiệu)
2. [Danh sách Interfaces](#danh-sách-interfaces)
3. [Cách sử dụng](#cách-sử-dụng)
4. [Lợi ích](#lợi-ích)
5. [Testing với Interfaces](#testing-với-interfaces)

---

## 🎯 Giới thiệu

Service Interfaces được tạo để:
- ✅ Tuân thủ **Dependency Inversion Principle** (SOLID)
- ✅ Dễ dàng **mock** khi testing
- ✅ Cho phép **swap implementations** dễ dàng
- ✅ Tách rời **abstraction** và **implementation**

---

## 📦 Danh sách Interfaces

### 1. CartServiceInterface

**Location**: `app/Contracts/CartServiceInterface.php`

**Chức năng**:
- Quản lý giỏ hàng (thêm, xóa, cập nhật)
- Xử lý checkout
- Tính toán tổng tiền
- Áp dụng coupon

**Main Methods**:
```php
public function processCheckout(array $data, $userId = null);
public function getOrCreateCart();
public function addToCart($productId, $quantity = 1);
public function updateCartItem($cartItemId, $quantity);
public function removeFromCart($cartItemId);
public function clearCart();
public function calculateCartTotals($cart);
```

---

### 2. OrderServiceInterface

**Location**: `app/Contracts/OrderServiceInterface.php`

**Chức năng**:
- Quản lý đơn hàng
- Cập nhật trạng thái đơn hàng
- Xử lý hủy đơn và hoàn trả tồn kho
- Thống kê đơn hàng

**Main Methods**:
```php
public function getOrdersForAdmin(array $filters = [], int $perPage = 15);
public function getOrderDetail($orderId);
public function updateOrderStatus($orderId, $newStatus);
public function deleteOrder($orderId);
public function createOrder(array $data);
public function getOrderStats($userId = null, $isAdmin = false);
```

---

### 3. PaymentServiceInterface

**Location**: `app/Contracts/PaymentServiceInterface.php`

**Chức năng**:
- Tạo URL thanh toán VNPay
- Xác thực callback từ VNPay
- Xử lý kết quả thanh toán
- Xử lý IPN (Instant Payment Notification)

**Main Methods**:
```php
public function createVNPayPaymentUrl($orderId, $ipAddress);
public function validateVNPayCallback($inputData);
public function processVNPayReturn($inputData, $userId = null);
public function processVNPayIPN($inputData);
```

---

## 🔧 Cách sử dụng

### 1. Trong Controllers

**❌ TRƯỚC ĐÂY** (Tight coupling):
```php
use App\Services\CartService;

class CustomerCartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }
}
```

**✅ SAU KHI SỬ DỤNG INTERFACE** (Loose coupling):
```php
use App\Contracts\CartServiceInterface;

class CustomerCartController extends Controller
{
    protected $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }
}
```

### 2. Service Binding

**File**: `app/Providers/ServiceBindingProvider.php`

```php
<?php

namespace App\Providers;

use App\Contracts\CartServiceInterface;
use App\Contracts\OrderServiceInterface;
use App\Contracts\PaymentServiceInterface;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PaymentService;
use Illuminate\Support\ServiceProvider;

class ServiceBindingProvider extends ServiceProvider
{
    public $bindings = [
        CartServiceInterface::class => CartService::class,
        OrderServiceInterface::class => OrderService::class,
        PaymentServiceInterface::class => PaymentService::class,
    ];

    public function register(): void
    {
        // Bindings tự động đăng ký qua $bindings property
    }
}
```

**Đăng ký Provider** trong `bootstrap/providers.php`:
```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\ServiceBindingProvider::class, // ✅ Thêm dòng này
];
```

---

## ✅ Lợi ích

### 1. **Dependency Inversion Principle (SOLID)**

```php
// ✅ Controller phụ thuộc vào Interface (abstraction)
// Không phụ thuộc vào implementation cụ thể
class CustomerCartController extends Controller
{
    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }
}
```

### 2. **Dễ dàng thay đổi Implementation**

```php
// Có thể swap sang implementation khác mà không cần sửa Controller
public $bindings = [
    CartServiceInterface::class => AdvancedCartService::class, // ✅ Swap implementation
];
```

### 3. **Testability - Dễ Mock**

```php
// Trong Unit Test, dễ dàng mock Interface
public function test_checkout_success()
{
    $mockCartService = $this->createMock(CartServiceInterface::class);
    $mockCartService->method('processCheckout')
        ->willReturn([
            'success' => true,
            'order' => $fakeOrder,
        ]);

    $controller = new CustomerCartController($mockCartService);
    // Test logic...
}
```

### 4. **Contract Documentation**

Interface tự nó là tài liệu - liệt kê tất cả methods public mà Service phải implement.

```php
interface CartServiceInterface
{
    /**
     * Xử lý checkout đơn hàng
     *
     * @param  array  $data
     * @param  int|null  $userId
     * @return array ['success', 'order', 'discount_amount', 'payment_method']
     * @throws \Exception
     */
    public function processCheckout(array $data, $userId = null);
}
```

---

## 🧪 Testing với Interfaces

### 1. Unit Test với Mock

**File**: `tests/Unit/CustomerCartControllerTest.php`

```php
<?php

namespace Tests\Unit;

use App\Contracts\CartServiceInterface;
use App\Http\Controllers\Web\CustomerCartController;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CustomerCartControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_redirects_to_vnpay_when_payment_method_is_vnpay()
    {
        // Arrange: Mock CartServiceInterface
        $mockCartService = $this->createMock(CartServiceInterface::class);
        
        $fakeOrder = new Order(['order_id' => 123]);
        
        $mockCartService->expects($this->once())
            ->method('processCheckout')
            ->willReturn([
                'success' => true,
                'order' => $fakeOrder,
                'payment_method' => 'vnpay',
            ]);

        // Inject mock vào controller
        $controller = new CustomerCartController($mockCartService);

        // Act: Gọi checkout method
        $request = Request::create('/cart/checkout', 'POST', [
            'payment_method' => 'vnpay',
        ]);
        
        $response = $controller->checkout($request);

        // Assert: Kiểm tra redirect đến payment page
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->isRedirect(route('payment.create.get')));
    }
}
```

### 2. Feature Test (Integration)

**File**: `tests/Feature/CheckoutTest.php`

```php
<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_checkout_with_cod()
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10000]);
        
        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        // Act
        $response = $this->actingAs($user)->post('/cart/checkout', [
            'shipping_name' => 'Test User',
            'shipping_phone' => '0123456789',
            'shipping_address' => 'Test Address',
            'payment_method' => 'cod',
        ]);

        // Assert
        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total_amount' => 20000,
        ]);
    }
}
```

### 3. Mock trong Laravel Service Container

```php
// Trong test, có thể bind mock vào container
public function test_with_container_binding()
{
    $mockCartService = $this->createMock(CartServiceInterface::class);
    
    $this->app->instance(CartServiceInterface::class, $mockCartService);
    
    // Controller sẽ tự động nhận mock từ container
    $response = $this->post('/cart/checkout', [...]);
}
```

---

## 🔄 Swapping Implementations

### Ví dụ: Tạo Advanced Cart Service

```php
<?php

namespace App\Services;

use App\Contracts\CartServiceInterface;

class AdvancedCartService implements CartServiceInterface
{
    // Implement tất cả methods từ Interface
    
    public function processCheckout(array $data, $userId = null)
    {
        // Advanced implementation với:
        // - AI-based discount recommendations
        // - Real-time inventory sync
        // - Fraud detection
        
        // ...
    }
    
    // ... implement các methods khác
}
```

**Swap trong ServiceProvider**:
```php
public $bindings = [
    CartServiceInterface::class => AdvancedCartService::class, // ✅ Swap
];
```

**✅ Lợi ích**: Không cần sửa bất kỳ Controller nào!

---

## 📊 So sánh trước và sau

| Aspect | ❌ Không có Interface | ✅ Có Interface |
|--------|----------------------|----------------|
| **Coupling** | Tight (phụ thuộc vào class cụ thể) | Loose (phụ thuộc vào abstraction) |
| **Testing** | Khó mock, phải dùng concrete class | Dễ mock interface |
| **Flexibility** | Khó swap implementation | Dễ swap qua Service Provider |
| **SOLID** | Vi phạm DIP | Tuân thủ DIP |
| **Maintenance** | Khó refactor | Dễ refactor |
| **Documentation** | Phải đọc code implementation | Interface tự là contract |

---

## 🎯 Best Practices

### 1. ✅ Type-hint Interface, không phải Implementation

```php
// ❌ BAD
public function __construct(CartService $cartService)

// ✅ GOOD
public function __construct(CartServiceInterface $cartService)
```

### 2. ✅ Interface chỉ chứa public methods

```php
// Interface chỉ định nghĩa public API
interface CartServiceInterface
{
    public function addToCart($productId, $quantity);
    // Không có protected/private methods
}
```

### 3. ✅ Đặt tên Interface rõ ràng

```php
// ✅ GOOD
CartServiceInterface
OrderServiceInterface
PaymentServiceInterface

// ❌ BAD
ICart
CartInt
CartInterface1
```

### 4. ✅ Document các methods trong Interface

```php
interface CartServiceInterface
{
    /**
     * Thêm sản phẩm vào giỏ hàng
     *
     * @param  int  $productId  ID của sản phẩm
     * @param  int  $quantity  Số lượng
     * @return array ['success' => bool]
     * @throws \Exception  Khi sản phẩm không tồn tại
     */
    public function addToCart($productId, $quantity = 1);
}
```

### 5. ✅ Giữ Interface ổn định

Khi thêm method mới, cân nhắc tạo interface mới thay vì sửa interface cũ (để tránh break existing code).

---

## 🔗 Tài liệu liên quan

- **[ARCHITECTURE.md](./ARCHITECTURE.md)** - Kiến trúc tổng thể
- **[BUSINESS_LOGIC.md](./BUSINESS_LOGIC.md)** - Business logic rules
- **[COMPLETE_TESTING_GUIDE.md](./COMPLETE_TESTING_GUIDE.md)** - Hướng dẫn testing
- **[CODING_CONVENTIONS.md](./CODING_CONVENTIONS.md)** - Quy tắc code

---

## 📝 Checklist Implementation

- [x] ✅ Tạo `CartServiceInterface`
- [x] ✅ Tạo `OrderServiceInterface`
- [x] ✅ Tạo `PaymentServiceInterface`
- [x] ✅ `CartService` implements `CartServiceInterface`
- [x] ✅ `OrderService` implements `OrderServiceInterface`
- [x] ✅ `PaymentService` implements `PaymentServiceInterface`
- [x] ✅ Tạo `ServiceBindingProvider`
- [x] ✅ Đăng ký provider trong `bootstrap/providers.php`
- [x] ✅ Cập nhật tất cả Controllers sử dụng Interfaces
- [ ] ⏳ Viết unit tests với mocked interfaces
- [ ] ⏳ Update documentation

---

**Cập nhật lần cuối**: 30/10/2025  
**Phiên bản**: 1.0  
**Tác giả**: Hoàng Quang Vinh  
**Thay đổi mới nhất**: 
- Tạo Service Interfaces cho Cart, Order, Payment
- Implement interfaces trong Services
- Bind interfaces trong ServiceProvider
- Cập nhật Controllers sử dụng interfaces
