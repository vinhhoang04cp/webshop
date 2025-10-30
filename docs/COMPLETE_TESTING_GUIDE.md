# Hướng Dẫn Testing Toàn Diện - WebShop Project

## 📋 Mục Lục

1. [Giới Thiệu](#1-giới-thiệu)
2. [Cấu Hình Testing](#2-cấu-hình-testing)
3. [Cấu Trúc Thư Mục Tests](#3-cấu-trúc-thư-mục-tests)
4. [Loại Tests](#4-loại-tests)
5. [Writing Tests](#5-writing-tests)
6. [Test Helpers & Traits](#6-test-helpers--traits)
7. [Database Testing](#7-database-testing)
8. [Authentication Testing](#8-authentication-testing)
9. [Authorization Testing](#9-authorization-testing)
10. [API Testing](#10-api-testing)
11. [Feature Testing](#11-feature-testing)
12. [Security Testing](#12-security-testing)
13. [File Upload Testing](#13-file-upload-testing)
14. [Running Tests](#14-running-tests)
15. [Test Coverage](#15-test-coverage)
16. [Best Practices](#16-best-practices)
17. [Troubleshooting](#17-troubleshooting)
18. [Continuous Integration](#18-continuous-integration)

---

## 1. Giới Thiệu

### 1.1 Tại Sao Cần Testing?

Testing là một phần quan trọng trong quá trình phát triển phần mềm:

- **Đảm bảo chất lượng code**: Phát hiện bugs sớm trước khi deploy
- **Tự tin refactor**: Thay đổi code mà không lo phá vỡ tính năng cũ
- **Documentation**: Tests là tài liệu sống về cách hệ thống hoạt động
- **Giảm regression bugs**: Ngăn chặn bugs cũ xuất hiện lại
- **Tăng tốc development**: Phát hiện lỗi nhanh hơn manual testing

### 1.2 Testing Framework

WebShop sử dụng **PHPUnit** - framework testing phổ biến nhất cho PHP và Laravel:

- **PHPUnit**: ^11.5.3
- **Laravel Testing Helpers**: Built-in Laravel testing utilities
- **Mockery**: ^1.6 - Mocking framework
- **Faker**: ^1.23 - Data generation

### 1.3 Testing Philosophy

Dự án tuân thủ các nguyên tắc:

- **Test-Driven Development (TDD)**: Viết test trước, code sau
- **AAA Pattern**: Arrange, Act, Assert
- **FIRST Principles**: Fast, Independent, Repeatable, Self-validating, Timely
- **Coverage >= 70%**: Mục tiêu coverage tối thiểu

---

## 2. Cấu Hình Testing

### 2.1 PHPUnit Configuration

File: `phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory>app</directory>
        </include>
    </source>
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_STORE" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="MAIL_MAILER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
    </php>
</phpunit>
```

### 2.2 Environment Variables

Testing environment sử dụng:

- **Database**: SQLite in-memory (`:memory:`) - nhanh và isolated
- **Cache**: Array driver - không persist data
- **Session**: Array driver - không persist sessions
- **Mail**: Array driver - không gửi email thật
- **Queue**: Sync - chạy jobs ngay lập tức

### 2.3 Composer Configuration

```json
{
    "scripts": {
        "test": [
            "@php artisan config:clear --ansi",
            "@php artisan test"
        ]
    },
    "require-dev": {
        "phpunit/phpunit": "^11.5.3",
        "mockery/mockery": "^1.6",
        "fakerphp/faker": "^1.23",
        "laravel/pint": "^1.25"
    }
}
```

---

## 3. Cấu Trúc Thư Mục Tests

```
tests/
├── Feature/                    # Feature tests (integration tests)
│   ├── Web/                   # Web controller tests
│   │   ├── AuthControllerTest.php
│   │   ├── ProductControllerTest.php
│   │   ├── CustomerProductControllerTest.php
│   │   ├── CartControllerTest.php
│   │   ├── OrderControllerTest.php
│   │   ├── CategoryControllerTest.php
│   │   ├── HomeControllerTest.php
│   │   └── ProfileControllerTest.php
│   ├── SecurityMiddlewareTest.php
│   ├── SessionSecurityTest.php
│   └── AdvancedSecurityTest.php
├── Unit/                      # Unit tests (isolated tests)
│   └── ExampleTest.php
├── Traits/                    # Reusable test traits
│   └── WebTestHelpers.php
├── TestCase.php              # Base test case
└── SUMMARY.md                # Tests documentation
```

### 3.1 Feature Tests

- Test toàn bộ flow của feature
- Test tích hợp giữa nhiều components
- Test HTTP requests/responses
- Test database interactions

### 3.2 Unit Tests

- Test individual methods/classes
- Test business logic
- Test isolated functionality
- Fast và không có dependencies

### 3.3 Test Traits

- Shared test helpers
- Common setup methods
- Reusable assertions
- Factory methods

---

## 4. Loại Tests

### 4.1 Unit Tests

**Mục đích**: Test các đơn vị code nhỏ nhất (methods, classes)

**Đặc điểm**:
- Nhanh (< 10ms per test)
- Isolated (không database, không HTTP)
- Focused (test 1 thing)

**Example**:

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PriceCalculator;

class PriceCalculatorTest extends TestCase
{
    /** @test */
    public function it_calculates_price_with_tax()
    {
        $calculator = new PriceCalculator();
        
        $result = $calculator->calculateWithTax(100, 0.1);
        
        $this->assertEquals(110, $result);
    }
    
    /** @test */
    public function it_applies_discount_correctly()
    {
        $calculator = new PriceCalculator();
        
        $result = $calculator->applyDiscount(100, 20);
        
        $this->assertEquals(80, $result);
    }
}
```

### 4.2 Feature Tests

**Mục đích**: Test toàn bộ features từ đầu đến cuối

**Đặc điểm**:
- Slower (database, HTTP)
- Integration testing
- Real-world scenarios

**Example**:

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPurchaseTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function user_can_purchase_product()
    {
        // Arrange
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100]);
        
        // Act
        $response = $this->actingAs($user)
            ->post('/cart/add', ['product_id' => $product->id]);
        
        // Assert
        $response->assertRedirect('/cart');
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);
    }
}
```

### 4.3 HTTP Tests

**Mục đích**: Test HTTP requests và responses

```php
/** @test */
public function it_returns_json_response()
{
    $response = $this->getJson('/api/products');
    
    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => ['id', 'name', 'price']
                 ]
             ]);
}
```

### 4.4 Database Tests

**Mục đích**: Test database operations

```php
/** @test */
public function it_creates_user_in_database()
{
    User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => bcrypt('password')
    ]);
    
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
}
```

---

## 5. Writing Tests

### 5.1 Test Structure (AAA Pattern)

```php
/** @test */
public function it_does_something()
{
    // 1. ARRANGE - Setup test data
    $user = User::factory()->create();
    $product = Product::factory()->create();
    
    // 2. ACT - Execute the action
    $response = $this->actingAs($user)
        ->post('/cart/add', ['product_id' => $product->id]);
    
    // 3. ASSERT - Verify the result
    $response->assertStatus(200);
    $this->assertDatabaseHas('cart_items', [
        'user_id' => $user->id,
        'product_id' => $product->id
    ]);
}
```

### 5.2 Naming Conventions

**Method Names**:
- Use `/** @test */` annotation hoặc prefix `test_`
- Descriptive names: `it_does_something_when_condition`
- Snake_case for readability

**Examples**:
```php
/** @test */
public function it_creates_product_successfully()

/** @test */
public function it_validates_required_fields()

/** @test */
public function admin_can_delete_product()

/** @test */
public function guest_cannot_access_dashboard()
```

### 5.3 Setup & Teardown

```php
class ProductTest extends TestCase
{
    use RefreshDatabase;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Chạy trước mỗi test
        $this->setupRoles();
        Storage::fake('public');
    }
    
    protected function tearDown(): void
    {
        // Chạy sau mỗi test
        Storage::fake('public')->deleteDirectory('products');
        
        parent::tearDown();
    }
}
```

### 5.4 Assertions

#### Common Assertions

```php
// Basic Assertions
$this->assertTrue($value);
$this->assertFalse($value);
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertEmpty($value);
$this->assertNotEmpty($value);
$this->assertCount(3, $array);

// String Assertions
$this->assertStringContainsString('needle', 'haystack');
$this->assertStringStartsWith('prefix', 'prefixString');
$this->assertStringEndsWith('suffix', 'stringSuffix');

// Array Assertions
$this->assertArrayHasKey('key', $array);
$this->assertContains('value', $array);

// Database Assertions
$this->assertDatabaseHas('users', ['email' => 'test@example.com']);
$this->assertDatabaseMissing('users', ['email' => 'deleted@example.com']);
$this->assertDatabaseCount('users', 10);

// Model Assertions
$this->assertModelExists($user);
$this->assertModelMissing($deletedUser);

// Authentication Assertions
$this->assertAuthenticated();
$this->assertGuest();
$this->assertAuthenticatedAs($user);
```

#### HTTP Response Assertions

```php
// Status Assertions
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated();
$response->assertNoContent();
$response->assertNotFound();
$response->assertForbidden();
$response->assertUnauthorized();

// Redirect Assertions
$response->assertRedirect('/dashboard');
$response->assertRedirectToRoute('home');

// View Assertions
$response->assertViewIs('products.index');
$response->assertViewHas('products');
$response->assertViewHas('total', 100);

// Session Assertions
$response->assertSessionHas('success');
$response->assertSessionHasErrors(['name', 'email']);
$response->assertSessionHasNoErrors();

// JSON Assertions
$response->assertJson(['success' => true]);
$response->assertJsonStructure(['data' => ['id', 'name']]);
$response->assertJsonCount(5, 'data');
$response->assertJsonFragment(['name' => 'Product']);
```

---

## 6. Test Helpers & Traits

### 6.1 WebTestHelpers Trait

File: `tests/Traits/WebTestHelpers.php`

```php
<?php

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

trait WebTestHelpers
{
    /**
     * Tạo user với role cụ thể
     */
    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $role = Role::where('role_name', $roleName)->first();
        if (!$role) {
            $role = Role::create([
                'role_name' => $roleName,
                'role_display_name' => ucfirst($roleName),
            ]);
        }

        UserRole::create([
            'user_id' => $user->id,
            'role_id' => $role->role_id,
            'assigned_at' => now(),
        ]);

        return $user->fresh();
    }

    /**
     * Tạo admin user
     */
    protected function createAdmin(): User
    {
        return $this->createUserWithRole('admin');
    }

    /**
     * Tạo manager user
     */
    protected function createManager(): User
    {
        return $this->createUserWithRole('manager');
    }

    /**
     * Tạo customer user
     */
    protected function createCustomer(): User
    {
        return $this->createUserWithRole('customer');
    }

    /**
     * Tạo category mẫu
     */
    protected function createCategory(array $attributes = []): Category
    {
        return Category::factory()->create($attributes);
    }

    /**
     * Tạo product mẫu
     */
    protected function createProduct(array $attributes = []): Product
    {
        if (!isset($attributes['category_id'])) {
            $category = $this->createCategory();
            $attributes['category_id'] = $category->category_id;
        }

        return Product::factory()->create($attributes);
    }

    /**
     * Tạo nhiều products
     */
    protected function createProducts(int $count, array $attributes = [])
    {
        if (!isset($attributes['category_id'])) {
            $category = $this->createCategory();
            $attributes['category_id'] = $category->category_id;
        }

        return Product::factory()->count($count)->create($attributes);
    }

    /**
     * Tạo order mẫu
     */
    protected function createOrder(array $attributes = []): Order
    {
        if (!isset($attributes['user_id'])) {
            $customer = $this->createCustomer();
            $attributes['user_id'] = $customer->id;
        }

        return Order::create(array_merge([
            'order_date' => now(),
            'total_amount' => 100.00,
            'status' => 'pending',
            'shipping_name' => 'Test Customer',
            'shipping_address' => '123 Test Street',
            'shipping_phone' => '0123456789',
        ], $attributes));
    }

    /**
     * Setup roles cơ bản
     */
    protected function setupRoles(): void
    {
        $roles = ['admin', 'manager', 'customer', 'guest'];

        foreach ($roles as $roleName) {
            if (!Role::where('role_name', $roleName)->exists()) {
                Role::create([
                    'role_name' => $roleName,
                    'role_display_name' => ucfirst($roleName),
                ]);
            }
        }
    }

    /**
     * Assert redirect to login
     */
    protected function assertRedirectToLogin($response): void
    {
        $response->assertRedirect(route('login'));
    }

    /**
     * Assert has validation error
     */
    protected function assertHasValidationError($response, string $field): void
    {
        $response->assertSessionHasErrors($field);
    }
}
```

### 6.2 Sử Dụng Helpers

```php
class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function admin_can_create_product()
    {
        // Sử dụng helper
        $admin = $this->createAdmin();
        $category = $this->createCategory();

        $response = $this->actingAs($admin)
            ->post('/products', [
                'name' => 'New Product',
                'category_id' => $category->category_id,
                'price' => 99.99
            ]);

        $response->assertRedirect();
    }
}
```

---

## 7. Database Testing

### 7.1 RefreshDatabase Trait

**Tự động reset database sau mỗi test**:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_creates_product()
    {
        // Database được reset trước test này
        Product::create(['name' => 'Test Product']);
        
        $this->assertDatabaseHas('products', ['name' => 'Test Product']);
        // Database sẽ được reset sau test này
    }
}
```

### 7.2 DatabaseMigrations vs DatabaseTransactions

```php
// Option 1: RefreshDatabase (recommended)
// - Migrate toàn bộ database mỗi test suite
// - Rollback mỗi test với transactions
use RefreshDatabase;

// Option 2: DatabaseMigrations
// - Migrate và rollback mỗi test
// - Chậm hơn
use DatabaseMigrations;

// Option 3: DatabaseTransactions
// - Chỉ rollback transaction
// - Nhanh nhưng không detect schema changes
use DatabaseTransactions;
```

### 7.3 Factories

**Tạo test data nhanh chóng**:

```php
// Create single model
$user = User::factory()->create();

// Create with attributes
$user = User::factory()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Create multiple
$users = User::factory()->count(10)->create();

// Make without saving
$user = User::factory()->make();

// With relationships
$user = User::factory()
    ->has(Order::factory()->count(3))
    ->create();

// State methods
$admin = User::factory()->admin()->create();
```

### 7.4 Seeders trong Tests

```php
/** @test */
public function it_uses_seeded_data()
{
    // Chạy specific seeder
    $this->seed(RoleSeeder::class);
    
    // Chạy all seeders
    $this->seed();
    
    // Verify seeded data
    $this->assertDatabaseHas('roles', ['role_name' => 'admin']);
}
```

### 7.5 Database Assertions

```php
// Has record
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com',
    'name' => 'Test User'
]);

// Missing record
$this->assertDatabaseMissing('users', [
    'email' => 'deleted@example.com'
]);

// Count records
$this->assertDatabaseCount('users', 10);

// Model exists
$this->assertModelExists($user);
$this->assertModelMissing($deletedUser);

// Soft deletes
$this->assertSoftDeleted('users', ['id' => $user->id]);
$this->assertNotSoftDeleted('users', ['id' => $user->id]);
```

---

## 8. Authentication Testing

### 8.1 Login Tests

```php
/** @test */
public function user_can_login_with_valid_credentials()
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $response = $this->post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password123'
    ]);

    $response->assertRedirect();
    $this->assertAuthenticatedAs($user);
}

/** @test */
public function user_cannot_login_with_invalid_credentials()
{
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123')
    ]);

    $response = $this->post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'wrong-password'
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
}
```

### 8.2 Registration Tests

```php
/** @test */
public function user_can_register_with_valid_data()
{
    $response = $this->post(route('register.post'), [
        'name' => 'Test User',
        'email' => 'newuser@example.com',
        'password' => 'Password123!@#',
        'password_confirmation' => 'Password123!@#'
    ]);

    $response->assertRedirect(route('login'));
    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com'
    ]);
}

/** @test */
public function registration_requires_password_confirmation()
{
    $response = $this->post(route('register.post'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'different'
    ]);

    $response->assertSessionHasErrors('password');
}
```

### 8.3 Acting As User

```php
/** @test */
public function authenticated_user_can_view_profile()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('profile'));

    $response->assertOk();
}

/** @test */
public function guest_cannot_view_profile()
{
    $response = $this->get(route('profile'));

    $response->assertRedirect(route('login'));
}
```

### 8.4 Logout Tests

```php
/** @test */
public function authenticated_user_can_logout()
{
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('logout'));

    $response->assertRedirect(route('login'));
    $this->assertGuest();
}
```

---

## 9. Authorization Testing

### 9.1 Role-Based Tests

```php
/** @test */
public function admin_can_access_dashboard()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)
        ->get(route('dashboard'));

    $response->assertOk();
}

/** @test */
public function customer_cannot_access_dashboard()
{
    $customer = $this->createCustomer();

    $response = $this->actingAs($customer)
        ->get(route('dashboard'));

    $response->assertForbidden(); // 403
}

/** @test */
public function guest_cannot_access_dashboard()
{
    $response = $this->get(route('dashboard'));

    $this->assertRedirectToLogin($response);
}
```

### 9.2 Permission Tests

```php
/** @test */
public function admin_can_delete_product()
{
    $admin = $this->createAdmin();
    $product = $this->createProduct();

    $response = $this->actingAs($admin)
        ->delete(route('products.destroy', $product));

    $response->assertRedirect();
    $this->assertModelMissing($product);
}

/** @test */
public function customer_cannot_delete_product()
{
    $customer = $this->createCustomer();
    $product = $this->createProduct();

    $response = $this->actingAs($customer)
        ->delete(route('products.destroy', $product));

    $response->assertForbidden();
    $this->assertModelExists($product);
}
```

### 9.3 Ownership Tests

```php
/** @test */
public function user_can_edit_own_order()
{
    $user = $this->createCustomer();
    $order = $this->createOrder(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('orders.edit', $order));

    $response->assertOk();
}

/** @test */
public function user_cannot_edit_other_user_order()
{
    $user1 = $this->createCustomer();
    $user2 = $this->createCustomer();
    $order = $this->createOrder(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)
        ->get(route('orders.edit', $order));

    $response->assertForbidden();
}
```

---

## 10. API Testing

### 10.1 JSON API Tests

```php
/** @test */
public function it_returns_products_list()
{
    $products = Product::factory()->count(3)->create();

    $response = $this->getJson('/api/products');

    $response->assertOk()
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'product_id',
                         'name',
                         'price',
                         'description'
                     ]
                 ]
             ])
             ->assertJsonCount(3, 'data');
}

