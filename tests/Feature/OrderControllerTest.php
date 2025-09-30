<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;
    private $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Tạo user để test
        $this->user = User::factory()->create();
        
        // Tạo category trước để tránh lỗi foreign key
        $category = \App\Models\Category::factory()->create();
        
        // Tạo product để test
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 100.00,
            'category_id' => $category->category_id
        ]);
    }

    /** @test */
    public function it_can_create_order_successfully()
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
                    'data' => [
                        'order_id',
                        'user_id', 
                        'order_date',
                        'status',
                        'total_amount',
                        'created_at',
                        'updated_at'
                    ]
                ]);

        // Kiểm tra order đã được tạo trong database
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total_amount' => 200.00
        ]);
    }

    /** @test */
    public function it_requires_user_id()
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

    /** @test */
    public function it_requires_valid_user_id()
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

    /** @test */
    public function it_requires_order_date()
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

    /** @test */
    public function it_requires_valid_status()
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

    /** @test */
    public function it_requires_total_amount()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
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
                ->assertJsonValidationErrors(['total_amount']);
    }

    /** @test */
    public function it_requires_total_amount_to_be_non_negative()
    {
        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => -100,
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
                ->assertJsonValidationErrors(['total_amount']);
    }

    /** @test */
    public function it_requires_items_array()
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

    /** @test */
    public function it_requires_at_least_one_item()
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

    /** @test */
    public function it_requires_valid_product_id_in_items()
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

    /** @test */
    public function it_requires_positive_quantity_in_items()
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

    /** @test */
    public function it_requires_non_negative_price_in_items()
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
                    'price' => -50.00 // Price không được âm
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items.0.price']);
    }

    /** @test */
    public function it_can_create_order_with_multiple_items()
    {
        $category2 = \App\Models\Category::factory()->create();
        $product2 = Product::factory()->create([
            'name' => 'Test Product 2',
            'price' => 50.00,
            'category_id' => $category2->category_id
        ]);

        $orderData = [
            'user_id' => $this->user->id,
            'order_date' => now()->format('Y-m-d'),
            'status' => 'pending',
            'total_amount' => 350.00,
            'items' => [
                [
                    'product_id' => $this->product->product_id,
                    'quantity' => 2,
                    'price' => 100.00
                ],
                [
                    'product_id' => $product2->product_id,
                    'quantity' => 3,
                    'price' => 50.00
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201);

        // Kiểm tra order đã được tạo
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'status' => 'pending',
            'total_amount' => 350.00
        ]);
    }
}