<?php

namespace Tests\Traits;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

/**
 * Trait WebTestHelpers
 *
 * Chứa các helper methods dùng chung cho Web Controller tests
 */
trait WebTestHelpers
{
    /**
     * Tạo user với role cụ thể
     */
    protected function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $role = Role::where('role_name', $roleName)->first();
        if (! $role) {
            $role = Role::create([
                'role_name' => $roleName,
                'role_display_name' => ucfirst($roleName),
            ]);
        }

        UserRole::create([
            'user_id' => $user->id, // FIX: User model uses 'id', not 'user_id'
            'role_id' => $role->role_id,
            'assigned_at' => now(),
        ]);

        return $user->fresh();
    }

    /**
     * Tạo admin user
     */
    protected function createAdmin(): User
    {
        return $this->createUserWithRole('admin');
    }

    /**
     * Tạo manager user
     */
    protected function createManager(): User
    {
        return $this->createUserWithRole('manager');
    }

    /**
     * Tạo customer user
     */
    protected function createCustomer(): User
    {
        return $this->createUserWithRole('customer');
    }

    /**
     * Tạo category mẫu
     */
    protected function createCategory(array $attributes = []): Category
    {
        return Category::factory()->create($attributes);
    }

    /**
     * Tạo product mẫu
     */
    protected function createProduct(array $attributes = []): Product
    {
        if (! isset($attributes['category_id'])) {
            $category = $this->createCategory();
            $attributes['category_id'] = $category->category_id;
        }

        return Product::factory()->create($attributes);
    }

    /**
     * Tạo nhiều products
     */
    protected function createProducts(int $count, array $attributes = []): \Illuminate\Support\Collection
    {
        if (! isset($attributes['category_id'])) {
            $category = $this->createCategory();
            $attributes['category_id'] = $category->category_id;
        }

        return Product::factory()->count($count)->create($attributes);
    }

    /**
     * Tạo order mẫu
     */
    protected function createOrder(array $attributes = []): Order
    {
        if (! isset($attributes['user_id'])) {
            $customer = $this->createCustomer();
            $attributes['user_id'] = $customer->id;
        }

        return Order::create(array_merge([
            'order_date' => now(),
            'total_amount' => 100.00,
            'status' => 'pending',
            'shipping_name' => 'Test Customer',
            'shipping_address' => '123 Test Street',
            'shipping_phone' => '0123456789',
        ], $attributes));
    }

    /**
     * Tạo nhiều orders
     */
    protected function createOrders(int $count, array $attributes = []): \Illuminate\Support\Collection
    {
        $orders = collect();

        for ($i = 0; $i < $count; $i++) {
            $orders->push($this->createOrder($attributes));
        }

        return $orders;
    }

    /**
     * Setup roles cơ bản
     */
    protected function setupRoles(): void
    {
        $roles = ['admin', 'manager', 'customer', 'guest'];

        foreach ($roles as $roleName) {
            if (! Role::where('role_name', $roleName)->exists()) {
                Role::create([
                    'role_name' => $roleName,
                    'role_display_name' => ucfirst($roleName),
                ]);
            }
        }
    }

    /**
     * Assert redirect to login
     */
    protected function assertRedirectToLogin($response): void
    {
        $response->assertRedirect(route('login'));
    }

    /**
     * Assert has validation error
     */
    protected function assertHasValidationError($response, string $field): void
    {
        $response->assertSessionHasErrors($field);
    }
}