/** @test */
public function it_creates_product_via_api()
{
    $admin = $this->createAdmin();
    $category = $this->createCategory();

    $response = $this->actingAs($admin)
        ->postJson('/api/products', [
            'name' => 'New Product',
            'price' => 99.99,
            'category_id' => $category->category_id
        ]);

    $response->assertCreated()
             ->assertJsonFragment(['name' => 'New Product']);
}
```

### 10.2 API Authentication

```php
/** @test */
public function api_requires_authentication()
{
    $response = $this->postJson('/api/products', [
        'name' => 'Product'
    ]);

    $response->assertUnauthorized(); // 401
}

/** @test */
public function api_accepts_bearer_token()
{
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/products');

    $response->assertOk();
}
```

### 10.3 API Validation

```php
/** @test */
public function api_validates_required_fields()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)
        ->postJson('/api/products', [
            'name' => '' // Invalid
        ]);

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['name', 'price']);
}
```

---

## 11. Feature Testing

### 11.1 Complete User Flow

```php
/** @test */
public function user_can_complete_checkout_process()
{
    // 1. User registers
    $this->post(route('register.post'), [
        'name' => 'Customer',
        'email' => 'customer@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!'
    ]);

    $user = User::where('email', 'customer@example.com')->first();

    // 2. User logs in
    $this->post(route('login.post'), [
        'email' => 'customer@example.com',
        'password' => 'Password123!'
    ]);

    // 3. User adds product to cart
    $product = $this->createProduct(['price' => 100]);
    
    $this->actingAs($user)
        ->post(route('cart.add'), [
            'product_id' => $product->product_id,
            'quantity' => 2
        ]);

    // 4. User checks out
    $response = $this->actingAs($user)
        ->post(route('orders.store'), [
            'shipping_name' => 'Customer',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789'
        ]);

    // 5. Verify order created
    $this->assertDatabaseHas('orders', [
        'user_id' => $user->id,
        'total_amount' => 200 // 100 * 2
    ]);
}
```

### 11.2 Admin CRUD Operations

```php
/** @test */
public function admin_can_manage_products()
{
    $admin = $this->createAdmin();
    $category = $this->createCategory();

    // Create
    $createResponse = $this->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Product 1',
            'price' => 50,
            'category_id' => $category->category_id,
            'stock_quantity' => 100
        ]);

    $product = Product::where('name', 'Product 1')->first();
    $createResponse->assertRedirect();

    // Read
    $readResponse = $this->actingAs($admin)
        ->get(route('products.show', $product));
    $readResponse->assertOk();

    // Update
    $updateResponse = $this->actingAs($admin)
        ->put(route('products.update', $product), [
            'name' => 'Updated Product',
            'price' => 75,
            'category_id' => $category->category_id,
            'stock_quantity' => 150
        ]);

    $updateResponse->assertRedirect();
    $this->assertDatabaseHas('products', [
        'product_id' => $product->product_id,
        'name' => 'Updated Product',
        'price' => 75
    ]);

    // Delete
    $deleteResponse = $this->actingAs($admin)
        ->delete(route('products.destroy', $product));

    $deleteResponse->assertRedirect();
    $this->assertModelMissing($product);
}
```

---

## 12. Security Testing

### 12.1 XSS Protection

```php
/** @test */
public function it_sanitizes_input_to_prevent_xss()
{
    $response = $this->postJson('/api/register', [
        'name' => '<script>alert("XSS")</script>Test User',
        'email' => 'test@example.com',
        'password' => 'SecurePass123!@#',
        'password_confirmation' => 'SecurePass123!@#'
    ]);

    $user = User::where('email', 'test@example.com')->first();

    // Script tags should be removed
    $this->assertStringNotContainsString('<script>', $user->name);
    $this->assertStringContainsString('Test User', $user->name);
}
```

### 12.2 CSRF Protection

```php
/** @test */
public function it_protects_against_csrf()
{
    $response = $this->post(route('login.post'), [
        'email' => 'test@example.com',
        'password' => 'password'
    ], ['X-CSRF-TOKEN' => 'invalid-token']);

    $response->assertStatus(419); // CSRF token mismatch
}
```

### 12.3 Security Headers

```php
/** @test */
public function it_includes_security_headers()
{
    $response = $this->get('/');

    $response->assertHeader('X-Content-Type-Options', 'nosniff');
    $response->assertHeader('X-Frame-Options', 'DENY');
    $response->assertHeader('X-XSS-Protection', '1; mode=block');
    $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    
    $this->assertTrue($response->headers->has('Content-Security-Policy'));
    $this->assertTrue($response->headers->has('Permissions-Policy'));
}
```

### 12.4 SQL Injection Protection

```php
/** @test */
public function it_protects_against_sql_injection()
{
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)
        ->get(route('products.index', [
            'search' => "'; DROP TABLE products; --"
        ]));

    $response->assertOk();
    
    // Table should still exist
    $this->assertDatabaseCount('products', 0);
}
```

---

## 13. File Upload Testing

### 13.1 Image Upload

```php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/** @test */
public function admin_can_upload_product_image()
{
    Storage::fake('public');
    
    $admin = $this->createAdmin();
    $category = $this->createCategory();

    $response = $this->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Product with Image',
            'price' => 99.99,
            'category_id' => $category->category_id,
            'stock_quantity' => 50,
            'image' => UploadedFile::fake()->image('product.jpg', 800, 600)
        ]);

    $response->assertRedirect();
    
    $product = Product::where('name', 'Product with Image')->first();
    
    // Verify file was stored
    Storage::disk('public')->assertExists($product->image_url);
}

