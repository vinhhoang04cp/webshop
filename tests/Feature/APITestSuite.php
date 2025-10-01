<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class APITestSuite extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Don't seed data to avoid constraint violations
    }

    public function test_categories_api_endpoints()
    {
        echo "\n=== TESTING CATEGORIES API ===\n";

        // Test GET /api/categories
        $response = $this->getJson('/api/categories');
        echo 'GET /api/categories: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test POST /api/categories
        $categoryData = [
            'name' => 'Test Category',
            'description' => 'Test description',
        ];
        $response = $this->postJson('/api/categories', $categoryData);
        echo 'POST /api/categories: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        $categoryId = $response->json('data.id') ?? 1;

        // Test GET /api/categories/{id}
        $response = $this->getJson("/api/categories/{$categoryId}");
        echo "GET /api/categories/{$categoryId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test PUT /api/categories/{id}
        $updateData = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
        ];
        $response = $this->putJson("/api/categories/{$categoryId}", $updateData);
        echo "PUT /api/categories/{$categoryId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test DELETE /api/categories/{id}
        $response = $this->deleteJson("/api/categories/{$categoryId}");
        echo "DELETE /api/categories/{$categoryId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }

    public function test_products_api_endpoints()
    {
        echo "\n=== TESTING PRODUCTS API ===\n";

        // Create a category first
        $category = Category::factory()->create();

        // Test GET /api/products
        $response = $this->getJson('/api/products');
        echo 'GET /api/products: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test POST /api/products
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99.99,
            'category_id' => $category->id,
        ];
        $response = $this->postJson('/api/products', $productData);
        echo 'POST /api/products: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        $productId = $response->json('data.id') ?? 1;

        // Test GET /api/products/{id}
        $response = $this->getJson("/api/products/{$productId}");
        echo "GET /api/products/{$productId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test PUT /api/products/{id}
        $updateData = [
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 149.99,
            'category_id' => $category->id,
        ];
        $response = $this->putJson("/api/products/{$productId}", $updateData);
        echo "PUT /api/products/{$productId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test DELETE /api/products/{id}
        $response = $this->deleteJson("/api/products/{$productId}");
        echo "DELETE /api/products/{$productId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }

    public function test_orders_api_endpoints()
    {
        echo "\n=== TESTING ORDERS API ===\n";

        // Create a user first
        $user = User::factory()->create();

        // Test GET /api/orders
        $response = $this->getJson('/api/orders');
        echo 'GET /api/orders: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test POST /api/orders
        $orderData = [
            'user_id' => $user->id,
            'total_amount' => 199.99,
            'status' => 'pending',
        ];
        $response = $this->postJson('/api/orders', $orderData);
        echo 'POST /api/orders: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        $orderId = $response->json('data.id') ?? 1;

        // Test GET /api/orders/{id}
        $response = $this->getJson("/api/orders/{$orderId}");
        echo "GET /api/orders/{$orderId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test PUT /api/orders/{id}
        $updateData = [
            'user_id' => $user->id,
            'total_amount' => 299.99,
            'status' => 'completed',
        ];
        $response = $this->putJson("/api/orders/{$orderId}", $updateData);
        echo "PUT /api/orders/{$orderId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test DELETE /api/orders/{id}
        $response = $this->deleteJson("/api/orders/{$orderId}");
        echo "DELETE /api/orders/{$orderId}: ".$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }

    public function test_order_items_api_endpoints()
    {
        echo "\n=== TESTING ORDER-ITEMS API ===\n";

        // Test GET /api/order-items
        $response = $this->getJson('/api/order-items');
        echo 'GET /api/order-items: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Test POST /api/order-items (if valid order and product exist)
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $orderItemData = [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 99.99,
        ];
        $response = $this->postJson('/api/order-items', $orderItemData);
        echo 'POST /api/order-items: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }

    public function test_product_details_api_endpoints()
    {
        echo "\n=== TESTING PRODUCT-DETAILS API ===\n";

        // Test GET /api/product-details
        $response = $this->getJson('/api/product-details');
        echo 'GET /api/product-details: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);

        // Create related data for POST test
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $productDetailData = [
            'product_id' => $product->id,
            'size' => 'Medium',
            'color' => 'Blue',
            'material' => 'Cotton',
        ];
        $response = $this->postJson('/api/product-details', $productDetailData);
        echo 'POST /api/product-details: '.$response->getStatusCode()."\n";
        $this->assertTrue($response->getStatusCode() >= 200 && $response->getStatusCode() < 300);
    }
}
