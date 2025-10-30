<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Tests\Traits\WebTestHelpers;

/**
 * Test cases cho AuthController
 *
 * Test các chức năng:
 * - Hiển thị form login/register
 * - Đăng nhập
 * - Đăng ký
 * - Đăng xuất
 * - Dashboard access
 */
class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WebTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupRoles();
    }

    /** @test */
    public function it_shows_login_page_for_guests()
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function it_redirects_authenticated_users_from_login_page()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->get(route('login'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function it_shows_register_page_for_guests()
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.register');
    }

    /** @test */
    public function it_redirects_authenticated_users_from_register_page()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login.post'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function it_validates_login_request()
    {
        $response = $this->post(route('login.post'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    /** @test */
    public function it_can_register_new_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'newuser@example.com',
            'password' => 'Password123!@#', // Strong password: 12+ chars, uppercase, lowercase, number, special
            'password_confirmation' => 'Password123!@#',
        ];

        $response = $this->post(route('register.post'), $userData);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'Test User',
        ]);
    }

    /** @test */
    public function it_validates_registration_request()
    {
        $response = $this->post(route('register.post'), [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_prevents_duplicate_email_registration()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('register.post'), [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function it_can_logout()
    {
        $user = $this->createCustomer();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();
    }

    /** @test */
    public function admin_can_access_dashboard()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
        $response->assertViewHas(['user', 'productsCount', 'ordersCount', 'usersCount', 'totalRevenue', 'recentOrders']);
    }

    /** @test */
    public function manager_can_access_dashboard()
    {
        $manager = $this->createManager();

        $response = $this->actingAs($manager)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.index');
    }

    /** @test */
    public function customer_cannot_access_dashboard()
    {
        $customer = $this->createCustomer();

        $response = $this->actingAs($customer)->get(route('dashboard'));

        // Customer gets 403 Forbidden, not redirected
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_dashboard()
    {
        $response = $this->get(route('dashboard'));

        $this->assertRedirectToLogin($response);
    }

    /** @test */
    public function it_redirects_authenticated_users_to_correct_dashboard()
    {
        $admin = $this->createAdmin();

        $response = $this->post(route('login.post'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Auth::check());
    }
}