/** @test */
public function it_validates_image_file_type()
{
    Storage::fake('public');
    
    $admin = $this->createAdmin();
    $category = $this->createCategory();

    $response = $this->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Product',
            'price' => 99.99,
            'category_id' => $category->category_id,
            'image' => UploadedFile::fake()->create('document.pdf', 100)
        ]);

    $response->assertSessionHasErrors('image');
}
```

### 13.2 File Size Validation

```php
/** @test */
public function it_validates_maximum_file_size()
{
    Storage::fake('public');
    
    $admin = $this->createAdmin();

    $response = $this->actingAs($admin)
        ->post(route('products.store'), [
            'name' => 'Product',
            'image' => UploadedFile::fake()->image('huge.jpg')->size(10000) // 10MB
        ]);

    $response->assertSessionHasErrors('image');
}
```

---

## 14. Running Tests

### 14.1 Artisan Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductControllerTest.php

# Run specific test method
php artisan test --filter=it_creates_product

# Run tests in parallel
php artisan test --parallel

# Run with coverage
php artisan test --coverage

# Run with minimum coverage threshold
php artisan test --coverage --min=70

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Stop on first failure
php artisan test --stop-on-failure

# Compact output
php artisan test --compact
```

### 14.2 PHPUnit Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific file
./vendor/bin/phpunit tests/Feature/ProductControllerTest.php

