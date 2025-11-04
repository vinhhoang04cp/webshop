<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WebTestHelpers;

/**
 * Test cases cho HomeController
 *
 * Test các chức năng:
 * - Hiển thị trang home
 * - Load categories
 * - Load featured products
 * - Load new products
 * - Cart count
 */
class HomeControllerTest extends TestCase
{
    use RefreshDatabase;
    use WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function it_displays_home_page()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
    }

    /** @test */
    public function it_loads_categories_on_home_page()
    {
        $categories = Category::factory()->count(3)->create();

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('categories');

        $viewCategories = $response->viewData('categories');
        $this->assertCount(3, $viewCategories);
    }

    /** @test */
    public function it_loads_featured_products_on_home_page()
    {
        $category = $this->createCategory();
        $this->createProducts(10, ['category_id' => $category->category_id]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('featuredProducts');

        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertLessThanOrEqual(8, $featuredProducts->count());
    }

    /** @test */
    public function it_loads_new_products_on_home_page()
    {
        $category = $this->createCategory();
        $this->createProducts(5, ['category_id' => $category->category_id]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('newProducts');

        $newProducts = $response->viewData('newProducts');
        $this->assertCount(5, $newProducts);
    }

    /** @test */
    public function it_shows_cart_count_for_authenticated_users()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('cartCount');
    }

    /** @test */
    public function it_shows_zero_cart_count_for_guests()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('cartCount', 0);
    }

    /** @test */
    public function it_displays_empty_home_page_when_no_products()
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewHas('featuredProducts');
        $response->assertViewHas('newProducts');

        $this->assertCount(0, $response->viewData('featuredProducts'));
        $this->assertCount(0, $response->viewData('newProducts'));
    }

    /** @test */
    public function it_limits_featured_products_to_eight()
    {
        $category = $this->createCategory();
        $this->createProducts(20, ['category_id' => $category->category_id]);

        $response = $this->get(route('home'));

        $featuredProducts = $response->viewData('featuredProducts');
        $this->assertEquals(8, $featuredProducts->count());
    }

    /** @test */
    public function it_limits_new_products_to_eight()
    {
        $category = $this->createCategory();
        $this->createProducts(15, ['category_id' => $category->category_id]);

        $response = $this->get(route('home'));

        $newProducts = $response->viewData('newProducts');
        $this->assertLessThanOrEqual(8, $newProducts->count());
    }
}
