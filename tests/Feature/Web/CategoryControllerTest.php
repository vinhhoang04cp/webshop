<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WebTestHelpers;

/**
 * Test cases cho CategoryController
 *
 * Test CRUD categories cho admin
 */
class CategoryControllerTest extends TestCase
{
    use RefreshDatabase, WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function admin_can_view_categories_index()
    {
        $admin = $this->createAdmin();
        Category::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get(route('dashboard.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.categories.index');
        $response->assertViewHas('categories');
    }

    /** @test */
    public function customer_cannot_view_categories_index()
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('dashboard.categories.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_create_category()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('dashboard.categories.store'), [
            'name' => 'New Category',
            'description' => 'Category description',
        ]);

        $response->assertRedirect(route('dashboard.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'New Category',
        ]);
    }

    /** @test */
    public function it_validates_category_creation()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->post(route('dashboard.categories.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function admin_can_update_category()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->put(route('dashboard.categories.update', $category->category_id), [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect(route('dashboard.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'category_id' => $category->category_id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function admin_can_delete_category()
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)->delete(route('dashboard.categories.destroy', $category->category_id));

        $response->assertRedirect(route('dashboard.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', [
            'category_id' => $category->category_id,
        ]);
    }

    /** @test */
    public function customer_cannot_create_category()
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->post(route('dashboard.categories.store'), [
            'name' => 'Hacked Category',
        ]);

        $response->assertStatus(403);
    }
}
