<?php

namespace Tests\Feature\Web;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\WebTestHelpers;

/**
 * Test cases cho ProductController (Admin)
 *
 * Test các chức năng CRUD sản phẩm:
 * - Danh sách sản phẩm
 * - Tạo sản phẩm mới
 * - Xem chi tiết sản phẩm
 * - Cập nhật sản phẩm
 * - Xóa sản phẩm
 */
class ProductControllerTest extends TestCase
{
    use RefreshDatabase;
    use WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
        Storage::fake('public');
    }

    /** @test */
    public function admin_can_view_products_index()
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $this->createProducts(5, ['category_id' => $category->category_id]);

        $response = $this->actingAs($admin)->get(route('dashboard.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.products.index');
        $response->assertViewHas(['paginatedProducts', 'products', 'categories', 'pagination']);
    }

    /** @test */
    public function manager_can_view_products_index()
    {
        $manager = $this->createManager();

        $response = $this->actingAs($manager)->get(route('dashboard.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.products.index');
    }

    /** @test */
    public function customer_cannot_view_products_index()
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('dashboard.products.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_view_products_index()
    {
        $response = $this->get(route('dashboard.products.index'));

        $this->assertRedirectToLogin($response);
    }

    /** @test */
    public function admin_can_search_products()
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();

        $this->createProduct(['name' => 'iPhone 15', 'category_id' => $category->category_id]);
        $this->createProduct(['name' => 'Samsung Galaxy', 'category_id' => $category->category_id]);

        $response = $this->actingAs($admin)->get(route('dashboard.products.index', ['search' => 'iPhone']));

        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->contains('name', 'iPhone 15'));
    }

    /** @test */
    public function admin_can_view_create_product_form()
    {
        $admin = $this->createAdmin();
        $this->createCategory();

        $response = $this->actingAs($admin)->get(route('dashboard.products.create'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.products.create');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function admin_can_create_product()
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();

        $productData = [
            'name' => 'New Product',
            'description' => 'Product description',
            'price' => 99.99,
            'category_id' => $category->category_id,
            'stock_quantity' => 50,
            'image' => UploadedFile::fake()->image('product.jpg'),
        ];

        $response = $this->actingAs($admin)->post(route('dashboard.products.store'), $productData);

        $response->assertRedirect(route('dashboard.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
            'price' => 99.99,
        ]);
    }

    /** @test */
    public function it_validates_product_creation()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('dashboard.products.store'), [
            'name' => '',
            'price' => -10,
            'category_id' => 999,
        ]);

        // Should redirect back with validation errors
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'price', 'category_id']);
    }

    /** @test */
    public function admin_can_view_product_details()
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $response = $this->actingAs($admin)->get(route('dashboard.products.show', $product->product_id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.products.show');
        $response->assertViewHas('product');
    }

    /** @test */
    public function it_returns_error_for_non_existent_product()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard.products.show', 99999));

        $response->assertRedirect(route('dashboard.products.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_can_view_edit_product_form()
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $response = $this->actingAs($admin)->get(route('dashboard.products.edit', $product->product_id));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.products.edit');
        $response->assertViewHas(['product', 'categories']);
    }

    /** @test */
    public function admin_can_update_product()
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct(['name' => 'Old Name']);

        $updateData = [
            'name' => 'Updated Name',
            'description' => 'Updated description',
            'price' => 149.99,
            'category_id' => $product->category_id,
            'stock_quantity' => 30,
        ];

        $response = $this->actingAs($admin)->put(
            route('dashboard.products.update', $product->product_id),
            $updateData
        );

        $response->assertRedirect(route('dashboard.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'product_id' => $product->product_id,
            'name' => 'Updated Name',
            'price' => 149.99,
        ]);
    }

    /** @test */
    public function admin_can_update_product_with_new_image()
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $updateData = [
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'category_id' => $product->category_id,
            'stock_quantity' => $product->stock_quantity,
            'image' => UploadedFile::fake()->image('new-product.jpg'),
        ];

        $response = $this->actingAs($admin)->put(
            route('dashboard.products.update', $product->product_id),
            $updateData
        );

        $response->assertRedirect(route('dashboard.products.index'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $admin = $this->createAdmin();
        $product = $this->createProduct();

        $response = $this->actingAs($admin)->delete(route('dashboard.products.destroy', $product->product_id));

        $response->assertRedirect(route('dashboard.products.index'));
        $response->assertSessionHas('success');

        // Product is hard deleted, not soft deleted
        $this->assertDatabaseMissing('products', [
            'product_id' => $product->product_id,
        ]);
    }

    /** @test */
    public function customer_cannot_create_product()
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('dashboard.products.create'));

        $response->assertStatus(403);
    }

    /** @test */
    public function customer_cannot_update_product()
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $response = $this->actingAs($customer)->put(route('dashboard.products.update', $product->product_id), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function customer_cannot_delete_product()
    {
        $customer = $this->createCustomer();
        $product = $this->createProduct();

        $response = $this->actingAs($customer)->delete(route('dashboard.products.destroy', $product->product_id));

        $response->assertStatus(403);
    }
}