# Run with filter
./vendor/bin/phpunit --filter testCreateProduct

# Generate coverage report
./vendor/bin/phpunit --coverage-html coverage

# Run specific group
./vendor/bin/phpunit --group=api
```

### 14.3 Custom Test Scripts

**File**: `run-web-tests.sh`

```bash
#!/bin/bash

# Chạy tất cả Web Controller tests
./run-web-tests.sh

# Chạy specific controller tests
./run-web-tests.sh --product
./run-web-tests.sh --auth
./run-web-tests.sh --cart

# Chạy parallel
./run-web-tests.sh --parallel

# Chạy với coverage
./run-web-tests.sh --coverage
```

**File**: `run-tests.sh`

```bash
#!/bin/bash

# Chạy Security tests
./run-tests.sh
```

### 14.4 Laravel Sail

```bash
# Run tests trong Docker
./vendor/bin/sail artisan test

# Run với Sail alias
sail test

# Run specific tests
sail test tests/Feature/ProductControllerTest.php

# Run với parallel
sail test --parallel

# Run với coverage
sail test --coverage --min=70
```

---

## 15. Test Coverage

### 15.1 Generating Coverage Reports

```bash
# HTML coverage report
php artisan test --coverage-html=coverage

# Terminal coverage
php artisan test --coverage

