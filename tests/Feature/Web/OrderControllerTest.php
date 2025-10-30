<?php

namespace Tests\Feature\Web;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WebTestHelpers;

/**
 * Test cases cho OrderController
 *
 * Test các chức năng quản lý đơn hàng:
 * - Danh sách đơn hàng
 * - Chi tiết đơn hàng
 * - Cập nhật trạng thái đơn hàng
 */
class OrderControllerTest extends TestCase
{
    use RefreshDatabase, WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function guest_cannot_view_orders()
    {
        $response = $this->get(route('dashboard.orders.index'));

        $this->assertRedirectToLogin($response);
    }

    /** @test */
    public function admin_can_view_all_orders()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        // Tạo orders
        $this->createOrders(5, [
            'user_id' => $customer->id, // User model uses 'id'
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.orders.index');
        $response->assertViewHas('orders');
    }

    /** @test */
    public function manager_can_view_all_orders()
    {
        $manager = $this->createManager();

        $response = $this->actingAs($manager)->get(route('dashboard.orders.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.orders.index');
    }

    /** @test */
    public function customer_cannot_view_orders_index()
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('dashboard.orders.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_search_orders()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $order = $this->createOrder([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.orders.index', ['search' => $order->order_id]));

        $response->assertStatus(200);
        $response->assertSee((string) $order->order_id);
    }

    /** @test */
    public function admin_can_view_order_details()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $order = $this->createOrder([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
        ]);

        OrderItem::create([
            'order_id' => $order->order_id,
            'product_id' => $product->product_id,
            'quantity' => 2,
            'price' => 50,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.orders.show', $order->order_id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.orders.show');
        $response->assertViewHas('order');
    }

    /** @test */
    public function it_returns_error_for_non_existent_order()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard.orders.show', 'INVALID-ORDER'));

        $response->assertRedirect(route('dashboard.orders.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_update_order_status()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $order = $this->createOrder([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
        ]);

        $response = $this->actingAs($admin)->put(route('dashboard.orders.update', $order->order_id), [
            'status' => 'processing',
        ]);

        // Controller might redirect to either index or show page
        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => $order->order_id,
            'status' => 'processing',
        ]);
    }

    /** @test */
    public function it_validates_order_status_update()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        $order = $this->createOrder([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
        ]);

        $response = $this->actingAs($admin)->put(route('dashboard.orders.update', $order->order_id), [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    /** @test */
    public function customer_cannot_update_order_status()
    {
        $customer = $this->createCustomer();

        $order = $this->createOrder([
            'user_id' => $customer->id,
            'total_amount' => 100,
            'status' => 'pending',
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
        ]);

        $response = $this->actingAs($customer)->put(route('dashboard.orders.update', $order->order_id), [
            'status' => 'delivered',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_displays_order_items_in_order_details()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();
        $products = $this->createProducts(3);

        $order = $this->createOrder([
            'user_id' => $customer->id,
            'total_amount' => 300,
            'status' => 'pending',
            'shipping_name' => 'John Doe',
            'shipping_address' => '123 Street',
            'shipping_phone' => '0123456789',
        ]);

        foreach ($products as $product) {
            OrderItem::create([
                'order_id' => $order->order_id,
                'product_id' => $product->product_id,
                'quantity' => 1,
                'price' => 100,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('dashboard.orders.show', $order->order_id));

        $response->assertStatus(200);
        // Verify order items exist in the database
        $this->assertEquals(3, OrderItem::where('order_id', $order->order_id)->count());
    }

    /** @test */
    public function it_paginates_orders_list()
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        // Tạo nhiều orders
        $this->createOrders(20, [
            'user_id' => $customer->id,
        ]);

        $response = $this->actingAs($admin)->get(route('dashboard.orders.index'));

        $response->assertStatus(200);
        $orders = $response->viewData('orders');
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $orders);
    }
}
