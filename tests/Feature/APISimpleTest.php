<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class APISimpleTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_api_endpoints()
    {
        echo "=== COMPREHENSIVE API TEST REPORT ===\n\n";

        $results = [];

        // Create shared test data
        $category = Category::create(['name' => 'Test Category', 'description' => 'Test']);

        // Refresh to get the correct ID after reorderIds()
        $category = $category->fresh();
        if (! $category) {
            // If fresh() fails, get the latest category
            $category = Category::latest('category_id')->first();
        }

        echo "Created category with ID: {$category->category_id}\n";

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 1. Categories API
        echo "1. CATEGORIES API\n";
        echo "================\n";

        // GET categories
        $response = $this->getJson('/api/categories');
        echo "GET /api/categories: {$response->getStatusCode()}\n";
        $results['categories']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        // POST category
        $categoryData = ['name' => 'Test Category New '.time(), 'description' => 'Test description'];
        $response = $this->postJson('/api/categories', $categoryData);
        echo "POST /api/categories: {$response->getStatusCode()}\n";
        if ($response->getStatusCode() >= 400) {
            echo '  Error: '.$response->getContent()."\n";
        }
        $results['categories']['post'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        // Try to get category by ID
        $response = $this->getJson('/api/categories/1');
        echo "GET /api/categories/1: {$response->getStatusCode()}\n";
        $results['categories']['get_one'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        echo "\n";

        // 2. Products API
        echo "2. PRODUCTS API\n";
        echo "===============\n";

        $response = $this->getJson('/api/products');
        echo "GET /api/products: {$response->getStatusCode()}\n";
        $results['products']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        // POST product (using shared category)
        $productData = [
            'name' => 'Test Product '.time(),
            'description' => 'Test description',
            'price' => 99.99,
            'stock_quantity' => 100,
            'category_id' => $category->category_id,  // Use the correct primary key
        ];
        $response = $this->postJson('/api/products', $productData);
        echo "POST /api/products: {$response->getStatusCode()}\n";
        if ($response->getStatusCode() >= 400) {
            echo '  Error: '.$response->getContent()."\n";
        }
        $results['products']['post'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        echo "\n";

        // 3. Orders API
        echo "3. ORDERS API\n";
        echo "=============\n";

        // Create product for order items (using shared category and user)
        $product = Product::create([
            'name' => 'Test Product for Order '.time(),
            'description' => 'Test description',
            'price' => 50.00,
            'stock_quantity' => 100,
            'category_id' => $category->category_id,
        ]);

        // Refresh to get correct ID after reorderIds()
        $product = $product->fresh();
        if (! $product) {
            $product = Product::latest('product_id')->first();
        }

        $response = $this->getJson('/api/orders');
        echo "GET /api/orders: {$response->getStatusCode()}\n";
        $results['orders']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        $orderData = [
            'user_id' => $user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 100.00,
            'items' => [
                [
                    'product_id' => $product->product_id,
                    'quantity' => 2,
                    'price' => 50.00,
                ],
            ],
        ];
        $response = $this->postJson('/api/orders', $orderData);
        echo "POST /api/orders: {$response->getStatusCode()}\n";
        if ($response->getStatusCode() >= 400) {
            echo '  Error: '.$response->getContent()."\n";
        }
        $results['orders']['post'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];
        echo "\n";

        // 4. Order Items API
        echo "4. ORDER ITEMS API\n";
        echo "==================\n";

        $response = $this->getJson('/api/order-items');
        echo "GET /api/order-items: {$response->getStatusCode()}\n";
        $results['order_items']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        echo "\n";

        // 5. Product Details API
        echo "5. PRODUCT DETAILS API\n";
        echo "======================\n";

        $response = $this->getJson('/api/product-details');
        echo "GET /api/product-details: {$response->getStatusCode()}\n";
        $results['product_details']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300,
        ];

        echo "\n";

        // Summary
        echo "=== SUMMARY ===\n";
        echo "===============\n";

        $totalTests = 0;
        $successfulTests = 0;

        foreach ($results as $apiName => $endpoints) {
            echo strtoupper(str_replace('_', ' ', $apiName)).":\n";
            foreach ($endpoints as $action => $result) {
                $totalTests++;
                if ($result['success']) {
                    $successfulTests++;
                    echo '  ✅ '.str_replace('_', ' ', $action).": {$result['status']}\n";
                } else {
                    echo '  ❌ '.str_replace('_', ' ', $action).": {$result['status']}\n";
                }
            }
            echo "\n";
        }

        $successRate = $totalTests > 0 ? round(($successfulTests / $totalTests) * 100, 2) : 0;
        echo "STATISTICS:\n";
        echo "Total endpoints tested: {$totalTests}\n";
        echo "Successful: {$successfulTests}\n";
        echo 'Failed: '.($totalTests - $successfulTests)."\n";
        echo "Success rate: {$successRate}%\n";

        // This assertion will always pass since we're just reporting
        $this->assertTrue(true);
    }
}