# With minimum threshold
php artisan test --coverage --min=70

# Coverage for specific path
php artisan test --coverage --min=80 tests/Feature
```

### 15.2 Reading Coverage Reports

**Terminal Output**:
```
  Tests:    45 passed (156 assertions)
  Duration: 2.34s

  app/Http/Controllers .................... 85.2 %
  app/Models .............................. 92.5 %
  app/Services ............................ 78.3 %
```

**HTML Report**: Mở `coverage/index.html` trong browser

### 15.3 Coverage Goals

| Component | Minimum Coverage |
|-----------|-----------------|
| Controllers | 80% |
| Models | 70% |
| Services | 85% |
| Middleware | 90% |
| Overall | 70% |

### 15.4 Improving Coverage

```php
// Add @codeCoverageIgnore for không quan trọng code
class Helper
{
    /**
     * @codeCoverageIgnore
     */
    public function debugMethod()
    {
        // This won't count in coverage
    }
}
```

---

## 16. Best Practices

### 16.1 Test Organization

✅ **DO**:
```php
class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WebTestHelpers;
    
    // Group related tests
    // Index tests
    /** @test */
    public function admin_can_view_products_index() { }
    
    /** @test */
    public function customer_cannot_view_products_index() { }
    
    // Create tests
    /** @test */
    public function admin_can_create_product() { }
    
    /** @test */
    public function it_validates_product_creation() { }
}
```

❌ **DON'T**:
```php
class MixedTest extends TestCase
{
    // Don't mix unrelated tests
    public function testProducts() { }
    public function testUsers() { }
    public function testOrders() { }
}
```

### 16.2 Test Independence

✅ **DO**:
```php
/** @test */
public function it_creates_user()
{
    // Create own test data
    $userData = ['name' => 'Test', 'email' => 'test@test.com'];
    
    $user = User::create($userData);
    
    $this->assertDatabaseHas('users', $userData);
}
```

❌ **DON'T**:
```php
protected $sharedUser; // Don't share state between tests

