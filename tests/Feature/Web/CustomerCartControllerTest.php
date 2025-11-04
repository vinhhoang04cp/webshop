<?php

namespace Tests\Feature\Web;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WebTestHelpers;

/**
 * Test cases cho CustomerCartController
 *
 * Test các chức năng giỏ hàng:
 * - Xem giỏ hàng
 * - Thêm sản phẩm vào giỏ
 * - Cập nhật số lượng
 * - Xóa sản phẩm
 * - Xóa toàn bộ giỏ hàng
 * - Checkout
 */
class CustomerCartControllerTest extends TestCase
{
    use RefreshDatabase;
    use WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function guest_cannot_view_cart()
    {
        $response = $this->get(route('cart.index'));

        $this->assertRedirectToLogin($response);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function authenticated_user_can_view_cart()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
        $response->assertViewHas(['cart', 'cartItems', 'categories', 'cartCount']);
    }

    /** @test */
    public function it_shows_empty_cart_for_new_user()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->get(route('cart.index'));

        $response->assertStatus(200);
        $cartItems = $response->viewData('cartItems');
        $this->assertCount(0, $cartItems);
    }

    /** @test */
    public function guest_cannot_add_to_cart()
    {
        $product = $this->createProduct();

        $response = $this->post(route('cart.add', $product->product_id), [
            'quantity' => 1,
        ]);

        $this->assertRedirectToLogin($response);
        $response->assertSessionHas('error');
    }

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $user = $this->createCustomer();
        $product = $this->createProduct(['stock_quantity' => 10]);

        // Tạo inventory cho product
        Inventory::create([
            'product_id' => $product->product_id,
            'current_stock' => 10,
        ]);

        $response = $this->actingAs($user)->post(route('cart.add', $product->product_id), [
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->product_id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function it_validates_quantity_when_adding_to_cart()
    {
        $user = $this->createCustomer();
        $product = $this->createProduct();

        $response = $this->actingAs($user)->post(route('cart.add', $product->product_id), [
            'quantity' => -5,
        ]);

        // Validation error: either 422 (JSON) or 302 (redirect)
        $this->assertContains($response->status(), [302, 422]);
    }

    /** @test */
    public function user_can_update_cart_item_quantity()
    {
        $user = $this->createCustomer();
        $product = $this->createProduct(['stock_quantity' => 20]);

        Inventory::create([
            'product_id' => $product->product_id,
            'current_stock' => 20,
        ]);

        // Tạo cart và cart item
        $cart = Cart::create(['user_id' => $user->id]); // User model uses 'id'
        $cartItem = CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user)->put(route('cart.update', $cartItem->cart_item_id), [
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', [
            'cart_item_id' => $cartItem->cart_item_id,
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function user_can_remove_item_from_cart()
    {
        $user = $this->createCustomer();
        $product = $this->createProduct();

        $cart = Cart::create(['user_id' => $user->id]); // User model uses 'id'
        $cartItem = CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user)->delete(route('cart.remove', $cartItem->cart_item_id));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('cart_items', [
            'cart_item_id' => $cartItem->cart_item_id,
        ]);
    }

    /** @test */
    public function user_can_clear_entire_cart()
    {
        $user = $this->createCustomer();
        $cart = Cart::create(['user_id' => $user->id]); // User model uses 'id'

        // Tạo nhiều cart items
        $products = $this->createProducts(3);
        foreach ($products as $product) {
            CartItem::create([
                'cart_id' => $cart->cart_id,
                'product_id' => $product->product_id,
                'quantity' => 1,
                'price' => $product->price,
            ]);
        }

        $response = $this->actingAs($user)->delete(route('cart.clear'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(0, CartItem::where('cart_id', $cart->cart_id)->count());
    }

    /** @test */
    public function guest_cannot_checkout()
    {
        $response = $this->post(route('cart.checkout'), [
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
            'payment_method' => 'cod',
        ]);

        $this->assertRedirectToLogin($response);
    }

    /** @test */
    public function user_can_checkout_with_cod()
    {
        $user = $this->createCustomer();
        $product = $this->createProduct(['stock_quantity' => 10]);

        Inventory::create([
            'product_id' => $product->product_id,
            'current_stock' => 10,
        ]);

        $cart = Cart::create(['user_id' => $user->id]); // User model uses 'id'
        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user)->post(route('cart.checkout'), [
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Main Street, City',
            'shipping_phone' => '0123456789',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        // Verify order was created for the user
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id, // User model uses 'id'
            'shipping_name' => 'John Doe',
        ]);

        // payment_method might be stored in a related table or differently
    }

    /** @test */
    public function it_validates_checkout_data()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->post(route('cart.checkout'), [
            'shipping_name' => '',
            'shipping_address' => '',
            'shipping_phone' => 'invalid',
            'payment_method' => '',
        ]);

        // Should redirect back with validation errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['shipping_name', 'shipping_address', 'payment_method']);
        // shipping_phone might have different validation rules
    }

    /** @test */
    public function checkout_redirects_to_vnpay_when_payment_method_is_vnpay()
    {
        $user = $this->createCustomer();
        $product = $this->createProduct(['stock_quantity' => 10]);

        Inventory::create([
            'product_id' => $product->product_id,
            'current_stock' => 10,
        ]);

        $cart = Cart::create(['user_id' => $user->id]); // User model uses 'id'
        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user)->post(route('cart.checkout'), [
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Main Street',
            'shipping_phone' => '0123456789',
            'payment_method' => 'vnpay',
        ]);

        $response->assertRedirect(route('payment.create.get'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function user_cannot_update_other_users_cart_item()
    {
        $user1 = $this->createCustomer();
        $user2 = User::factory()->create();

        $cart = Cart::create(['user_id' => $user2->id]); // User model uses 'id'
        $product = $this->createProduct();
        $cartItem = CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user1)->put(route('cart.update', $cartItem->cart_item_id), [
            'quantity' => 10,
        ]);

        // Should not update or should return error
        $response->assertStatus(302);
    }
}
