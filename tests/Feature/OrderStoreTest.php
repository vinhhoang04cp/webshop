<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderStoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $product;
    private $category;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo category trước
        $this->category = Category::create([
            'name' => 'Test Category',
            'description' => 'Test category description'
        ]);
        
        // Tạo user để test
        $this->user = User::factory()->create();
        
        // Tạo product để test (không dùng factory để tránh lỗi)
        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test product description',
            'price' => 100.00,
            'stock_quantity' => 50,
            'category_id' => $this->category->category_id,
            'image_url' => 'https://example.com/image.jpg'
        ]);
    }

    public function test_it_can_create_order_successfully()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'order_id',
                    'user_id', 
                    'order_date',
                    'status',
                    'total_amount',
                    'created_at',
                    'updated_at'
                ]);

        // Kiểm tra order đã được tạo trong database
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total_amount' => 200.00
        ]);

        // Kiểm tra order items đã được tạo
        $this->assertDatabaseHas('order_items', [
            'product_id' => $this->product->product_id,
            'quantity' => 2,
            'price' => 100.00
        ]);
    }

    public function test_it_requires_user_id()
    {
        $orderData = [
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    public function test_it_requires_valid_user_id()
    {
        $orderData = [
            'user_id' => 99999, // ID không tồn tại
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['user_id']);
    }

    public function test_it_requires_order_date()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['order_date']);
    }

    public function test_it_requires_valid_status()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'invalid_status',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['status']);
    }

    public function test_it_requires_items_array()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    public function test_it_requires_at_least_one_item()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => []
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    public function test_it_requires_valid_product_id_in_items()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => 99999, // ID không tồn tại
                    'quantity' => 2,
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.product_id']);
    }

    public function test_it_requires_positive_quantity_in_items()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 200.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 0, // Quantity phải >= 1
                    'price' => 100.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.quantity']);
    }
}