/** @test */
public function test_one()
{
    $this->sharedUser = User::create([...]);
}

/** @test */
public function test_two()
{
    // Depends on test_one running first
    $this->sharedUser->update([...]);
}
```

### 16.3 Descriptive Names

✅ **DO**:
```php
/** @test */
public function admin_can_delete_product()

/** @test */
public function guest_cannot_access_dashboard()

/** @test */
public function it_validates_required_email_field()
```

❌ **DON'T**:
```php
public function test1() // Not descriptive

public function testStuff() // Too vague

public function testProductControllerDeleteMethod() // Too verbose
```

### 16.4 One Assertion per Concept

✅ **DO**:
```php
/** @test */
public function it_creates_user_in_database()
{
    $user = User::create(['email' => 'test@example.com']);
    
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
}

/** @test */
public function it_sends_welcome_email()
{
    Mail::fake();
    
    User::create(['email' => 'test@example.com']);
    
    Mail::assertSent(WelcomeEmail::class);
}
```

❌ **DON'T**:
```php
/** @test */
public function it_creates_user() // Testing too many things
{
    $user = User::create(['email' => 'test@example.com']);
    
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    Mail::assertSent(WelcomeEmail::class);
    $this->assertTrue($user->isActive());
    $this->assertNotNull($user->created_at);
}
```

### 16.5 Use Factories

✅ **DO**:
```php
$user = User::factory()->create();
$product = Product::factory()->create();
```

❌ **DON'T**:
```php
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => Hash::make('password'),
    'email_verified_at' => now(),
    // ... many more fields
]);
```

### 16.6 Test Edge Cases

```php
/** @test */
public function it_handles_empty_cart()
{
    $user = $this->createCustomer();
    
    $response = $this->actingAs($user)
        ->post(route('checkout'));
    
    $response->assertSessionHasErrors('cart');
}

