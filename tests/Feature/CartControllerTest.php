<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $products;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo user để test
        $this->user = User::factory()->create();

        // Tạo category
        $category = Category::factory()->create();

        // Tạo một số products để test
        $this->products = Product::factory()->count(3)->create([
            'category_id' => $category->category_id,
            'price' => 100.00,
            'stock_quantity' => 50,
        ]);
    }

    #[Test]
    public function it_can_create_a_cart_with_items()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/carts', [
                'items' => [
                    [
                        'product_id' => $this->products[0]->product_id,
                        'quantity' => 2,
                    ],
                    [
                        'product_id' => $this->products[1]->product_id,
                        'quantity' => 1,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'cart_id',
                    'user_id',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Kiểm tra cart đã được tạo trong database
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
        ]);

        // Kiểm tra cart items đã được tạo
        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(2, $cart->items->count());
    }

    /** @test */
    public function it_fails_to_create_cart_without_items()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/carts', [
                'items' => [],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /** @test */
    public function it_fails_to_create_cart_with_invalid_product_id()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/carts', [
                'items' => [
                    [
                        'product_id' => 99999, // ID không tồn tại
                        'quantity' => 2,
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /** @test */
    public function it_fails_to_create_cart_with_invalid_quantity()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/carts', [
                'items' => [
                    [
                        'product_id' => $this->products[0]->product_id,
                        'quantity' => 0, // Quantity không hợp lệ
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_fails_to_create_cart_with_missing_quantity()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/carts', [
                'items' => [
                    [
                        'product_id' => $this->products[0]->product_id,
                        // Thiếu quantity
                    ],
                ],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /** @test */
    public function it_can_list_all_carts()
    {
        // Tạo một số carts
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Cart::create(['user_id' => $user1->id]);
        Cart::create(['user_id' => $user2->id]);
        Cart::create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/carts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'cart_id',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_filter_carts_by_user_id()
    {
        // Tạo cart cho user hiện tại
        $cart = Cart::create(['user_id' => $this->user->id]);

        // Tạo cart cho user khác
        $otherUser = User::factory()->create();
        Cart::create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/carts?user_id='.$this->user->id);

        $response->assertStatus(200);

        // Kiểm tra chỉ có cart của user hiện tại
        $data = $response->json('data');
        foreach ($data as $item) {
            $this->assertEquals($this->user->id, $item['user_id']);
        }
    }

    /** @test */
    public function it_calculates_cart_totals_correctly()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/carts', [
                'items' => [
                    [
                        'product_id' => $this->products[0]->product_id,
                        'quantity' => 2, // 2 * 100 = 200
                    ],
                    [
                        'product_id' => $this->products[1]->product_id,
                        'quantity' => 3, // 3 * 100 = 300
                    ],
                ],
            ]);

        $response->assertStatus(201);

        // Lấy cart từ database
        $cart = Cart::where('user_id', $this->user->id)->with('items.product')->first();

        // Kiểm tra tổng số lượng items
        $this->assertEquals(5, $cart->items->sum('quantity'));

        // Kiểm tra tổng giá trị cart (2*100 + 3*100 = 500)
        $expectedTotal = 500.00;
        $actualTotal = $cart->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });

        $this->assertEquals($expectedTotal, $actualTotal);
    }
}
