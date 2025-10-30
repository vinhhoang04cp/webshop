# Web Controllers Tests

Tài liệu hướng dẫn sử dụng test suite cho Web Controllers.

## 📋 Tổng quan

Test suite này bao gồm các test cases cho tất cả Web Controllers trong ứng dụng WebShop, được xây dựng trên Laravel 12 với Laravel Sail và MySQL trên Docker.

## 🗂️ Cấu trúc Tests

```
tests/
├── Feature/
│   └── Web/
│       ├── AuthControllerTest.php          # Tests cho authentication
│       ├── HomeControllerTest.php          # Tests cho trang home
│       ├── ProductControllerTest.php       # Tests cho quản lý sản phẩm (Admin)
│       ├── CustomerProductControllerTest.php # Tests cho xem sản phẩm (Customer)
│       ├── CustomerCartControllerTest.php  # Tests cho giỏ hàng
│       ├── OrderControllerTest.php         # Tests cho đơn hàng
│       ├── CategoryControllerTest.php      # Tests cho categories
│       └── README.md                       # File này
└── Traits/
    └── WebTestHelpers.php                  # Helper methods dùng chung
```

## 🚀 Chạy Tests

### Chạy tất cả tests
```bash
./vendor/bin/sail artisan test
```

### Chạy tests cho một file cụ thể
```bash
./vendor/bin/sail artisan test tests/Feature/Web/AuthControllerTest.php
```

### Chạy một test method cụ thể
```bash
./vendor/bin/sail artisan test --filter it_can_login_with_valid_credentials
```

### Chạy tests với coverage
```bash
./vendor/bin/sail artisan test --coverage
```

### Chạy tests song song (nhanh hơn)
```bash
./vendor/bin/sail artisan test --parallel
```

## 📝 Chi tiết từng Test File

### 1. AuthControllerTest.php (22 tests)

Test authentication flows:
- ✅ Hiển thị login/register pages
- ✅ Login với credentials hợp lệ/không hợp lệ
- ✅ Register user mới
- ✅ Validation cho login/register
- ✅ Logout
- ✅ Dashboard access theo roles
- ✅ Redirects cho authenticated users

**Key Test Cases:**
```php
it_can_login_with_valid_credentials()
it_cannot_login_with_invalid_credentials()
it_can_register_new_user()
admin_can_access_dashboard()
customer_cannot_access_dashboard()
```

### 2. HomeControllerTest.php (9 tests)

Test trang home:
- ✅ Hiển thị trang home
- ✅ Load categories
- ✅ Load featured products (limit 8)
- ✅ Load new products (limit 8)
- ✅ Cart count cho user/guest
- ✅ Empty state handling

**Key Test Cases:**
```php
it_displays_home_page()
it_loads_categories_on_home_page()
it_limits_featured_products_to_eight()
```

### 3. ProductControllerTest.php (18 tests)

Test quản lý sản phẩm (Admin/Manager):
- ✅ View products index với pagination
- ✅ Search products
- ✅ Create product với image upload
- ✅ View product details
- ✅ Update product
- ✅ Delete product (soft delete)
- ✅ Authorization checks

**Key Test Cases:**
```php
admin_can_view_products_index()
admin_can_create_product()
admin_can_update_product()
admin_can_delete_product()
customer_cannot_create_product()
```

### 4. CustomerProductControllerTest.php (12 tests)

Test xem sản phẩm (Customer/Guest):
- ✅ View products by category
- ✅ Product details
- ✅ Related products
- ✅ Product ratings
- ✅ Pagination
- ✅ Soft deleted products không hiển thị

**Key Test Cases:**
```php
it_shows_products_by_category()
it_shows_product_details()
it_shows_related_products_on_product_details()
it_does_not_show_deleted_products()
```

### 5. CustomerCartControllerTest.php (16 tests)

Test giỏ hàng:
- ✅ View cart
- ✅ Add to cart với validation
- ✅ Update quantity
- ✅ Remove item
- ✅ Clear cart
- ✅ Checkout với COD
- ✅ Checkout với VNPay redirect
- ✅ Authorization checks