/** @test */
public function it_handles_out_of_stock_product()
{
    $product = $this->createProduct(['stock_quantity' => 0]);
    
    $response = $this->post(route('cart.add'), [
        'product_id' => $product->product_id
    ]);
    
    $response->assertSessionHasErrors('stock');
}

/** @test */
public function it_handles_negative_quantity()
{
    $response = $this->post(route('cart.add'), [
        'product_id' => 1,
        'quantity' => -5
    ]);
    
    $response->assertSessionHasErrors('quantity');
}
```

### 16.7 Mock External Services

```php
use Illuminate\Support\Facades\Http;

/** @test */
public function it_processes_payment()
{
    Http::fake([
        'payment-gateway.com/*' => Http::response([
            'status' => 'success',
            'transaction_id' => 'txn_123'
        ], 200)
    ]);
    
    $result = $this->paymentService->charge(100);
    
    $this->assertEquals('success', $result['status']);
}
```

### 16.8 Test Data Cleanup

```php
protected function setUp(): void
{
    parent::setUp();
    Storage::fake('public');
}

protected function tearDown(): void
{
    // Cleanup uploaded files
    Storage::fake('public')->deleteDirectory('products');
    
    parent::tearDown();
}
```

---

## 17. Troubleshooting

### 17.1 Common Issues

#### Issue: Tests failing vì database không reset

**Solution**:
```php
// Ensure using RefreshDatabase
use Illuminate\Foundation\Testing\RefreshDatabase;

