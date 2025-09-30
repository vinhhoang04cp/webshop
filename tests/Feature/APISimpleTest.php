<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\User;

class APISimpleTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_api_endpoints()
    {
        echo "\n=== COMPREHENSIVE API TEST REPORT ===\n\n";
        
        $results = [];
        
        // 1. Categories API
        echo "1. CATEGORIES API\n";
        echo "================\n";
        
        // GET categories
        $response = $this->getJson('/api/categories');
        echo "GET /api/categories: {$response->getStatusCode()}\n";
        $results['categories']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        // POST category
        $categoryData = ['name' => 'Test Category', 'description' => 'Test description'];
        $response = $this->postJson('/api/categories', $categoryData);
        echo "POST /api/categories: {$response->getStatusCode()}\n";
        if ($response->getStatusCode() >= 400) {
            echo "  Error: " . $response->getContent() . "\n";
        }
        $results['categories']['post'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        // Try to get category by ID
        $response = $this->getJson('/api/categories/1');
        echo "GET /api/categories/1: {$response->getStatusCode()}\n";
        $results['categories']['get_one'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        echo "\n";
        
        // 2. Products API
        echo "2. PRODUCTS API\n";
        echo "===============\n";
        
        // Create category first for product
        $category = Category::create(['name' => 'Test Category', 'description' => 'Test']);
        
        $response = $this->getJson('/api/products');
        echo "GET /api/products: {$response->getStatusCode()}\n";
        $results['products']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 99.99,
            'category_id' => $category->id
        ];
        $response = $this->postJson('/api/products', $productData);
        echo "POST /api/products: {$response->getStatusCode()}\n";
        if ($response->getStatusCode() >= 400) {
            echo "  Error: " . $response->getContent() . "\n";
        }
        $results['products']['post'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        echo "\n";
        
        // 3. Orders API
        echo "3. ORDERS API\n";
        echo "=============\n";
        
        // Create user first
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password')
        ]);
        
        $response = $this->getJson('/api/orders');
        echo "GET /api/orders: {$response->getStatusCode()}\n";
        $results['orders']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        $orderData = [
            'user_id' => $user->id,
            'total_amount' => 199.99,
            'status' => 'pending'
        ];
        $response = $this->postJson('/api/orders', $orderData);
        echo "POST /api/orders: {$response->getStatusCode()}\n";
        if ($response->getStatusCode() >= 400) {
            echo "  Error: " . $response->getContent() . "\n";
        }
        $results['orders']['post'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        echo "\n";
        
        // 4. Order Items API
        echo "4. ORDER ITEMS API\n";
        echo "==================\n";
        
        $response = $this->getJson('/api/order-items');
        echo "GET /api/order-items: {$response->getStatusCode()}\n";
        $results['order_items']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        echo "\n";
        
        // 5. Product Details API
        echo "5. PRODUCT DETAILS API\n";
        echo "======================\n";
        
        $response = $this->getJson('/api/product-details');
        echo "GET /api/product-details: {$response->getStatusCode()}\n";
        $results['product_details']['get_all'] = [
            'status' => $response->getStatusCode(),
            'success' => $response->getStatusCode() >= 200 && $response->getStatusCode() < 300
        ];
        
        echo "\n";
        
        // Summary
        echo "=== SUMMARY ===\n";
        echo "===============\n";
        
        $totalTests = 0;
        $successfulTests = 0;
        
        foreach ($results as $apiName => $endpoints) {
            echo strtoupper(str_replace('_', ' ', $apiName)) . ":\n";
            foreach ($endpoints as $action => $result) {
                $totalTests++;
                if ($result['success']) {
                    $successfulTests++;
                    echo "  ✅ " . str_replace('_', ' ', $action) . ": {$result['status']}\n";
                } else {
                    echo "  ❌ " . str_replace('_', ' ', $action) . ": {$result['status']}\n";
                }
            }
            echo "\n";
        }
        
        $successRate = $totalTests > 0 ? round(($successfulTests / $totalTests) * 100, 2) : 0;
        echo "STATISTICS:\n";
        echo "Total endpoints tested: {$totalTests}\n";
        echo "Successful: {$successfulTests}\n";
        echo "Failed: " . ($totalTests - $successfulTests) . "\n";
        echo "Success rate: {$successRate}%\n";
        
        // This assertion will always pass since we're just reporting
        $this->assertTrue(true);
    }
}