**Key Test Cases:**
```php
user_can_add_product_to_cart()
user_can_update_cart_item_quantity()
user_can_checkout_with_cod()
checkout_redirects_to_vnpay_when_payment_method_is_vnpay()
user_cannot_update_other_users_cart_item()
```

### 6. OrderControllerTest.php (13 tests)

Test quản lý đơn hàng:
- ✅ View orders list
- ✅ Search orders
- ✅ View order details
- ✅ Update order status
- ✅ Validation
- ✅ Authorization checks
- ✅ Pagination

**Key Test Cases:**
```php
admin_can_view_all_orders()
admin_can_update_order_status()
it_validates_order_status_update()
customer_cannot_update_order_status()
```

### 7. CategoryControllerTest.php (7 tests)

Test quản lý categories:
- ✅ View categories
- ✅ Create category
- ✅ Update category
- ✅ Delete category
- ✅ Validation
- ✅ Authorization

## 🛠️ WebTestHelpers Trait

Helper methods có sẵn:

```php
// Tạo users với roles
$admin = $this->createAdmin();
$manager = $this->createManager();
$customer = $this->createCustomer();
$user = $this->createUserWithRole('custom_role');

// Tạo test data
$category = $this->createCategory(['name' => 'Electronics']);
$product = $this->createProduct(['price' => 99.99]);
$products = $this->createProducts(5);

// Setup
$this->setupRoles(); // Tạo roles cơ bản

// Assertions
$this->assertRedirectToLogin($response);
$this->assertHasValidationError($response, 'email');
```

## ✅ Test Coverage

Total: **97+ test cases**

- AuthController: 22 tests
- HomeController: 9 tests
- ProductController: 18 tests
- CustomerProductController: 12 tests
- CustomerCartController: 16 tests
- OrderController: 13 tests
- CategoryController: 7 tests

## 🔧 Cấu hình Test Environment

### phpunit.xml
```xml
<env name="APP_ENV" value="testing"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

**Lưu ý:** Tests sử dụng SQLite in-memory database để chạy nhanh hơn MySQL.

## 📊 Best Practices

### 1. Sử dụng RefreshDatabase
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
}
```

### 2. Setup roles trong setUp()
```php
protected function setUp(): void
{
    parent::setUp();
    $this->setupRoles();
}
```

### 3. Test naming convention
```php
/** @test */
public function it_can_do_something()
{
    // hoặc
}

public function test_it_can_do_something()
{
    // Laravel 12 hỗ trợ cả hai
}
```

### 4. Arrange-Act-Assert pattern
```php
/** @test */
public function it_can_create_product()
{
    // Arrange
    $admin = $this->createAdmin();
    $data = ['name' => 'Product'];
    
    // Act
    $response = $this->actingAs($admin)->post(route('products.store'), $data);
    
    // Assert
    $response->assertRedirect();
    $this->assertDatabaseHas('products', ['name' => 'Product']);
}
```

### 5. Test cả happy path và sad path
```php
/** @test */
public function it_can_login_with_valid_credentials() { }

/** @test */
public function it_cannot_login_with_invalid_credentials() { }
```

## 🐛 Troubleshooting

### Tests chạy chậm
```bash
# Sử dụng parallel testing
./vendor/bin/sail artisan test --parallel

# Hoặc cache configurations
./vendor/bin/sail artisan config:cache
```

### Database errors
```bash
# Clear cache
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear

# Migrate fresh
./vendor/bin/sail artisan migrate:fresh --env=testing
```

### Factory errors
```bash
# Đảm bảo factories được define đúng
php artisan make:factory ProductFactory --model=Product
```

## 📚 Resources

- [Laravel Testing Documentation](https://laravel.com/docs/12.x/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Sail Documentation](https://laravel.com/docs/12.x/sail)

## 🎯 TODO

- [ ] Thêm tests cho CouponController
- [ ] Thêm tests cho InventoryController
- [ ] Thêm tests cho ReportController
- [ ] Thêm tests cho PaymentController
- [ ] Thêm tests cho ProfileController
- [ ] Thêm Browser tests với Laravel Dusk
- [ ] Setup CI/CD pipeline

## 👨‍💻 Maintainer

Hoàng Quang Vinh

---

**Last Updated:** 30/10/2025
**Laravel Version:** 12.x
**PHPUnit Version:** 11.x