class MyTest extends TestCase
{
    use RefreshDatabase;
}
```

#### Issue: Foreign key constraint errors

**Solution**:
```php
// Create relationships in correct order
$category = $this->createCategory();
$product = $this->createProduct(['category_id' => $category->category_id]);
```

#### Issue: Session data not persisting

**Solution**:
```php
// Use startSession() before accessing session
$this->startSession();
$response = $this->post('/login', [...]);
```

#### Issue: File upload tests failing

**Solution**:
```php
// Always use Storage::fake()
protected function setUp(): void
{
    parent::setUp();
    Storage::fake('public');
}
```

### 17.2 Debugging Tests

```php
// Dump response
$response->dump();
$response->dumpHeaders();
$response->dumpSession();

// Dump database
$this->assertDatabaseHas('users', [
    'email' => 'test@example.com'
]);
dd(User::all()); // Debug actual data

// Enable query logging
DB::enableQueryLog();
// ... run code
dd(DB::getQueryLog());

// Pause test execution
$this->artisan('tinker'); // Not recommended in CI
```

### 17.3 Performance Issues

```bash
# Run tests in parallel
php artisan test --parallel

# Use SQLite in-memory
# Already configured in phpunit.xml

# Reduce seeded data
# Only create necessary data

# Clear cache before tests
php artisan config:clear
php artisan cache:clear
```

### 17.4 CI/CD Issues

```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          
      - name: Install Dependencies
        run: composer install
        
      - name: Run Tests
        run: php artisan test --coverage --min=70
```

---

## 18. Continuous Integration

### 18.1 GitHub Actions

**File**: `.github/workflows/tests.yml`

```yaml
name: Laravel Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main, develop ]

jobs:
  laravel-tests:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3

    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          extensions: mbstring, xml, ctype, json, bcmath, pdo_mysql
          coverage: xdebug

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress --no-interaction

      - name: Copy .env
        run: |
          cp .env.example .env
          php artisan key:generate

      - name: Run migrations
        run: php artisan migrate --force

      - name: Run tests
        run: php artisan test --coverage --min=70

      - name: Upload coverage to Codecov
        uses: codecov/codecov-action@v3
        with:
          files: ./coverage.xml
```

### 18.2 GitLab CI

**File**: `.gitlab-ci.yml`

```yaml
stages:
  - test

test:
  stage: test
  image: php:8.2-fpm
  
  services:
    - mysql:8.0
    
  variables:
    MYSQL_DATABASE: testing
    MYSQL_ROOT_PASSWORD: password
    DB_HOST: mysql
    DB_USERNAME: root
    
  before_script:
    - apt-get update -qq
    - apt-get install -y -qq git unzip
    - curl -sS https://getcomposer.org/installer | php
    - php composer.phar install
    - cp .env.example .env
    - php artisan key:generate
    
  script:
    - php artisan migrate --force
    - php artisan test --coverage --min=70
    
  coverage: '/^\s*Lines:\s*\d+.\d+\%/'
```

### 18.3 Pre-commit Hooks

**File**: `.git/hooks/pre-commit`

```bash
#!/bin/bash

echo "Running tests before commit..."

# Run tests
php artisan test --compact

if [ $? -ne 0 ]; then
    echo "Tests failed! Commit aborted."
    exit 1
fi

echo "Tests passed! Proceeding with commit."
exit 0
```

Make executable:
```bash
chmod +x .git/hooks/pre-commit
```

### 18.4 Code Quality Checks

```bash
# Laravel Pint (Code style)
./vendor/bin/pint

# PHPStan (Static analysis)
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse

# PHP Code Sniffer
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs app tests
```

---

## 🎯 Tổng Kết

### Key Takeaways

1. **Testing là bắt buộc**, không phải optional
2. **Feature tests** cho integration, **Unit tests** cho logic
3. **RefreshDatabase** để database clean mỗi test
4. **Factories** để tạo test data nhanh
5. **WebTestHelpers** để reuse common operations
6. **AAA Pattern**: Arrange, Act, Assert
7. **Coverage >= 70%** minimum
8. **Run tests thường xuyên**, đặc biệt trước commit

### Test Checklist

- [ ] Mỗi feature có tests
- [ ] Tests pass locally
- [ ] Tests pass trong CI/CD
- [ ] Coverage >= 70%
- [ ] Edge cases được test
- [ ] Security được test
- [ ] Tests có tên descriptive
- [ ] Tests độc lập với nhau
- [ ] No hardcoded values
- [ ] Factories được sử dụng

### Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Factories](https://laravel.com/docs/database-testing)
- [HTTP Tests](https://laravel.com/docs/http-tests)

---

**Document Version**: 1.0  
**Last Updated**: October 30, 2025  
**Author**: WebShop Development Team

